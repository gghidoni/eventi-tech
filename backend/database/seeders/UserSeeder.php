<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
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
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => 'Azienda srl',
                'email' => 'email.organizer@email.it',
                'password' => bcrypt('password'),
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => 'Marco Bianchi',
                'email' => 'email.speaker@email.it',
                'password' => bcrypt('password'),
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'name' => 'Gianni Ghidoni',
                'email' => 'email.admin@email.it',
                'password' => bcrypt('password'),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        ];

        User::insert($users);

        foreach (User::all() as $user) {
            $user->roles()->attach(Role::find($user->id));
        }

    }
}
