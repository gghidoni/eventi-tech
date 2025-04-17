<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;



class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $techCompanies = [
            'PHP Verona',
            'Laravel Italia',
            'JS Pisa',
            'React Roma',
            'Vue Milano',
            'WordPress Firenze',
            'Symfony Torino',
            'DevOps Napoli',
            'Docker Bologna',
            'Python Palermo',
            'Angular Bari',
            'Tech Meetup Genova',
            'AI Torino',
            'Data Science Milano',
            'Flutter Venezia',
            'C++ Trento',
            'Java Ancona',
            'Ruby Bari',
            'Node.js Lecce',
            'Next.js Cagliari',
        ];

        $users = [
            [
                'name' => 'Andrea Rossi',
                'email' => 'user@email.it',
                'roles' => ['user']
            ],
            [
                'name' => 'Azienda srl',
                'email' => 'organizer1@email.it',
                'roles' => ['organizer']
            ],
            [
                'name' => 'Marco Bianchi',
                'email' => 'user+organizer@email.it',
                'roles' => ['user', 'organizer']
            ],
            [
                'name' => 'Gianni Ghidoni',
                'email' => 'admin@email.it',
                'roles' => ['admin']
            ],
            [
                'name' => 'Organization',
                'email' => 'organizer2@email.it',
                'roles' => ['organizer']
            ],
        ];

        foreach ($users as $user) {
            $userModel = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => bcrypt('password'),
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            $roles = Role::whereIn('slug', $user['roles'])->get();

            $userModel->roles()->attach($roles);


            $userMeta = [
                'user_id' => $userModel->id,
                'website' => 'www' . Str::slug($userModel->name) . '.it',
                'facebook' => 'https://facebook.com/' . $faker->userName,
                'instagram' => 'https://instagram.com/' . $faker->userName,
                'linkedin' => 'https://linkedin.com/in/' . $faker->userName,
            ];

            if (in_array('organizer', $user['roles'])) {
                $org_name = $techCompanies[array_rand($techCompanies)];
                $website = 'https://www.' . Str::slug($org_name) . '.it';
                $userMeta['org_name'] = $org_name;
                $userMeta['website'] = $website;
            }

            UserMeta::create($userMeta);
        }


    }
}
