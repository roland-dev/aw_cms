<?php

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
       DB::table('permissions')->insert(
          [
              'code' => 'admin',
              'name' => '1',
              'pre_code' => 'root',
              'active'=>'1',
          ]
      );
    }
}
