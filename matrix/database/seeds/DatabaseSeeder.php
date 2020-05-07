<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
		DB::table('users')->insert([
			'name' =>'xiaojiang', 
			'email' => '2244606@qq.com',
			'password' =>'123456', 
            'active'=>'1',
		]);
    }
}
