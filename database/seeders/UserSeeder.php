<?php

declare(strict_types=1);

namespace Database\Seeders;

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
                'name'              => 'Andrea Rossi',
                'email'             => 'andrea.rossi@email.it',
                'website'           => 'www.andrearossi.it',
                'linkedin'          => 'https://linkedin.com/in/andrear',
                'facebook'          => 'https://facebook.com/andrear',
                'instagram'         => 'https://instagram.com/andrear',
                'is_admin'          => false,
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
                'updated_at'        => now(),
                'created_at'        => now(),
            ],
            [
                'name'              => 'Marco Bianchi',
                'email'             => 'marco.bianchi@email.it',
                'website'           => 'www.marcobianchi.it',
                'linkedin'          => 'https://linkedin.com/in/marcob',
                'facebook'          => 'https://facebook.com/marcob',
                'instagram'         => 'https://instagram.com/marcob',
                'is_admin'          => false,
                'password'          => bcrypt('password'),
                'email_verified_at' => null,
                'updated_at'        => now(),
                'created_at'        => now(),
            ],
            [
                'name'              => 'Anna Verdi',
                'email'             => 'anna.verdi@email.it',
                'website'           => 'www.annav.it',
                'linkedin'          => 'https://linkedin.com/in/annav',
                'facebook'          => 'https://facebook.com/annav',
                'instagram'         => 'https://instagram.com/annav',
                'is_admin'          => false,
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
                'updated_at'        => now(),
                'created_at'        => now(),
            ],
            [
                'name'              => 'Gianni Ghidoni',
                'email'             => 'gianni.ghidoni@email.it',
                'website'           => 'www.giannighidoni.it',
                'linkedin'          => 'https://linkedin.com/in/giannig',
                'facebook'          => 'https://facebook.com/giannig',
                'instagram'         => 'https://instagram.com/giannig',
                'is_admin'          => true,
                'password'          => bcrypt('password'),
                'email_verified_at' => null,
                'updated_at'        => now(),
                'created_at'        => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
