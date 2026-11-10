<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('admin:create {--name=} {--email=} {--password=}')]
#[Description('Create an admin account. Prompts for missing values and hashes the password.')]
class CreateAdminAccount extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        (new RolePermissionSeeder)->run();

        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->password();

        if ($password === null) {
            return self::FAILURE;
        }

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', Password::min(8)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $role = Role::query()->where('role_name', 'Admin')->first();

        if ($role === null) {
            $this->error('The Admin role is not available.');

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $this->info('Admin account created for '.$email.'.');

        return self::SUCCESS;
    }

    private function password(): ?string
    {
        $password = $this->option('password');

        if (is_string($password) && $password !== '') {
            return $password;
        }

        $password = $this->secret('Password');
        $confirmation = $this->secret('Confirm password');

        if ($password !== $confirmation) {
            $this->error('The password confirmation does not match.');

            return null;
        }

        return $password;
    }
}
