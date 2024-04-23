<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'syaifullah',
                'username' => 'adminipung',
                'email' => 'admin1@gmail.com',
                'password' => 'adminipung',
                'role' => 'admin'
            ],
            [
                'name' => 'Tendy',
                'username' => 'admintendy',
                'email' => 'admin2@gmail.com',
                'password' => 'admintendy',
                'role' => 'admin'
            ],
            [
                'name' => 'fauzy',
                'username' => 'adminfauzy',
                'email' => 'admin3@gmail.com',
                'password' => 'adminfauzy',
                'role' => 'admin'
            ],
        ];

        // Insert data ke dalam tabel 'users'
        DB::table('users')->insert($data);
    }
}
