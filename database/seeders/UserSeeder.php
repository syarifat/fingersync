<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        echo "👨‍🏫 Seeding Admin & Guru...\n";
        $faker = Faker::create('id_ID');

        // Admin
        $uAdmin = DB::table('users')->insertGetId([
            'nama' => 'Administrator', 'username' => 'admin', 'password' => Hash::make('password'), 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('admin')->insert([
            'user_id' => $uAdmin, 'nama' => 'Super Admin', 'username' => 'admin', 'created_at' => now(), 'updated_at' => now()
        ]);
    }
}