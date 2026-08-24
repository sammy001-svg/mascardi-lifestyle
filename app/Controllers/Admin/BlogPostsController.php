<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Uploader;
use App\Core\Validator;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\MediaUpload;

final class BlogPostsController
{
    public function index(): void
    {
        View::render('admin/blog-posts/index', [
            'pageTitle'    => 'Blog Posts',
            'pageSubtitle' => 'Articles published on the Mascardi blog',
            'activeModule' => 'blog-posts',
            'posts'        => BlogPost::all(),
        ]);
    }

    public function create(): void
    {
        View::render('admin/blog-posts/form', [
            'pageTitle'    => 'New Blog Post',
            'activeModule' => 'blog-posts',
            'post'         => null,
            'categories'   => BlogCategory::all(),
        ]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(Request::all([
            'title', 'slug', 'category_id', 'excerpt', 'body', 'status', 'published_at',
        ]));

        if ($errors) {
            redirect_with_errors(admin_url('blog-posts', 'create'), $errors, $_POST);
        }

        if (BlogPost::slugExists($data['slug'])) {
            redirect_with_errors(admin_url('blog-posts', 'create'), ['slug' => ['That slug is already in use.']], $_POST);
        }

        $data['author_id'] = Auth::user()['id'] ?? null;
        $data['cover_image_path'] = $this->handleCoverImage(null, Auth::user()['id'] ?? null);

        $id = BlogPost::create($data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'blog_post.create', 'blog_post', $id, $data['title']);
        Session::flash('success', 'Post created.');
        Response::redirect(admin_url('blog-posts', 'edit', ['id' => $id]));
    }

    public function edit(): void
    {
        $id   = Request::intInput('id');
        $post = BlogPost::find($id);
        if (!$post) {
            Response::notFound();
        }

        View::render('admin/blog-posts/form', [
            'pageTitle'    => 'Edit Blog Post',
            'activeModule' => 'blog-posts',
            'post'         => $post,
            'categories'   => BlogCategory::all(),
        ]);
    }

    public function update(): void
    {
        $id   = Request::intInput('id');
        $post = BlogPost::find($id);
        if (!$post) {
            Response::notFound();
        }

        [$data, $errors] = $this->validate(Request::all([
            'title', 'slug', 'category_id', 'excerpt', 'body', 'status', 'published_at',
        ]));

        if ($errors) {
            redirect_with_errors(admin_url('blog-posts', 'edit', ['id' => $id]), $errors, $_POST);
        }

        if (BlogPost::slugExists($data['slug'], $id)) {
            redirect_with_errors(admin_url('blog-posts', 'edit', ['id' => $id]), ['slug' => ['That slug is already in use.']], $_POST);
        }

        $newCover = $this->handleCoverImage($post['cover_image_path'] ?? null, Auth::user()['id'] ?? null);
        if ($newCover !== null) {
            $data['cover_image_path'] = $newCover;
        }

        BlogPost::update($id, $data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'blog_post.update', 'blog_post', $id, $data['title']);
        Session::flash('success', 'Post updated.');
        Response::redirect(admin_url('blog-posts', 'edit', ['id' => $id]));
    }

    public function delete(): void
    {
        $id   = Request::intInput('id');
        $post = BlogPost::find($id);
        if ($post) {
            if (!empty($post['cover_image_path']) && empty(MediaUpload::usages($post['cover_image_path']))) {
                Uploader::delete($post['cover_image_path']);
            }
            BlogPost::delete($id);
            ActivityLog::record(Auth::user()['id'] ?? null, 'blog_post.delete', 'blog_post', $id, $post['title']);
            Session::flash('success', 'Post deleted.');
        }
        Response::redirect(admin_url('blog-posts'));
    }

    // ------------------------------------------------------------------ //

    private function handleCoverImage(?string $existing, ?int $adminId): ?string
    {
        // Check picked-from-library first
        $pickedIds = (array) ($_POST['picked_media_ids'] ?? []);
        if (!empty($pickedIds)) {
            $media = MediaUpload::find((int) $pickedIds[0]);
            if ($media) {
                return $media['file_path'];
            }
        }

        // Direct file upload
        $files = Request::normalizeFiles('cover_image');
        if (!empty($files)) {
            try {
                return Uploader::storeImage($files[0], 'blog', $adminId);
            } catch (\RuntimeException) {
                // Fall through — keep existing image
            }
        }

        return null; // No new image; caller keeps existing
    }

    private function validate(array $input): array
    {
        if (empty($input['slug']) && !empty($input['title'])) {
            $input['slug'] = slugify($input['title']);
        } else {
            $input['slug'] = slugify((string) ($input['slug'] ?? ''));
        }

        $v = new Validator($input);
        $v->required('title', 'Title')->maxLength('title', 200, 'Title');
        $v->required('slug', 'Slug');
        $v->required('status', 'Status');

        if ($v->fails()) {
            return [$input, $v->errors()];
        }

        // Auto-set published_at when status is published and field is blank
        $publishedAt = $input['published_at'] ?: null;
        if ($input['status'] === 'published' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        return [[
            'title'       => $input['title'],
            'slug'        => $input['slug'],
            'category_id' => $input['category_id'] !== '' ? (int) $input['category_id'] : null,
            'excerpt'     => $input['excerpt'] ?: null,
            'body'        => $input['body'] ?: null,
            'status'      => $input['status'],
            'published_at'=> $publishedAt,
        ], []];
    }
}
