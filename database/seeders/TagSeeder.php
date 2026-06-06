<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Elenco tag minimale: nome + slug icona Simple Icons.
        $techTopics = [
            ['label' => 'PHP', 'icon' => 'php'],
            ['label' => 'JS', 'icon' => 'javascript'],
            ['label' => 'Laravel', 'icon' => 'laravel'],
            ['label' => 'Backend', 'icon' => 'backendless'],
            ['label' => 'Cybersec', 'icon' => 'securityscorecard'],
            ['label' => 'Frontend', 'icon' => 'html5'],
            ['label' => 'DevOps', 'icon' => 'devdotto'],
            ['label' => 'Cloud', 'icon' => 'icloud'],
            ['label' => 'ML', 'icon' => 'tensorflow'],
            ['label' => 'DataSci', 'icon' => 'jupyter'],
            ['label' => 'AI', 'icon' => 'huggingface'],
            ['label' => 'Blockchain', 'icon' => 'blockchaindotcom'],
            ['label' => 'React', 'icon' => 'react'],
            ['label' => 'Vue', 'icon' => 'vuedotjs'],
            ['label' => 'Node', 'icon' => 'nodedotjs'],
            ['label' => 'Docker', 'icon' => 'docker'],
            ['label' => 'K8s', 'icon' => 'kubernetes'],
            ['label' => 'Go', 'icon' => 'go'],
            ['label' => 'Python', 'icon' => 'python'],
            ['label' => 'Ruby', 'icon' => 'ruby'],
            ['label' => 'Java', 'icon' => 'openjdk'],
            ['label' => 'C++', 'icon' => 'cplusplus'],
            ['label' => 'SQL', 'icon' => 'postgresql'],
            ['label' => 'NoSQL', 'icon' => 'mongodb'],
            ['label' => 'HTML/CSS', 'icon' => 'html5'],
            ['label' => 'GraphQL', 'icon' => 'graphql'],
            ['label' => 'Firebase', 'icon' => 'firebase'],
            ['label' => 'Swift', 'icon' => 'swift'],
            ['label' => 'Flutter', 'icon' => 'flutter'],
            ['label' => 'Mobile', 'icon' => 'android'],
            ['label' => 'Web Dev', 'icon' => 'googlechrome'],
            ['label' => 'Software Eng', 'icon' => 'codefactor'],
            ['label' => 'Open Source', 'icon' => 'opensourceinitiative'],
            ['label' => 'Agile', 'icon' => 'target'],
            ['label' => 'Scrum', 'icon' => 'jira'],
            ['label' => 'UX/UI', 'icon' => 'figma'],
            ['label' => 'Serverless', 'icon' => 'serverfault'],
            ['label' => 'TypeScript', 'icon' => 'typescript'],
            ['label' => 'Rust', 'icon' => 'rust'],
            ['label' => 'CI/CD', 'icon' => 'githubactions'],
            ['label' => 'API', 'icon' => 'fastapi'],
            ['label' => 'Linux', 'icon' => 'linux'],
            ['label' => 'C#', 'icon' => 'sharp'],
            ['label' => '.NET', 'icon' => 'dotnet'],
            ['label' => 'Django', 'icon' => 'django'],
            ['label' => 'Flask', 'icon' => 'flask'],
            ['label' => 'FastAPI', 'icon' => 'fastapi'],
            ['label' => 'Spring', 'icon' => 'spring'],
            ['label' => 'Next.js', 'icon' => 'nextdotjs'],
            ['label' => 'Nuxt', 'icon' => 'nuxt'],
            ['label' => 'Svelte', 'icon' => 'svelte'],
            ['label' => 'Angular', 'icon' => 'angular'],
            ['label' => 'Tailwind', 'icon' => 'tailwindcss'],
            ['label' => 'Bootstrap', 'icon' => 'bootstrap'],
            ['label' => 'Sass', 'icon' => 'sass'],
            ['label' => 'Redux', 'icon' => 'redux'],
            ['label' => 'Jest', 'icon' => 'jest'],
            ['label' => 'Webpack', 'icon' => 'webpack'],
            ['label' => 'Vite', 'icon' => 'vite'],
            ['label' => 'Nginx', 'icon' => 'nginx'],
            ['label' => 'Apache', 'icon' => 'apache'],
            ['label' => 'Terraform', 'icon' => 'terraform'],
            ['label' => 'Ansible', 'icon' => 'ansible'],
            ['label' => 'Jenkins', 'icon' => 'jenkins'],
            ['label' => 'Git', 'icon' => 'git'],
            ['label' => 'GitHub', 'icon' => 'github'],
            ['label' => 'GitLab', 'icon' => 'gitlab'],
            ['label' => 'PostgreSQL', 'icon' => 'postgresql'],
            ['label' => 'MySQL', 'icon' => 'mysql'],
            ['label' => 'Redis', 'icon' => 'redis'],
            ['label' => 'MariaDB', 'icon' => 'mariadb'],
            ['label' => 'SQLite', 'icon' => 'sqlite'],
            ['label' => 'Elastic', 'icon' => 'elasticsearch'],
            ['label' => 'RabbitMQ', 'icon' => 'rabbitmq'],
            ['label' => 'Kafka', 'icon' => 'apachekafka'],
            ['label' => 'Prometheus', 'icon' => 'prometheus'],
            ['label' => 'Grafana', 'icon' => 'grafana'],
            ['label' => 'Kotlin', 'icon' => 'kotlin'],
            ['label' => 'Scala', 'icon' => 'scala'],
            ['label' => 'Elixir', 'icon' => 'elixir'],
            ['label' => 'Solidity', 'icon' => 'solidity'],
            ['label' => 'TensorFlow', 'icon' => 'tensorflow'],
            ['label' => 'PyTorch', 'icon' => 'pytorch'],
            ['label' => 'NumPy', 'icon' => 'numpy'],
            ['label' => 'Pandas', 'icon' => 'pandas'],
            ['label' => 'AWS', 'icon' => 'cloudflare'],
            ['label' => 'Azure', 'icon' => 'openstack'],
            ['label' => 'GCP', 'icon' => 'googlecloud'],
            ['label' => 'Supabase', 'icon' => 'supabase'],
            ['label' => 'Prisma', 'icon' => 'prisma'],
            ['label' => 'Strapi', 'icon' => 'strapi'],
            ['label' => 'WordPress', 'icon' => 'wordpress'],
            ['label' => 'Shopify', 'icon' => 'shopify'],
            ['label' => 'Neo4j', 'icon' => 'neo4j'],
        ];

        // Colori ufficiali Simple Icons estratti dagli SVG del CDN.
        $iconColors = [
            'android'              => '#3DDC84',
            'angular'              => '#0F0F11',
            'ansible'              => '#EE0000',
            'apache'               => '#D22128',
            'apachekafka'          => '#231F20',
            'backendless'          => '#1D77BD',
            'blockchaindotcom'     => '#121D33',
            'bootstrap'            => '#7952B3',
            'cloudflare'           => '#F38020',
            'codefactor'           => '#F44A6A',
            'cplusplus'            => '#00599C',
            'devdotto'             => '#0A0A0A',
            'django'               => '#092E20',
            'docker'               => '#2496ED',
            'dotnet'               => '#512BD4',
            'elasticsearch'        => '#005571',
            'elixir'               => '#4B275F',
            'fastapi'              => '#009688',
            'figma'                => '#F24E1E',
            'firebase'             => '#DD2C00',
            'flask'                => '#3BABC3',
            'flutter'              => '#02569B',
            'git'                  => '#F05032',
            'github'               => '#181717',
            'githubactions'        => '#2088FF',
            'gitlab'               => '#FC6D26',
            'go'                   => '#00ADD8',
            'googlechrome'         => '#4285F4',
            'googlecloud'          => '#4285F4',
            'grafana'              => '#F46800',
            'graphql'              => '#E10098',
            'html5'                => '#E34F26',
            'huggingface'          => '#FFD21E',
            'icloud'               => '#3693F3',
            'javascript'           => '#F7DF1E',
            'jenkins'              => '#D24939',
            'jest'                 => '#C21325',
            'jira'                 => '#0052CC',
            'jupyter'              => '#F37626',
            'kotlin'               => '#7F52FF',
            'kubernetes'           => '#326CE5',
            'laravel'              => '#FF2D20',
            'linux'                => '#FCC624',
            'mariadb'              => '#003545',
            'mongodb'              => '#47A248',
            'mysql'                => '#4479A1',
            'neo4j'                => '#4581C3',
            'nextdotjs'            => '#000000',
            'nginx'                => '#009639',
            'nodedotjs'            => '#5FA04E',
            'numpy'                => '#013243',
            'nuxt'                 => '#00DC82',
            'openjdk'              => '#000000',
            'opensourceinitiative' => '#3DA639',
            'openstack'            => '#ED1944',
            'pandas'               => '#150458',
            'php'                  => '#777BB4',
            'postgresql'           => '#4169E1',
            'prisma'               => '#2D3748',
            'prometheus'           => '#E6522C',
            'python'               => '#3776AB',
            'pytorch'              => '#EE4C2C',
            'rabbitmq'             => '#FF6600',
            'react'                => '#61DAFB',
            'redis'                => '#FF4438',
            'redux'                => '#764ABC',
            'ruby'                 => '#CC342D',
            'rust'                 => '#000000',
            'sass'                 => '#CC6699',
            'scala'                => '#DC322F',
            'securityscorecard'    => '#7033FD',
            'serverfault'          => '#E7282D',
            'sharp'                => '#99CC00',
            'shopify'              => '#7AB55C',
            'solidity'             => '#363636',
            'spring'               => '#6DB33F',
            'sqlite'               => '#003B57',
            'strapi'               => '#4945FF',
            'supabase'             => '#3FCF8E',
            'svelte'               => '#FF3E00',
            'swift'                => '#F05138',
            'tailwindcss'          => '#06B6D4',
            'target'               => '#CC0000',
            'tensorflow'           => '#FF6F00',
            'terraform'            => '#844FBA',
            'typescript'           => '#3178C6',
            'vite'                 => '#9135FF',
            'vuedotjs'             => '#4FC08D',
            'webpack'              => '#8DD6F9',
            'wordpress'            => '#21759B',
        ];

        foreach ($techTopics as $tag) {
            $icon = $tag['icon'];

            // Se manca un colore ufficiale, blocchiamo il seeding per evitare dati incoerenti.
            if (!array_key_exists($icon, $iconColors)) {
                throw new RuntimeException("Colore Simple Icons non trovato per slug: {$icon}");
            }

            $badgeColor = $iconColors[$icon];
            $labelColor = $this->pickLabelColorForBadge($badgeColor);

            Tag::query()->create([
                'name'        => $tag['label'],
                'slug'        => Str::slug($tag['label']), // Genera lo slug dal nome del tag.
                'icon'        => $icon,
                'badge_color' => $badgeColor,
                'label_color' => $labelColor,
            ]);
        }
    }

    /**
     * Restituisce un colore testo leggibile su un badge con questo sfondo.
     */
    private function pickLabelColorForBadge(string $badgeColor): string
    {
        $hex = mb_ltrim($badgeColor, '#');

        if (mb_strlen($hex) !== 6) {
            return '#FFFFFF';
        }

        $red = hexdec(mb_substr($hex, 0, 2));
        $green = hexdec(mb_substr($hex, 2, 2));
        $blue = hexdec(mb_substr($hex, 4, 2));

        // Luminanza percepita (YIQ): soglia semplice per scegliere testo chiaro/scuro.
        $yiq = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $yiq >= 160 ? '#111827' : '#FFFFFF';
    }
}
