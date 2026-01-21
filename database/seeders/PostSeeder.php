<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Welcome to Laravel Translation',
                'content' => 'This is a demo post to showcase automatic translation feature in Laravel.'
            ],
            [
                'title' => 'Google Translate API Integration',
                'content' => 'Learn how to integrate Google Translate API with Laravel for automatic translations.'
            ],
            [
                'title' => 'Multi-language Support',
                'content' => 'Support multiple languages in your Laravel application easily with this setup.'
            ]
        ];

        foreach ($posts as $postData) {
            $post = Post::create($postData);
            $post->translateAndSave(); // Auto-translate to all languages
        }
    }
}