<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        DB::table('roles')->insert([
            'name' => 'Super Admin',
            'slug' => 'super-admin'
        ],);

        DB::table('roles')->insert([
            'name' => 'Customer',
            'slug' => 'customer'
        ]);

        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@miniaspireapp.com',
            'password' => bcrypt('admin@2022'),
        ]);

        DB::table('users_roles')->insert([
            'user_id' => 1,
            'role_id' => 1
        ]);
    }
}
