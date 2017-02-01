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
            'email' => 'johndoe@example.com',
            'password' => app('hash')->make('johndoe'),
            'remember_token' => str_random(10),
        ]);
    }
}
