<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\BlogCategory;

final class BlogCategoriesController
{
    public function index(): void
    {
        View::render('admin/blog-categories/index', [
            'pageTitle'    => 'Blog Categories',
            'pageSubtitle' => 'Organise your blog posts into topics',
            'activeModule' => 'blog-categories',
            'categories'   => BlogCategory::all(),
        ]);
    }

    public function create(): void
    {
        View::render('admin/blog-categories/form', [
            'pageTitle'    => 'Add Blog Category',
            'activeModule' => 'blog-categories',
            'category'     => null,
        ]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(Request::all(['name', 'slug', 'sort_order']));

        if ($errors) {
            redirect_with_errors(admin_url('blog-categories', 'create'), $errors, $_POST);
        }

        if (BlogCategory::slugExists($data['slug'])) {
            redirect_with_errors(admin_url('blog-categories', 'create'), ['slug' => ['That slug is already in use.']], $_POST);
        }

        $id = BlogCategory::create($data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'blog_category.create', 'blog_category', $id, $data['name']);
        Session::flash('success', 'Category created.');
        Response::redirect(admin_url('blog-categories'));
    }

    public function edit(): void
    {
        $id       = Request::intInput('id');
        $category = BlogCategory::find($id);
        if (!$category) {
            Response::notFound();
        }

        View::render('admin/blog-categories/form', [
            'pageTitle'    => 'Edit Blog Category',
            'activeModule' => 'blog-categories',
            'category'     => $category,
        ]);
    }

    public function update(): void
    {
        $id       = Request::intInput('id');
        $category = BlogCategory::find($id);
        if (!$category) {
            Response::notFound();
        }

        [$data, $errors] = $this->validate(Request::all(['name', 'slug', 'sort_order']));

        if ($errors) {
            redirect_with_errors(admin_url('blog-categories', 'edit', ['id' => $id]), $errors, $_POST);
        }

        if (BlogCategory::slugExists($data['slug'], $id)) {
            redirect_with_errors(admin_url('blog-categories', 'edit', ['id' => $id]), ['slug' => ['That slug is already in use.']], $_POST);
        }

        BlogCategory::update($id, $data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'blog_category.update', 'blog_category', $id, $data['name']);
        Session::flash('success', 'Category updated.');
        Response::redirect(admin_url('blog-categories'));
    }

    public function delete(): void
    {
        $id       = Request::intInput('id');
        $category = BlogCategory::find($id);
        if ($category) {
            BlogCategory::delete($id);
            ActivityLog::record(Auth::user()['id'] ?? null, 'blog_category.delete', 'blog_category', $id, $category['name']);
            Session::flash('success', 'Category deleted.');
        }
        Response::redirect(admin_url('blog-categories'));
    }

    private function validate(array $input): array
    {
        if (empty($input['slug']) && !empty($input['name'])) {
            $input['slug'] = slugify($input['name']);
        } else {
            $input['slug'] = slugify((string) ($input['slug'] ?? ''));
        }

        $v = new Validator($input);
        $v->required('name', 'Name')->maxLength('name', 120, 'Name');
        $v->required('slug', 'Slug');

        if ($v->fails()) {
            return [$input, $v->errors()];
        }

        return [[
            'name'       => $input['name'],
            'slug'       => $input['slug'],
            'sort_order' => (int) ($input['sort_order'] ?? 0),
        ], []];
    }
}
