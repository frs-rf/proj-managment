<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@pmtracker.test'],
            [
                'name' => 'Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'employee_id' => 'EMP-001',
                'status' => 'Active',
            ]
        );
        $admin->assignRole('administrator');
    }
}
