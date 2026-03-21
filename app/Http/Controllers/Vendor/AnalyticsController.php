<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __invoke(): Response
    {
        $status = 'completed';

        $startOfWeek = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $previousStart = $startOfWeek->copy()->subWeek();
        $previousEnd = $endOfWeek->copy()->subWeek();

        $weeklySales = $this->buildWeeklySales($status, $startOfWeek, $endOfWeek);
        $categoryMix = $this->buildCategoryMix($status, $startOfWeek, $endOfWeek);
        $peakWindows = $this->buildPeakWindows($status, $startOfWeek, $endOfWeek);
        $topProducts = $this->buildTopProducts($status, $startOfWeek, $endOfWeek, $previousStart, $previousEnd);

        $currentRevenue = collect($weeklySales)->sum('revenue');

        $previousRevenue = (float) Order::query()
            ->where('status', $status)
            ->whereBetween('placed_at', [$previousStart, $previousEnd])
            ->sum('total');

        $growthSignal = $this->formatGrowth($currentRevenue, $previousRevenue);

        $recentInsights = $this->buildInsights(
            $weeklySales,
            $categoryMix,
            $peakWindows,
            $topProducts,
            $growthSignal
        );

        return Inertia::render('vendor/Analytics', [
            'weeklySales' => $weeklySales,
            'categoryMix' => $categoryMix,
            'peakWindows' => $peakWindows,
            'topProducts' => $topProducts,
            'recentInsights' => $recentInsights,
            'growthSignal' => $growthSignal,
        ]);
    }

private function buildWeeklySales(string $status, CarbonInterface $start, CarbonInterface $end): array
    {
        $rows = Order::query()
            ->where('status', $status)
            ->whereBetween('placed_at', [$start, $end])
            ->selectRaw('DATE(placed_at) as order_date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('order_date')
            ->get()
            ->keyBy('order_date');

        return collect(range(0, 6))
            ->map(function (int $offset) use ($start, $rows) {
                $date = $start->copy()->addDays($offset);
                $key = $date->toDateString();
                $row = $rows->get($key);

                return [
                    'day' => $date->format('D'),
                    'revenue' => round((float) ($row->revenue ?? 0), 2),
                    'orders' => (int) ($row->orders ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function buildCategoryMix(string $status, CarbonInterface $start, CarbonInterface $end): array
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', $status)
            ->whereBetween('orders.placed_at', [$start, $end])
            ->selectRaw('products.category_id, SUM(order_items.total) as revenue')
            ->groupBy('products.category_id')
            ->get();

        $categoryIds = $rows->pluck('category_id')->filter()->unique()->values();

        $categoryNames = Category::query()
            ->whereIn('id', $categoryIds)
            ->pluck('name', 'id');

        $totalRevenue = (float) $rows->sum('revenue');

        if ($totalRevenue <= 0) {
            return [];
        }

        return $rows
            ->map(function ($row) use ($categoryNames, $totalRevenue) {
                $label = $row->category_id
                    ? ($categoryNames[$row->category_id] ?? 'Unknown Category')
                    : 'Uncategorized';

                return [
                    'label' => $label,
                    'value' => (int) round(((float) $row->revenue / $totalRevenue) * 100),
                ];
            })
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    private function buildPeakWindows(string $status, CarbonInterface $start, CarbonInterface $end): array
    {
        $rows = Order::query()
            ->where('status', $status)
            ->whereBetween('placed_at', [$start, $end])
            ->selectRaw("
                CASE
                    WHEN HOUR(placed_at) >= 8 AND HOUR(placed_at) < 10 THEN '8:00 AM - 10:00 AM'
                    WHEN HOUR(placed_at) >= 11 AND HOUR(placed_at) < 13 THEN '11:00 AM - 1:00 PM'
                    WHEN HOUR(placed_at) >= 14 AND HOUR(placed_at) < 16 THEN '2:00 PM - 4:00 PM'
                    WHEN HOUR(placed_at) >= 17 AND HOUR(placed_at) < 19 THEN '5:00 PM - 7:00 PM'
                    ELSE 'Other'
                END as label,
                COUNT(*) as orders
            ")
            ->groupBy('label')
            ->get()
            ->pluck('orders', 'label');

        $slots = [
            '11:00 AM - 1:00 PM',
            '2:00 PM - 4:00 PM',
            '5:00 PM - 7:00 PM',
            '8:00 AM - 10:00 AM',
        ];

        return collect($slots)
            ->map(fn ($label) => [
                'label' => $label,
                'orders' => (int) ($rows[$label] ?? 0),
            ])
            ->sortByDesc('orders')
            ->values()
            ->all();
    }

    private function buildTopProducts(
        string $status,
        CarbonInterface $start,
        CarbonInterface $end,
        CarbonInterface $previousStart,
        CarbonInterface $previousEnd
    ): array {
        $current = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', $status)
            ->whereBetween('orders.placed_at', [$start, $end])
            ->selectRaw('products.id as product_id, products.name as product_name, SUM(order_items.quantity) as orders_count, SUM(order_items.total) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('orders_count')
            ->limit(5)
            ->get();

        $previous = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', $status)
            ->whereBetween('orders.placed_at', [$previousStart, $previousEnd])
            ->selectRaw('products.id as product_id, SUM(order_items.quantity) as orders_count')
            ->groupBy('products.id')
            ->get()
            ->pluck('orders_count', 'product_id');

        return $current
            ->map(function ($row) use ($previous) {
                $previousOrders = (int) ($previous[$row->product_id] ?? 0);
                $currentOrders = (int) $row->orders_count;

                return [
                    'name' => $row->product_name,
                    'orders' => $currentOrders,
                    'revenue' => round((float) $row->revenue, 2),
                    'growth' => $this->formatGrowth($currentOrders, $previousOrders),
                ];
            })
            ->values()
            ->all();
    }

    private function buildInsights(
        array $weeklySales,
        array $categoryMix,
        array $peakWindows,
        array $topProducts,
        string $growthSignal
    ): array {
        $bestDay = collect($weeklySales)->sortByDesc('revenue')->first();
        $topCategory = collect($categoryMix)->sortByDesc('value')->first();
        $topWindow = collect($peakWindows)->sortByDesc('orders')->first();
        $topProduct = collect($topProducts)->first();

        $insights = [];

        if (($bestDay['revenue'] ?? 0) > 0) {
            $insights[] = "{$bestDay['day']} is your strongest sales day this week with {$bestDay['orders']} completed orders.";
        } else {
            $insights[] = 'No completed orders have been recorded yet for the current week.';
        }

        if ($topCategory) {
            $insights[] = "{$topCategory['label']} leads your category mix at {$topCategory['value']}% of weekly revenue.";
        }

        if ($topWindow && $topWindow['orders'] > 0) {
            $insights[] = "{$topWindow['label']} is the busiest fulfillment window so far.";
        }

        if ($topProduct) {
            $insights[] = "{$topProduct['name']} is currently your top-selling product by order volume.";
        }

        if ($growthSignal !== '0%') {
            $insights[] = "Week-over-week revenue trend is {$growthSignal}.";
        }

        return array_slice($insights, 0, 3);
    }

    private function formatGrowth(float|int $current, float|int $previous): string
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous <= 0) {
            return $current > 0 ? 'New' : '0%';
        }

        $change = (($current - $previous) / $previous) * 100;
        $rounded = (int) round($change);

        return ($rounded > 0 ? '+' : '') . $rounded . '%';
    }
}