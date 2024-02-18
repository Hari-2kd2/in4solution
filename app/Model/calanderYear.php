<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class calanderYear extends Model
{
    protected $table = 'calendar_year';
    protected $primaryKey = 'year_id';
    public $timestamps = false;

    protected $fillable = [
        'year_name', 'year_start', 'year_end'
    ];

    public static function currentYear($curYear=null) {
        if($curYear===null) {
            $curYear = date('Y');
        }
        $calanderYear = calanderYear::whereRaw("YEAR(year_start)='$curYear' AND YEAR(year_end)='$curYear' ")->first();
        if(!$calanderYear) {
            $calanderYear = new calanderYear;
            // abort(403, 'Calander / Finacial year record not found');
        }
        return $calanderYear;
    }

    public static function calanderYearList($array=true) {
        if($array===true) {
            $calanderYear = calanderYear::where('year_status', 1)->limit(10)->get()->toArray();
        } else {
            $calanderYear = calanderYear::where('year_status', 1)->limit(10)->get();
        }
        return $calanderYear;
    }

    public static function yearList($array=true) {
        // $calanderYear = calanderYear::whereRaw("YEAR(year_start)='$curYear' AND YEAR(year_end)='$curYear' ")->first();
        $calanderYear = calanderYear::limit(10)->get()->toArray();
        $data=[];
        if($array===true) {
            foreach ($calanderYear as $key => $calanderYearOne) {
                $data[$calanderYearOne['year_id']] = $calanderYearOne['year_name'];
            }
            return $data;
        }
        return $calanderYear;
    }
}
