<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function createAdminUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('admin');

        return $user;
    }
}
