<?php

namespace App;

use App\Model\Role;
use App\Model\Branch;
use App\Model\Employee;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements JWTSubject
{
    // use BranchTrait;
    // use CrudTrait;

    public const PASS_MIN=5, PASS_MAX=16;
    protected $table = 'user';
    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'role_id', 'branch_id', 'user_name', 'password', 'status', 'created_by', 'updated_by', 'device_employee_id', 'google2fa_secret'];

    protected $hidden = [
        'password', 'remember_token',
    ];



    public static function scopeUserRole($query, $role)
    {
        return $query->where('role_id', $role);
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'role_id', 'role_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function getIsAdminAttribute()
    {
        return $this->role()->where('role_id', 1)->exists();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    // public function scopeBranch($query)
    // {
    //     $branch_id = session()->get('branch_id');

    //     if (auth()->user()->hasRole('superadministrator')) {
    //         // $branch = DB::table('find_branch')->where('user_id', auth()->user()->id)->first();

    //         // if ($branch_id != '0' && $branch_id) {
    //         return $query->where('branch_id', $branch_id)->get();
    //         // } else {
    //         //     return $query->get();
    //         // }
    //     } else {
    //         return $query->where('branch_id', $branch_id)->get();
    //     }
    // }

    // // Relationships Functions
    // // public function employee(): BelongsTo
    // // {
    // //     return $this->belongsTo(Employee::class);
    // // }

    // public function branches(): BelongsToMany
    // {
    //     return $this->belongsToMany(Branch::class);
    // }

    // public function myBranchUsers()
    // {
    //     return $this->branches()->Where('branch_id', 1);
    // }
}
