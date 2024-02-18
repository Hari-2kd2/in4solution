<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
// use App\Traits\BranchTrait;

class ProfessionalTax extends Model
{
    // use BranchTrait;

    protected $table = 'professional_tax';
    protected $primaryKey = 'pt_id';

    protected $fillable = ['months', 'amount', 'status', 'branch_id'];
}
