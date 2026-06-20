<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    private function posts(): array
    {
        return require base_path('resources/data/blog-posts.php');
    }

    public function index(): View
    {
        $posts = array_map(fn ($p) => array_diff_key($p, ['body' => '']), $this->posts());

        return view('blog.index', compact('posts'));
    }

    public function show(string $slug): View
    {
        $post = collect($this->posts())->firstWhere('slug', $slug);

        if (! $post) {
            throw new NotFoundHttpException;
        }

        return view('blog.show', compact('post'));
    }
}
