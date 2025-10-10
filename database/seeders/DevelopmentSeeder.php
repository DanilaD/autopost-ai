<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding development data...');

        // Create admin user
        $this->command->info('Creating admin user...');
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@autopost.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $this->command->info("✅ Admin created: {$admin->email}");

        // Create company owned by admin
        $this->command->info('Creating test company...');
        $company = Company::create([
            'name' => 'Test Company',
            'owner_id' => $admin->id,
            'settings' => [
                'timezone' => 'UTC',
                'locale' => 'en',
            ],
        ]);
        $this->command->info("✅ Company created: {$company->name}");

        // Attach admin to company
        $company->users()->attach($admin->id, [
            'role' => UserRole::ADMIN->value,
        ]);
        $admin->update(['current_company_id' => $company->id]);
        $this->command->info('✅ Admin attached to company');

        // Create regular user
        $this->command->info('Creating regular user...');
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@autopost.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'current_company_id' => $company->id,
        ]);
        $company->users()->attach($user->id, [
            'role' => UserRole::USER->value,
        ]);
        $this->command->info("✅ User created: {$user->email}");

        // Create network manager
        $this->command->info('Creating network manager...');
        $network = User::factory()->create([
            'name' => 'Network Manager',
            'email' => 'network@autopost.ai',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'current_company_id' => $company->id,
        ]);
        $company->users()->attach($network->id, [
            'role' => UserRole::NETWORK->value,
        ]);
        $this->command->info("✅ Network manager created: {$network->email}");

        // Summary
        $this->command->newLine();
        $this->command->info('🎉 Development data seeded successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@autopost.ai', 'password'],
                ['User', 'user@autopost.ai', 'password'],
                ['Network', 'network@autopost.ai', 'password'],
            ]
        );
        $this->command->info("Company: {$company->name} (ID: {$company->id})");
    }
}
