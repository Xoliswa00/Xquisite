<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateStaffAccount
{
    /**
     * Create a new staff account with auto-generated temporary password.
     * Staff will be required to change password on first login.
     */
    public function execute(array $data): User
    {
        $temporaryPassword = Str::password(12);

        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($temporaryPassword),
            'tenant_id' => $data['tenant_id'],
            'role' => $data['role'] ?? 'staff',
            'require_password_change' => true,
        ]);

        if (isset($data['role'])) {
            $staff->assignRole($data['role']);
        }

        return $staff;
    }
}
