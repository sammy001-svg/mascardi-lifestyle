<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Response;
use App\Core\View;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Setting;

final class BlogController
{
    private const PER_PAGE = 9;

    public function index(): void
    {
        $categorySlug = $_GET['category'] ?? null;
        $page         = max(1, (int) ($_GET['page'] ?? 1));
        $offset       = ($page - 1) * self::PER_PAGE;

        $category   = null;
        $categoryId = null;

        if ($categorySlug) {
            $category   = BlogCategory::findBySlug($categorySlug);
            $categoryId = $category ? (int) $category['id'] : -1;
        }

        $posts = BlogPost::published(self::PER_PAGE, $offset, $categoryId);
        $total = BlogPost::countPublished($categoryId);
        $pages = (int) ceil($total / self::PER_PAGE);

        View::render('site/blog/index', [
            'pageTitle'      => 'Blog — Mascardi Lifestyle',
            'settings'       => Setting::all(),
            'posts'          => $posts,
            'categories'     => BlogCategory::all(),
            'activeCategory' => $category,
            'currentPage'    => $page,
            'totalPages'     => $pages,
            'totalPosts'     => $total,
        ], 'site');
    }

    public function show(string $slug): void
    {
        $post = BlogPost::findBySlug($slug);

        if (!$post) {
            Response::notFound();
        }

        View::render('site/blog/show', [
            'pageTitle' => e($post['title']) . ' — Mascardi Lifestyle',
            'settings'  => Setting::all(),
            'post'      => $post,
            'recent'    => BlogPost::recent(3),
        ], 'site');
    }
}
