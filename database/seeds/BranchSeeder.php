<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $time = Carbon::now();
        DB::table('branch')->truncate();
        DB::table('branch')->insert(
            [
                ['branch_name' => 'First Branch', 'created_at' => $time, 'updated_at' => $time],
                ['branch_name' => 'Second Branch', 'created_at' => $time, 'updated_at' => $time],
                ['branch_name' => 'Thired Branch', 'created_at' => $time, 'updated_at' => $time],
                ['branch_name' => 'Fourth Branch', 'created_at' => $time, 'updated_at' => $time],

            ]

        );
    }
}
