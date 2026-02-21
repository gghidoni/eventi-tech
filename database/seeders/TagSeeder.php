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
        // Array di tag a tema eventi tech con icona Simple Icons.
        $techTopics = [
            ['label' => 'PHP',          'icon' => 'php',               'badge_color' => '#777BB4', 'label_color' => '#FFFFFF'],
            ['label' => 'JS',           'icon' => 'javascript',        'badge_color' => '#F7DF1E', 'label_color' => '#111827'],
            ['label' => 'Laravel',      'icon' => 'laravel',           'badge_color' => '#FF2D20', 'label_color' => '#FFFFFF'],
            ['label' => 'Backend',      'icon' => 'server',            'badge_color' => '#334155', 'label_color' => '#FFFFFF'],
            ['label' => 'Cybersec',     'icon' => 'securityscorecard', 'badge_color' => '#0EA5E9', 'label_color' => '#FFFFFF'],
            ['label' => 'Frontend',     'icon' => 'html5',             'badge_color' => '#2563EB', 'label_color' => '#FFFFFF'],
            ['label' => 'DevOps',       'icon' => 'devdotto',          'badge_color' => '#0F766E', 'label_color' => '#FFFFFF'],
            ['label' => 'Cloud',        'icon' => 'icloud',            'badge_color' => '#0284C7', 'label_color' => '#FFFFFF'],
            ['label' => 'ML',           'icon' => 'tensorflow',        'badge_color' => '#FF6F00', 'label_color' => '#FFFFFF'],
            ['label' => 'DataSci',      'icon' => 'jupyter',           'badge_color' => '#4B8BBE', 'label_color' => '#FFFFFF'],
            ['label' => 'AI',           'icon' => 'openai',            'badge_color' => '#10A37F', 'label_color' => '#FFFFFF'],
            ['label' => 'Blockchain',   'icon' => 'blockchaindotcom',  'badge_color' => '#F7931A', 'label_color' => '#111827'],
            ['label' => 'React',        'icon' => 'react',             'badge_color' => '#61DAFB', 'label_color' => '#111827'],
            ['label' => 'Vue',          'icon' => 'vuedotjs',          'badge_color' => '#42B883', 'label_color' => '#FFFFFF'],
            ['label' => 'Node',         'icon' => 'nodedotjs',         'badge_color' => '#339933', 'label_color' => '#FFFFFF'],
            ['label' => 'Docker',       'icon' => 'docker',            'badge_color' => '#2496ED', 'label_color' => '#FFFFFF'],
            ['label' => 'K8s',          'icon' => 'kubernetes',        'badge_color' => '#326CE5', 'label_color' => '#FFFFFF'],
            ['label' => 'Go',           'icon' => 'go',                'badge_color' => '#00ADD8', 'label_color' => '#111827'],
            ['label' => 'Python',       'icon' => 'python',            'badge_color' => '#3776AB', 'label_color' => '#FFFFFF'],
            ['label' => 'Ruby',         'icon' => 'ruby',              'badge_color' => '#CC342D', 'label_color' => '#FFFFFF'],
            ['label' => 'Java',         'icon' => 'openjdk',           'badge_color' => '#ED8B00', 'label_color' => '#111827'],
            ['label' => 'C++',          'icon' => 'cplusplus',         'badge_color' => '#00599C', 'label_color' => '#FFFFFF'],
            ['label' => 'SQL',          'icon' => 'postgresql',        'badge_color' => '#336791', 'label_color' => '#FFFFFF'],
            ['label' => 'NoSQL',        'icon' => 'mongodb',           'badge_color' => '#47A248', 'label_color' => '#FFFFFF'],
            ['label' => 'HTML/CSS',     'icon' => 'html5',             'badge_color' => '#E34F26', 'label_color' => '#FFFFFF'],
            ['label' => 'GraphQL',      'icon' => 'graphql',           'badge_color' => '#E10098', 'label_color' => '#FFFFFF'],
            ['label' => 'Firebase',     'icon' => 'firebase',          'badge_color' => '#FFCA28', 'label_color' => '#111827'],
            ['label' => 'Swift',        'icon' => 'swift',             'badge_color' => '#FA7343', 'label_color' => '#FFFFFF'],
            ['label' => 'Flutter',      'icon' => 'flutter',           'badge_color' => '#02569B', 'label_color' => '#FFFFFF'],
            ['label' => 'Mobile',       'icon' => 'android',           'badge_color' => '#0EA5E9', 'label_color' => '#FFFFFF'],
            ['label' => 'Web Dev',      'icon' => 'googlechrome',      'badge_color' => '#14B8A6', 'label_color' => '#FFFFFF'],
            ['label' => 'Software Eng', 'icon' => 'code',              'badge_color' => '#1F2937', 'label_color' => '#FFFFFF'],
            ['label' => 'Open Source',  'icon' => 'opensourceinitiative', 'badge_color' => '#3DA639', 'label_color' => '#FFFFFF'],
            ['label' => 'Agile',        'icon' => 'target',            'badge_color' => '#22C55E', 'label_color' => '#111827'],
            ['label' => 'Scrum',        'icon' => 'jira',              'badge_color' => '#009FDA', 'label_color' => '#FFFFFF'],
            ['label' => 'UX/UI',        'icon' => 'figma',             'badge_color' => '#DB2777', 'label_color' => '#FFFFFF'],
            ['label' => 'Serverless',   'icon' => 'serverfault',       'badge_color' => '#FF9900', 'label_color' => '#111827'],

            ['label' => 'TypeScript',   'icon' => 'typescript',        'badge_color' => '#3178C6', 'label_color' => '#FFFFFF'],
            ['label' => 'Rust',         'icon' => 'rust',              'badge_color' => '#000000', 'label_color' => '#FFFFFF'],
            ['label' => 'CI/CD',        'icon' => 'githubactions',     'badge_color' => '#0EA5E9', 'label_color' => '#111827'],
            ['label' => 'API',          'icon' => 'fastapi',           'badge_color' => '#6366F1', 'label_color' => '#FFFFFF'],
            ['label' => 'Linux',        'icon' => 'linux',             'badge_color' => '#FCC624', 'label_color' => '#111827'],
            ['label' => 'C#',           'icon' => 'csharp',            'badge_color' => '#512BD4', 'label_color' => '#FFFFFF'],
            ['label' => '.NET',         'icon' => 'dotnet',            'badge_color' => '#512BD4', 'label_color' => '#FFFFFF'],
            ['label' => 'Django',       'icon' => 'django',            'badge_color' => '#092E20', 'label_color' => '#FFFFFF'],
            ['label' => 'Flask',        'icon' => 'flask',             'badge_color' => '#000000', 'label_color' => '#FFFFFF'],
            ['label' => 'FastAPI',      'icon' => 'fastapi',           'badge_color' => '#009688', 'label_color' => '#FFFFFF'],
            ['label' => 'Spring',       'icon' => 'spring',            'badge_color' => '#6DB33F', 'label_color' => '#111827'],
            ['label' => 'Next.js',      'icon' => 'nextdotjs',         'badge_color' => '#000000', 'label_color' => '#FFFFFF'],
            ['label' => 'Nuxt',         'icon' => 'nuxt',              'badge_color' => '#00DC82', 'label_color' => '#111827'],
            ['label' => 'Svelte',       'icon' => 'svelte',            'badge_color' => '#FF3E00', 'label_color' => '#FFFFFF'],
            ['label' => 'Angular',      'icon' => 'angular',           'badge_color' => '#DD0031', 'label_color' => '#FFFFFF'],
            ['label' => 'Tailwind',     'icon' => 'tailwindcss',       'badge_color' => '#06B6D4', 'label_color' => '#111827'],
            ['label' => 'Bootstrap',    'icon' => 'bootstrap',         'badge_color' => '#7952B3', 'label_color' => '#FFFFFF'],
            ['label' => 'Sass',         'icon' => 'sass',              'badge_color' => '#CC6699', 'label_color' => '#FFFFFF'],
            ['label' => 'Redux',        'icon' => 'redux',             'badge_color' => '#764ABC', 'label_color' => '#FFFFFF'],
            ['label' => 'Jest',         'icon' => 'jest',              'badge_color' => '#C21325', 'label_color' => '#FFFFFF'],
            ['label' => 'Webpack',      'icon' => 'webpack',           'badge_color' => '#8DD6F9', 'label_color' => '#111827'],
            ['label' => 'Vite',         'icon' => 'vite',              'badge_color' => '#646CFF', 'label_color' => '#FFFFFF'],
            ['label' => 'Nginx',        'icon' => 'nginx',             'badge_color' => '#009639', 'label_color' => '#FFFFFF'],
            ['label' => 'Apache',       'icon' => 'apache',            'badge_color' => '#D22128', 'label_color' => '#FFFFFF'],
            ['label' => 'Terraform',    'icon' => 'terraform',         'badge_color' => '#7B42BC', 'label_color' => '#FFFFFF'],
            ['label' => 'Ansible',      'icon' => 'ansible',           'badge_color' => '#EE0000', 'label_color' => '#FFFFFF'],
            ['label' => 'Jenkins',      'icon' => 'jenkins',           'badge_color' => '#D24939', 'label_color' => '#FFFFFF'],
            ['label' => 'Git',          'icon' => 'git',               'badge_color' => '#F05032', 'label_color' => '#FFFFFF'],
            ['label' => 'GitHub',       'icon' => 'github',            'badge_color' => '#181717', 'label_color' => '#FFFFFF'],
            ['label' => 'GitLab',       'icon' => 'gitlab',            'badge_color' => '#FC6D26', 'label_color' => '#111827'],
            ['label' => 'PostgreSQL',   'icon' => 'postgresql',        'badge_color' => '#4169E1', 'label_color' => '#FFFFFF'],
            ['label' => 'MySQL',        'icon' => 'mysql',             'badge_color' => '#4479A1', 'label_color' => '#FFFFFF'],
            ['label' => 'Redis',        'icon' => 'redis',             'badge_color' => '#DC382D', 'label_color' => '#FFFFFF'],
            ['label' => 'MariaDB',      'icon' => 'mariadb',           'badge_color' => '#003545', 'label_color' => '#FFFFFF'],
            ['label' => 'SQLite',       'icon' => 'sqlite',            'badge_color' => '#003B57', 'label_color' => '#FFFFFF'],
            ['label' => 'Elastic',      'icon' => 'elasticsearch',     'badge_color' => '#005571', 'label_color' => '#FFFFFF'],
            ['label' => 'RabbitMQ',     'icon' => 'rabbitmq',          'badge_color' => '#FF6600', 'label_color' => '#111827'],
            ['label' => 'Kafka',        'icon' => 'apachekafka',       'badge_color' => '#231F20', 'label_color' => '#FFFFFF'],
            ['label' => 'Prometheus',   'icon' => 'prometheus',        'badge_color' => '#E6522C', 'label_color' => '#FFFFFF'],
            ['label' => 'Grafana',      'icon' => 'grafana',           'badge_color' => '#F46800', 'label_color' => '#111827'],
            ['label' => 'Kotlin',       'icon' => 'kotlin',            'badge_color' => '#7F52FF', 'label_color' => '#FFFFFF'],
            ['label' => 'Scala',        'icon' => 'scala',             'badge_color' => '#DC322F', 'label_color' => '#FFFFFF'],
            ['label' => 'Elixir',       'icon' => 'elixir',            'badge_color' => '#4B275F', 'label_color' => '#FFFFFF'],
            ['label' => 'Solidity',     'icon' => 'solidity',          'badge_color' => '#363636', 'label_color' => '#FFFFFF'],
            ['label' => 'TensorFlow',   'icon' => 'tensorflow',        'badge_color' => '#FF6F00', 'label_color' => '#111827'],
            ['label' => 'PyTorch',      'icon' => 'pytorch',           'badge_color' => '#EE4C2C', 'label_color' => '#FFFFFF'],
            ['label' => 'NumPy',        'icon' => 'numpy',             'badge_color' => '#013243', 'label_color' => '#FFFFFF'],
            ['label' => 'Pandas',       'icon' => 'pandas',            'badge_color' => '#150458', 'label_color' => '#FFFFFF'],
            ['label' => 'AWS',          'icon' => 'amazonwebservices', 'badge_color' => '#FF9900', 'label_color' => '#111827'],
            ['label' => 'Azure',        'icon' => 'microsoftazure',    'badge_color' => '#0078D4', 'label_color' => '#FFFFFF'],
            ['label' => 'GCP',          'icon' => 'googlecloud',       'badge_color' => '#4285F4', 'label_color' => '#FFFFFF'],
            ['label' => 'Supabase',     'icon' => 'supabase',          'badge_color' => '#3ECF8E', 'label_color' => '#111827'],
            ['label' => 'Prisma',       'icon' => 'prisma',            'badge_color' => '#2D3748', 'label_color' => '#FFFFFF'],
            ['label' => 'Strapi',       'icon' => 'strapi',            'badge_color' => '#4945FF', 'label_color' => '#FFFFFF'],
            ['label' => 'WordPress',    'icon' => 'wordpress',         'badge_color' => '#21759B', 'label_color' => '#FFFFFF'],
            ['label' => 'Shopify',      'icon' => 'shopify',           'badge_color' => '#95BF47', 'label_color' => '#111827'],
            ['label' => 'Neo4j',        'icon' => 'neo4j',             'badge_color' => '#008CC1', 'label_color' => '#FFFFFF'],
        ];

        // Crea i tag nel database.
        foreach ($techTopics as $tag) {
            Tag::query()->create([
                'name' => $tag['label'],
                'slug' => Str::slug($tag['label']), // Genera lo slug a partire dal nome del tag.
                'icon' => $tag['icon'],
                'badge_color' => $tag['badge_color'],
                'label_color' => $tag['label_color'],
            ]);
        }
    }
}
