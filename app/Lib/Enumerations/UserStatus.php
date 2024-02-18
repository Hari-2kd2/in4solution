<?php

namespace App\Lib\Enumerations;

class UserStatus
{
     public static $PROBATION_PERIOD  = 0;
     public static $CONFIRMED  = 1;
     public static $RESIGNED  = 2;

     public static $ACTIVE = 1;
     public static $INACTIVE = 2;

     public static $TERMINATE = 3;
     public static $PERMANENT  = 4;
     public static $Resigned  = 5;
     public static $Left  = 6;
     public static $Abscond  = 7;
     public static $Death  = 8;

     // Resigned
     // Left
     // Abscond
     // Death
     // Terminated
     public static function statusList($status = '')
     {
          $statusList = [
               1 => 'Active',
               2 => 'Inactive',
          ];
          return isset($statusList[$status]) ?  $statusList[$status] : $statusList;
     }

     public static function permenantStatusList($status = '')
     {
          $statusList = [
               0 => 'Probation',
               1 => 'Confirmed',
               2 => 'Resigned',
          ];
          return isset($statusList[$status]) ?  $statusList[$status] : $statusList;
     }

     public static function permanentStatusColor($status = '')
     {
          $statusList = [
               0 => '#FDDA0D',
               1 => '#00FF00',
               2 => '#FF474C',
          ];
          
          return isset($statusList[$status]) ?  $statusList[$status] : $statusList;
     }

     public static function leavingList($status = '')
     {
          $leavingList = [
               3 => 'Terminate',
               4 => 'Permanent',
               5 => 'Resigned',
               6 => 'Left',
               7 => 'Abscond',
               8 => 'Death',
          ];
          return $status != '' ? (isset($leavingList[$status]) ? $leavingList[$status] : '-') : $leavingList;
     }
}
