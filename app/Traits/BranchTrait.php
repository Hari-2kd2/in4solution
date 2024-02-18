<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;


trait BranchTrait
{
    protected static function bootBranchTrait()
    {
        // static::addGlobalScope('branch', function (Builder $builder) {
        //     $branchId = session('logged_session_data.branch_id');
        //     $roleId = session('logged_session_data.role_id');
        //     $selectedbranchId = session('selected_branchId');
        //     if ($branchId !== null && $roleId !== 1) {
        //         $builder->where('branch_id', $branchId);
        //     } elseif ($selectedbranchId != null && $roleId == 1) {
        //         $builder->where('branch_id', $selectedbranchId);
        //     }
        // });
    }
}
