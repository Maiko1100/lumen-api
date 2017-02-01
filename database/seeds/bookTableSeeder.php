<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('books')->insert([
            'name' => 'John Doe',
            'year' => 'johndoe@example.com',
            'author' => app('hash')->make('johndoe'),
]);
    }
}
