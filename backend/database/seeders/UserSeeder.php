<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Andrea Rossi',
                'email' => 'email.user@email.it',
                'password' => bcrypt('password'),
                'role_id' => Role::USER,
                'updated_at' => now(),
                'created_at' => now()
            ],
            [
                'name' => 'Azienda srl',
                'email' => 'email.organizer@email.it',
                'password' => bcrypt('password'),
                'role_id' => Role::ORGANIZER,
                'updated_at' => now(),
                'created_at' => now()
            ],
            [
                'name' => 'Marco Bianchi',
                'email' => 'email.speaker@email.it',
                'password' => bcrypt('password'),
                'role_id' => Role::SPEAKER,
                'updated_at' => now(),
                'created_at' => now()
            ],
            [
                'name' => 'Gianni Ghidoni',
                'email' => 'email.admin@email.it',
                'password' => bcrypt('password'),
                'role_id' => Role::ADMIN,
                'updated_at' => now(),
                'created_at' => now()
            ],
        ];

        User::insert($users);

    }
}
