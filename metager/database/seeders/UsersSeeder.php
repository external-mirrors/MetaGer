<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();
        DB::table('users')->insert(
            [
                'email' => config("metager.metager.admin.user"),
                'name' => config("metager.metager.admin.user"),
                'password' => Hash::make(config("metager.metager.admin.password")),
            ]
        );
    }
}
