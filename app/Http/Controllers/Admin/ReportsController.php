<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Services\PlatformHealthService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        // Get data from CENTRAL database (not tenant-specific)
        // Using Spatie Permission package methods instead of role column
        $totalCustomers = User::role('customer')->count();
        $totalVendors = Tenant::count();
        $activeVendors = Tenant::where('is_approved', true)->count();
        $pendingVendors = Tenant::where('is_approved', false)->count();
        
        // Calculate approval rate
        $approvalRate = $totalVendors > 0 ? round(($activeVendors / $totalVendors) * 100) : 0;
        
        // Get monthly data for the last 6 months
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push([
                'month' => $date->format('M Y'),
                'start' => $date->startOfMonth()->toDateString(),
                'end' => $date->endOfMonth()->toDateString(),
            ]);
        }
        
        // Customer growth data
        $customerGrowth = $months->map(function ($month) {
            return [
                'month' => $month['month'],
                'count' => User::role('customer')
                    ->whereBetween('created_at', [$month['start'], $month['end']])
                    ->count()
            ];
        });
        
        // Vendor growth data
        $vendorGrowth = $months->map(function ($month) {
            return [
                'month' => $month['month'],
                'count' => Tenant::whereBetween('created_at', [$month['start'], $month['end']])
                    ->count()
            ];
        });
        
        // Recent customers
        $recentCustomers = User::role('customer')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'verified' => !is_null($user->email_verified_at),
                    'created_at' => $user->created_at->toISOString(),
                ];
            });
        
        // Recent vendors
        $recentVendors = Tenant::latest()
            ->take(6)
            ->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name ?? 'Unnamed Store',
                    'email' => $tenant->email ?? '',
                    'domain' => $tenant->domains->first()?->domain ?? null,
                    'is_approved' => $tenant->is_approved,
                    'created_at' => $tenant->created_at->toISOString(),
                ];
            });
        
        // Top vendors (by days active)
        $topVendors = Tenant::where('is_approved', true)
            ->oldest()
            ->take(10)
            ->get()
            ->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name ?? 'Unnamed Store',
                    'domain' => $tenant->domains->first()?->domain ?? null,
                    'created_at' => $tenant->created_at->toISOString(),
                    'days_active' => $tenant->created_at->diffInDays(now()),
                ];
            });
        
        // Calculate this month's new signups
        $thisMonthStart = Carbon::now()->startOfMonth();
        $newCustomersThisMonth = User::role('customer')
            ->where('created_at', '>=', $thisMonthStart)
            ->count();
        $newVendorsThisMonth = Tenant::where('created_at', '>=', $thisMonthStart)->count();
        
        // Customer verification stats
        $verifiedCustomers = User::role('customer')
            ->whereNotNull('email_verified_at')
            ->count();
        $unverifiedCustomers = $totalCustomers - $verifiedCustomers;
        $verificationRate = $totalCustomers > 0 ? round(($verifiedCustomers / $totalCustomers) * 100) : 0;
        
        // Health score calculation (simple example)
        $healthScore = min(100, round(
            ($approvalRate * 0.4) + 
            ($verificationRate * 0.3) + 
            (min(100, $newCustomersThisMonth * 10) * 0.2) + 
            (min(100, $newVendorsThisMonth * 10) * 0.1)
        ));

        $approvedTenants = Tenant::where('is_approved', true)->get();

        $centralCategories = Category::orderBy('name')->get();

        // Start every category with zero stores
        $categoryStoreCounts = [];
        foreach ($centralCategories as $category) {
            $categoryStoreCounts[$category->id] = 0;
        }

        // Loop through each approved tenant and count which categories appear in that tenant
        foreach ($approvedTenants as $tenant) {
            $tenant->run(function () use (&$categoryStoreCounts) {
                $usedCategoryIds = Product::query()
                    ->whereNotNull('category_id')
                    ->distinct()
                    ->pluck('category_id');

                foreach ($usedCategoryIds as $categoryId) {
                    if (array_key_exists($categoryId, $categoryStoreCounts)) {
                        $categoryStoreCounts[$categoryId]++;
                    }
                }
            });
        }

        $totalStores = $approvedTenants->count();

        $categoryBreakdown = $centralCategories
            ->map(function ($category) use ($categoryStoreCounts, $totalStores) {
                $count = $categoryStoreCounts[$category->id] ?? 0;

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color ?: '#6366f1',
                    'count' => $count,
                    'pct' => $totalStores > 0
                        ? round(($count / $totalStores) * 100)
                        : 0,
                ];
            })
            ->filter(fn ($row) => $row['count'] > 0)
            ->values();
        // Health score — canonical formula via PlatformHealthService
        $phs = PlatformHealthService::compute(
            totalVendors:          $totalVendors,
            activeVendors:         $activeVendors,
            totalCustomers:        $totalCustomers,
            verifiedCustomers:     $verifiedCustomers,
            newCustomersThisMonth: $newCustomersThisMonth,
            newVendorsThisMonth:   $newVendorsThisMonth,
        );
        $healthScore = $phs['score'];
        
        return Inertia::render('admin/Reports', [
            'overview' => [
                'totalVendors'         => $totalVendors,
                'activeVendors'        => $activeVendors,
                'pendingVendors'       => $pendingVendors,
                'totalCustomers'       => $totalCustomers,
                'approvalRate'         => $approvalRate,
                'newVendorsThisMonth'  => $newVendorsThisMonth,
                'newCustomersThisMonth'=> $newCustomersThisMonth,
                'healthScore'          => $healthScore,
                'healthComponents'     => $phs['components'],
            ],
            'vendorPerformance' => [
                'topVendors' => $topVendors,
                'monthlyRegistrations' => $vendorGrowth,
                'approvalFunnel' => [
                    ['label' => 'Total Registered', 'value' => $totalVendors],
                    ['label' => 'Approved', 'value' => $activeVendors],
                    ['label' => 'Pending Review', 'value' => $pendingVendors],
                ],
            ],
            'customerActivity' => [
                'totalCustomers' => $totalCustomers,
                'monthlySignups' => $customerGrowth,
                'verified' => $verifiedCustomers,
                'unverified' => $unverifiedCustomers,
                'verificationRate' => $verificationRate,
                'recentSignups' => $recentCustomers,
            ],
            'categoryBreakdown' => [
                'breakdown' => $categoryBreakdown,
                'totalUnique' => $categoryBreakdown->count(),
            ],
        ]);
    }
}