<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

trait CrudTrait
{
    public static function getAllTraits()
    {
        $loggedSessionData = session()->get('logged_session_data', []);

        $branchId = null;

        if (!empty($loggedSessionData) && isset($loggedSessionData['branch_id'])) {
            $encryptedBranchId = $loggedSessionData['branch_id'];
            $branchId = $encryptedBranchId;
        }
        return self::where('branch_id', $branchId)->get();
    }

    public static function getByIdTrait($id)
    {
        $loggedSessionData = session()->get('logged_session_data', []);

        $branchId = null;

        if (!empty($loggedSessionData) && isset($loggedSessionData['branch_id'])) {
            $encryptedBranchId = $loggedSessionData['branch_id'];
            $branchId = $encryptedBranchId;
        }
        return self::where('branch_id', $branchId)->find($id);
    }

    public static function createTrait(array $attributes)
    {
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $selectedBranchId = session('selected_branchId');

        if ($branchId !== null && $roleId != 1) {
            $attributes['branch_id'] = $branchId;
        } elseif ($selectedBranchId !== null && $roleId == 1) {
            $attributes['branch_id'] = $selectedBranchId;
        } else {
        }

        return self::create($attributes);
    }
    public static function insertTrait(array $attributes)
    {
        $loggedSessionData = session()->get('logged_session_data', []);

        $branchId = null;

        if (!empty($loggedSessionData) && isset($loggedSessionData['branch_id'])) {
            $encryptedBranchId = $loggedSessionData['branch_id'];
            $branchId = $encryptedBranchId;
        }
        $attributes['branch_id'] = $branchId;
        return self::insert($attributes);
    }

    public function updateTrait(array $attributes)
    {
        return $this->update($attributes);
    }

    public function deleteTrait()
    {
        return $this->delete();
    }
}
