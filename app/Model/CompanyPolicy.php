<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CompanyPolicy extends Model
{
    protected $table = 'company_policy';
    protected $primaryKey = 'company_policy_id';

    protected $fillable = [
        'company_policy_id', 'policy_type', 'branch_id', 'title', 'file', 'created_by', 'updated_by'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }
}
