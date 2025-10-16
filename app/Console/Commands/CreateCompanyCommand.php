<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;

class CreateCompanyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:create {email} {company_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new company and assign it to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $companyName = $this->argument('company_name');

        // Find the user by email
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email '{$email}' not found.");

            return 1;
        }

        // Create the company
        $company = Company::create([
            'name' => $companyName,
            'owner_id' => $user->id,
            'settings' => [
                'timezone' => $user->timezone ?? 'UTC',
                'locale' => $user->locale ?? 'en',
            ],
        ]);

        // Attach user to company as network (company admin)
        $company->users()->attach($user->id, ['role' => 'network']);

        // Set as current company
        $user->update(['current_company_id' => $company->id]);

        $this->info("✅ Company '{$companyName}' created successfully!");
        $this->info("👤 User '{$user->name}' ({$user->email}) is now the admin");
        $this->info("🏢 Company ID: {$company->id}");
        $this->info("🔗 User's current company ID: {$user->current_company_id}");

        return 0;
    }
}
