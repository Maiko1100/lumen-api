<?php

use Illuminate\Database\Seeder;

class YearTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('year')->insert([
            'year' => 2013,
        ]);
        DB::table('year')->insert([
            'year' => 2014,
        ]);
        DB::table('year')->insert([
            'year' => 2015,
        ]);
        DB::table('year')->insert([
            'year' => 2016,
        ]);
        DB::table('year')->insert([
            'year' => 2017,
        ]);
    }
}
