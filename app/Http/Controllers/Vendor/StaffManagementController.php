<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;

class StaffManagementController extends Controller
{
    public function index()
    {
        // Get all users except the vendor owner (who is likely the first user or has is_admin=true)
        // Adjust logic based on how you distinguish owner from staff
        $staff = User::where('id', '!=', auth()->id())
            ->whereDoesntHave('roles', function($q) {
                $q->where('name', 'vendor');
            })
            ->with('roles', 'permissions')
            ->get();

        return Inertia::render('vendor/Staff', [
            'staff' => $staff,
            'availablePermissions' => Permission::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|unique:users,login_id',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'login_id' => $validated['login_id'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('staff');

        return back()->with('success', 'Staff account created successfully!');
    }

    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'array',
        ]);

        $user->syncPermissions($validated['permissions']);

        return back()->with('success', 'Permissions updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('vendor')) {
            abort(403, 'Cannot delete vendor owner.');
        }

        $user->delete();

        return back()->with('success', 'Staff account deleted successfully!');
    }
}
