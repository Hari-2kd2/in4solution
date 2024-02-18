<?php

namespace App\Lib\Enumerations;

class AttendanceStatus
{
    public static $PRESENT  = 1; // present
    public static $ABSENT  = 2; // absent
    public static $LEAVE  = 3; // leaves
    public static $HOLIDAY  = 4; // public holiday
    public static $FUTURE  = 5;
    public static $UPDATE  = 6;
    public static $ERROR  = 7;
    public static $ONETIMEINPUNCH  = 8;
    public static $ONETIMEOUTPUNCH = 9;
    public static $LESSHOURS  = 10;
    public static $COMPOFF  = 11; // comp off
    public static $INCENTIVE  = 12;
    public static $OD  = 13; // od
    public static $WEEKOFF  = 14; // weekoff holiday
}
