<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'id' => Role::USER,
                'title' => 'User',
                'slug' => 'user'
            ],
            [
                'id' => Role::ORGANIZER,
                'title' => 'Organizer',
                'slug' => 'organizer'
            ],
            [
                'id' => Role::SPEAKER,
                'title' => 'Speaker',
                'slug' => 'speaker'
            ],
            [
                'id' => Role::ADMIN,
                'title' => 'Admin',
                'slug' => 'admin'
            ]
        ];

        Role::insert($roles);
    }
}
