<?php

namespace App\Http\Controllers;

use App\Models\CompanyUser;
use App\Models\Company;
use App\Models\User;

use Illuminate\Http\Request;



class CompanyUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function store(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:owner,admin,staff,viewer',
        ]);

        $company->users()->attach($validated['user_id'], [
            'role' => $validated['role']
        ]);

        return response()->json([
            'message' => 'User added to company'
        ]);
    }

    public function updateRole(Request $request, Company $company, User $user)
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'role' => 'required|in:owner,admin,staff,viewer',
        ]);

        $company->users()->updateExistingPivot(
            $user->id,
            ['role' => $validated['role']]
        );

        return response()->json([
            'message' => 'Role updated'
        ]);
    }

    public function destroy(Company $company, User $user)
    {
        $this->authorize('update', $company);

        $company->users()->detach($user->id);

        return response()->noContent();
    }

        public function index(Company $company)
        {
            $this->authorize('view', $company);
    
            $users = $company->users()->withPivot('role')->get();
    
            return response()->json($users);
        }
    
        public function show(Company $company, User $user)
        {
            $this->authorize('view', $company);
    
            $user = $company->users()->where('user_id', $user->id)->withPivot('role')->firstOrFail();
    
            return response()->json($user);
        }
    
        public function update(Request $request, Company $company, User $user)
        {
            $this->authorize('update', $company);
    
            $validated = $request->validate([
                'role' => 'required|in:owner,admin,staff,viewer',
            ]);
    
            $company->users()->updateExistingPivot(
                $user->id,
                ['role' => $validated['role']]
            );
    
            return response()->json([
                'message' => 'Role updated'
            ]);
        }
}
