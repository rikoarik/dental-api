<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::firstOrCreate([
            'email' => 'admin@clinic.com',
        ], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
        ]);

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->call(DummyDataSeeder::class);
    }
}
