<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\User;





class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        //
    if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Platform owner sees everything
        if ($user->isPlatformOwner()) {
            return;
        }

        if ($user->company_id) {
            $builder->where(
                $model->getTable().'.company_id',
                $user->company_id
            );
        } else {
            // If user has no company, return no results
            $builder->whereRaw('1 = 0');
        }
    }








    
}
