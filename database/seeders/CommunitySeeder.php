<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $communities = [];

        foreach (User::all() as $user) {
            if ($user->id === 1) {
                $communities[] = [
                    'user_id'     => $user->id,
                    'name'        => 'Java Ancona',
                    'logo'        => null,
                    'website'     => 'www.javaancora.it',
                    'slug'        => 'java-ancona',
                    'facebook'    => 'https://facebook/pinco_pallo',
                    'linkedin'    => 'https://linkedin/pinco_pallo',
                    'instagram'   => 'https://instagram/pinco_pallo',
                    'description' => 'Java Italia è una community nazionale dedicata agli sviluppatori Java di ogni livello. Organizziamo eventi, meetup e conferenze per promuovere la condivisione della conoscenza, il networking tra professionisti e l’aggiornamento costante sulle ultime novità del linguaggio Java, dell’ecosistema Spring, di Jakarta EE e molto altro. Che tu sia un principiante o uno sviluppatore esperto, troverai sempre un ambiente accogliente, stimolante e orientato alla crescita professionale.',
                    'phone'       => '+393472810547',
                    'status'      => CommunityStatus::Active->value,
                ];
            } elseif ($user->id === 2) {
                $communities[] = [
                    'user_id'     => $user->id,
                    'name'        => 'Laravel Pordenone',
                    'logo'        => null,
                    'website'     => 'www.laravelprodenone.it',
                    'slug'        => 'laravel-pordenone',
                    'facebook'    => 'https://facebook/laravelprodenone',
                    'linkedin'    => 'https://linkedin/laravelprodenone',
                    'instagram'   => 'https://instagram/laravelprodenone',
                    'description' => 'Laravel Pordenone è la community per sviluppatori e appassionati del framework Laravel. Organizziamo eventi e meetup per condividere esperienze, best practice e novità dal mondo PHP e Laravel. Che tu sia alle prime armi o un developer esperto, troverai un ambiente accogliente dove confrontarti, imparare e creare connessioni con altri professionisti del settore.',
                    'phone'       => '+393472220547',
                    'status'      => CommunityStatus::Active->value,
                ];
                $communities[] = [
                    'user_id'     => $user->id,
                    'name'        => 'Wordpress Meetup Firenze',
                    'logo'        => null,
                    'website'     => 'www.wordpressfirenze.it',
                    'slug'        => 'wordpress-firenze',
                    'facebook'    => 'https://facebook/aabb',
                    'linkedin'    => 'https://linkedin/aabb',
                    'instagram'   => 'https://instagram/aabb',
                    'description' => 'WordPress Meetup Firenze è la community locale dedicata a chi utilizza, sviluppa o semplicemente è curioso del mondo WordPress. Organizziamo incontri regolari per condividere esperienze, buone pratiche e novità legate al CMS più usato al mondo. Che tu sia uno sviluppatore, un designer, un blogger o un imprenditore, troverai un ambiente amichevole, collaborativo e ricco di opportunità per crescere e fare networking.',
                    'phone'       => '+393472220547',
                    'status'      => CommunityStatus::Pending->value,
                ];
            }
        }

        Community::query()->insert($communities);
    }
}
