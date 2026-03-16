<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('unverified customer can access customer profile page', function () {
    Role::findOrCreate('customer');

    $user = User::factory()->unverified()->create();
    $user->assignRole('customer');

    $this->actingAs($user)
        ->get(route('customer.profile'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('customer/Profile'));
});
