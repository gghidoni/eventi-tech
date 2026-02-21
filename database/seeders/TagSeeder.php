<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array di tag a tema eventi tech
        $techTopics = [
            ['label' => 'PHP',          'badge_color' => '#777BB4', 'label_color' => '#FFFFFF'],
            ['label' => 'JS',           'badge_color' => '#F7DF1E', 'label_color' => '#111827'],
            ['label' => 'Laravel',      'badge_color' => '#FF2D20', 'label_color' => '#FFFFFF'],
            ['label' => 'Backend',      'badge_color' => '#334155', 'label_color' => '#FFFFFF'],
            ['label' => 'Cybersec',     'badge_color' => '#0EA5E9', 'label_color' => '#FFFFFF'],
            ['label' => 'Frontend',     'badge_color' => '#2563EB', 'label_color' => '#FFFFFF'],
            ['label' => 'DevOps',       'badge_color' => '#0F766E', 'label_color' => '#FFFFFF'],
            ['label' => 'Cloud',        'badge_color' => '#0284C7', 'label_color' => '#FFFFFF'],
            ['label' => 'ML',           'badge_color' => '#FF6F00', 'label_color' => '#FFFFFF'],
            ['label' => 'DataSci',      'badge_color' => '#4B8BBE', 'label_color' => '#FFFFFF'],
            ['label' => 'AI',           'badge_color' => '#10A37F', 'label_color' => '#FFFFFF'],
            ['label' => 'Blockchain',   'badge_color' => '#F7931A', 'label_color' => '#111827'],
            ['label' => 'React',        'badge_color' => '#61DAFB', 'label_color' => '#111827'],
            ['label' => 'Vue',          'badge_color' => '#42B883', 'label_color' => '#FFFFFF'],
            ['label' => 'Node',         'badge_color' => '#339933', 'label_color' => '#FFFFFF'],
            ['label' => 'Docker',       'badge_color' => '#2496ED', 'label_color' => '#FFFFFF'],
            ['label' => 'K8s',          'badge_color' => '#326CE5', 'label_color' => '#FFFFFF'],
            ['label' => 'Go',           'badge_color' => '#00ADD8', 'label_color' => '#111827'],
            ['label' => 'Python',       'badge_color' => '#3776AB', 'label_color' => '#FFFFFF'],
            ['label' => 'Ruby',         'badge_color' => '#CC342D', 'label_color' => '#FFFFFF'],
            ['label' => 'Java',         'badge_color' => '#ED8B00', 'label_color' => '#111827'],
            ['label' => 'C++',          'badge_color' => '#00599C', 'label_color' => '#FFFFFF'],
            ['label' => 'SQL',          'badge_color' => '#336791', 'label_color' => '#FFFFFF'],
            ['label' => 'NoSQL',        'badge_color' => '#47A248', 'label_color' => '#FFFFFF'],
            ['label' => 'HTML/CSS',     'badge_color' => '#E34F26', 'label_color' => '#FFFFFF'],
            ['label' => 'GraphQL',      'badge_color' => '#E10098', 'label_color' => '#FFFFFF'],
            ['label' => 'Firebase',     'badge_color' => '#FFCA28', 'label_color' => '#111827'],
            ['label' => 'Swift',        'badge_color' => '#FA7343', 'label_color' => '#FFFFFF'],
            ['label' => 'Flutter',      'badge_color' => '#02569B', 'label_color' => '#FFFFFF'],
            ['label' => 'Mobile',       'badge_color' => '#0EA5E9', 'label_color' => '#FFFFFF'],
            ['label' => 'Web Dev',      'badge_color' => '#14B8A6', 'label_color' => '#FFFFFF'],
            ['label' => 'Software Eng', 'badge_color' => '#1F2937', 'label_color' => '#FFFFFF'],
            ['label' => 'Open Source',  'badge_color' => '#3DA639', 'label_color' => '#FFFFFF'],
            ['label' => 'Agile',        'badge_color' => '#22C55E', 'label_color' => '#111827'],
            ['label' => 'Scrum',        'badge_color' => '#009FDA', 'label_color' => '#FFFFFF'],
            ['label' => 'UX/UI',        'badge_color' => '#DB2777', 'label_color' => '#FFFFFF'],
            ['label' => 'Serverless',   'badge_color' => '#FF9900', 'label_color' => '#111827'],
        
            ['label' => 'TypeScript',   'badge_color' => '#3178C6', 'label_color' => '#FFFFFF'],
            ['label' => 'Rust',         'badge_color' => '#000000', 'label_color' => '#FFFFFF'],
            ['label' => 'CI/CD',        'badge_color' => '#0EA5E9', 'label_color' => '#111827'],
            ['label' => 'API',          'badge_color' => '#6366F1', 'label_color' => '#FFFFFF'],
            ['label' => 'Linux',        'badge_color' => '#FCC624', 'label_color' => '#111827'],
            ['label' => 'C#',           'badge_color' => '#512BD4', 'label_color' => '#FFFFFF'],
            ['label' => '.NET',         'badge_color' => '#512BD4', 'label_color' => '#FFFFFF'],
            ['label' => 'Django',       'badge_color' => '#092E20', 'label_color' => '#FFFFFF'],
            ['label' => 'Flask',        'badge_color' => '#000000', 'label_color' => '#FFFFFF'],
            ['label' => 'FastAPI',      'badge_color' => '#009688', 'label_color' => '#FFFFFF'],
            ['label' => 'Spring',       'badge_color' => '#6DB33F', 'label_color' => '#111827'],
            ['label' => 'Next.js',      'badge_color' => '#000000', 'label_color' => '#FFFFFF'],
            ['label' => 'Nuxt',         'badge_color' => '#00DC82', 'label_color' => '#111827'],
            ['label' => 'Svelte',       'badge_color' => '#FF3E00', 'label_color' => '#FFFFFF'],
            ['label' => 'Angular',      'badge_color' => '#DD0031', 'label_color' => '#FFFFFF'],
            ['label' => 'Tailwind',     'badge_color' => '#06B6D4', 'label_color' => '#111827'],
            ['label' => 'Bootstrap',    'badge_color' => '#7952B3', 'label_color' => '#FFFFFF'],
            ['label' => 'Sass',         'badge_color' => '#CC6699', 'label_color' => '#FFFFFF'],
            ['label' => 'Redux',        'badge_color' => '#764ABC', 'label_color' => '#FFFFFF'],
            ['label' => 'Jest',         'badge_color' => '#C21325', 'label_color' => '#FFFFFF'],
            ['label' => 'Webpack',      'badge_color' => '#8DD6F9', 'label_color' => '#111827'],
            ['label' => 'Vite',         'badge_color' => '#646CFF', 'label_color' => '#FFFFFF'],
            ['label' => 'Nginx',        'badge_color' => '#009639', 'label_color' => '#FFFFFF'],
            ['label' => 'Apache',       'badge_color' => '#D22128', 'label_color' => '#FFFFFF'],
            ['label' => 'Terraform',    'badge_color' => '#7B42BC', 'label_color' => '#FFFFFF'],
            ['label' => 'Ansible',      'badge_color' => '#EE0000', 'label_color' => '#FFFFFF'],
            ['label' => 'Jenkins',      'badge_color' => '#D24939', 'label_color' => '#FFFFFF'],
            ['label' => 'Git',          'badge_color' => '#F05032', 'label_color' => '#FFFFFF'],
            ['label' => 'GitHub',       'badge_color' => '#181717', 'label_color' => '#FFFFFF'],
            ['label' => 'GitLab',       'badge_color' => '#FC6D26', 'label_color' => '#111827'],
            ['label' => 'PostgreSQL',   'badge_color' => '#4169E1', 'label_color' => '#FFFFFF'],
            ['label' => 'MySQL',        'badge_color' => '#4479A1', 'label_color' => '#FFFFFF'],
            ['label' => 'Redis',        'badge_color' => '#DC382D', 'label_color' => '#FFFFFF'],
            ['label' => 'MariaDB',      'badge_color' => '#003545', 'label_color' => '#FFFFFF'],
            ['label' => 'SQLite',       'badge_color' => '#003B57', 'label_color' => '#FFFFFF'],
            ['label' => 'Elastic',      'badge_color' => '#005571', 'label_color' => '#FFFFFF'],
            ['label' => 'RabbitMQ',     'badge_color' => '#FF6600', 'label_color' => '#111827'],
            ['label' => 'Kafka',        'badge_color' => '#231F20', 'label_color' => '#FFFFFF'],
            ['label' => 'Prometheus',   'badge_color' => '#E6522C', 'label_color' => '#FFFFFF'],
            ['label' => 'Grafana',      'badge_color' => '#F46800', 'label_color' => '#111827'],
            ['label' => 'Kotlin',       'badge_color' => '#7F52FF', 'label_color' => '#FFFFFF'],
            ['label' => 'Scala',        'badge_color' => '#DC322F', 'label_color' => '#FFFFFF'],
            ['label' => 'Elixir',       'badge_color' => '#4B275F', 'label_color' => '#FFFFFF'],
            ['label' => 'Solidity',     'badge_color' => '#363636', 'label_color' => '#FFFFFF'],
            ['label' => 'TensorFlow',   'badge_color' => '#FF6F00', 'label_color' => '#111827'],
            ['label' => 'PyTorch',      'badge_color' => '#EE4C2C', 'label_color' => '#FFFFFF'],
            ['label' => 'NumPy',        'badge_color' => '#013243', 'label_color' => '#FFFFFF'],
            ['label' => 'Pandas',       'badge_color' => '#150458', 'label_color' => '#FFFFFF'],
            ['label' => 'AWS',          'badge_color' => '#FF9900', 'label_color' => '#111827'],
            ['label' => 'Azure',        'badge_color' => '#0078D4', 'label_color' => '#FFFFFF'],
            ['label' => 'GCP',          'badge_color' => '#4285F4', 'label_color' => '#FFFFFF'],
            ['label' => 'Supabase',     'badge_color' => '#3ECF8E', 'label_color' => '#111827'],
            ['label' => 'Prisma',       'badge_color' => '#2D3748', 'label_color' => '#FFFFFF'],
            ['label' => 'Strapi',       'badge_color' => '#4945FF', 'label_color' => '#FFFFFF'],
            ['label' => 'WordPress',    'badge_color' => '#21759B', 'label_color' => '#FFFFFF'],
            ['label' => 'Shopify',      'badge_color' => '#95BF47', 'label_color' => '#111827'],
            ['label' => 'Neo4j',        'badge_color' => '#008CC1', 'label_color' => '#FFFFFF'],
        ];

        // Crea i tag nel database
        foreach ($techTopics as $tag) {
            Tag::query()->create([
                'name' => $tag['label'],
                'slug' => Str::slug($tag['label']), // Genera lo slug a partire dal nome del tag
                'badge_color' => $tag['badge_color'],
                'label_color' => $tag['label_color'] 
            ]);
        }
    }
}
