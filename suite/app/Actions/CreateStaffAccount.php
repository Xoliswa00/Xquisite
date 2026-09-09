<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateStaffAccount
{
    /**
     * Create a staff account for a tenant. The caller resolves the plaintext
     * password (owner-set or generated) and owns role assignment; this action
     * just persists the user and forces a password change on first login.
     *
     * @param array{name:string,email:string,tenant_id:int,password:string} $data
     */
    public function execute(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'tenant_id' => $data['tenant_id'],
            'require_password_change' => true,
        ]);
    }
}
