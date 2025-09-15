<?php

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
        $tags = [
            'PHP',
            'JavaScript',
            'Laravel',
            'Backend',
            'Cybersecurity',
            'Frontend',
            'DevOps',
            'Cloud Computing',
            'Machine Learning',
            'Data Science',
            'AI',
            'Blockchain',
            'React',
            'Vue.js',
            'Node.js',
            'Docker',
            'Kubernetes',
            'Go',
            'Python',
            'Ruby',
            'Java',
            'C++',
            'SQL',
            'NoSQL',
            'HTML/CSS',
            'GraphQL',
            'Firebase',
            'Swift',
            'Flutter',
            'Mobile Development',
            'Web Development',
            'Software Engineering',
            'Open Source',
            'Agile',
            'Scrum',
            'UX/UI Design',
            'Serverless',
        ];

        // Crea i tag nel database
        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag,
                'slug' => Str::slug($tag), // Genera lo slug a partire dal nome del tag
            ]);
        }
    }
}
