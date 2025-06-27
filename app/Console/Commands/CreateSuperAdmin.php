<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Enums\StatusEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-super-admin 
                           {--name= : The name of the super admin}
                           {--email= : The email address}  
                           {--password= : The password (will prompt if not provided)}
                           {--phone= : Phone number (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new super-admin user (the only secure way to create super-admin)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Creating Super Admin User');
        $this->info('This is the ONLY secure way to create super-admin users.');
        $this->newLine();

        // Get input data
        $name = $this->option('name') ?: $this->ask('Super Admin Name');
        $email = $this->option('email') ?: $this->ask('Email Address');
        $phone = $this->option('phone') ?: $this->ask('Phone Number (optional)', null);
        
        // Get password securely
        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Password (min 8 characters)');
            $confirmPassword = $this->secret('Confirm Password');
            
            if ($password !== $confirmPassword) {
                $this->error('❌ Passwords do not match!');
                return 1;
            }
        }

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone' => $phone,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            $this->error('❌ Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("  • $error");
            }
            return 1;
        }

        // Check if super-admin role exists
        if (!\Spatie\Permission\Models\Role::where('name', 'super-admin')->exists()) {
            $this->error('❌ Super-admin role does not exist! Run seeders first: php artisan db:seed --class=RolePermissionSeeder');
            return 1;
        }

        // Confirm creation
        $this->table(['Field', 'Value'], [
            ['Name', $name],
            ['Email', $email],
            ['Phone', $phone ?: 'Not provided'],
            ['Role', 'super-admin'],
            ['Status', 'active'],
        ]);

        if (!$this->confirm('Create this super-admin user?')) {
            $this->info('❌ Cancelled by user');
            return 0;
        }

        try {
            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'phone' => $phone,
                'restaurant_id' => null, // Super admins don't belong to specific restaurants
                'status' => StatusEnum::ACTIVE->value,
            ]);

            // Assign super-admin role
            $user->assignRole('super-admin');

            $this->newLine();
            $this->info('✅ Super Admin created successfully!');
            $this->info("👤 User ID: {$user->id}");
            $this->info("📧 Email: {$user->email}");
            $this->info("🔑 Role: super-admin");
            $this->newLine();
            $this->warn('🔒 SECURITY NOTE: This user has FULL system access!');
            $this->warn('🚨 Keep credentials secure and change password regularly.');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to create super admin: ' . $e->getMessage());
            return 1;
        }
    }
}
