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
use App\Models\ProductCategory;

final class ProductCategoriesController
{
    public function index(): void
    {
        View::render('admin/product-categories/index', [
            'pageTitle' => 'Product Categories',
            'pageSubtitle' => 'Organize the Shop Mascardi catalog',
            'activeModule' => 'products',
            'categories' => ProductCategory::all(),
        ]);
    }

    public function store(): void
    {
        $input = Request::all(['name', 'slug', 'sort_order']);
        $input['slug'] = !empty($input['slug']) ? slugify((string) $input['slug']) : slugify((string) ($input['name'] ?? ''));
        $input['sort_order'] = (int) ($input['sort_order'] ?: 0);

        $v = new Validator($input);
        $v->required('name', 'Name')->maxLength('name', 120, 'Name');

        if ($v->fails() || ProductCategory::slugExists($input['slug'])) {
            $errors = $v->fails() ? $v->errors() : ['name' => ['A category with that name/slug already exists.']];
            redirect_with_errors(admin_url('product-categories'), $errors, $_POST);
        }

        ProductCategory::create($input);
        ActivityLog::record(Auth::user()['id'] ?? null, 'category.create', 'product_category', null, $input['name']);

        Session::flash('success', 'Category added.');
        Response::redirect(admin_url('product-categories'));
    }

    public function update(): void
    {
        $id = Request::intInput('id');
        if (!ProductCategory::find($id)) {
            Response::notFound();
        }

        $input = Request::all(['name', 'slug', 'sort_order']);
        $input['slug'] = !empty($input['slug']) ? slugify((string) $input['slug']) : slugify((string) ($input['name'] ?? ''));
        $input['sort_order'] = (int) ($input['sort_order'] ?: 0);

        $v = new Validator($input);
        $v->required('name', 'Name')->maxLength('name', 120, 'Name');

        if ($v->fails() || ProductCategory::slugExists($input['slug'], $id)) {
            Session::flash('error', 'Could not update category — check the name/slug.');
            Response::redirect(admin_url('product-categories'));
        }

        ProductCategory::update($id, $input);
        ActivityLog::record(Auth::user()['id'] ?? null, 'category.update', 'product_category', $id, $input['name']);

        Session::flash('success', 'Category updated.');
        Response::redirect(admin_url('product-categories'));
    }

    public function delete(): void
    {
        $id = Request::intInput('id');
        $category = ProductCategory::find($id);
        if ($category) {
            ProductCategory::delete($id);
            ActivityLog::record(Auth::user()['id'] ?? null, 'category.delete', 'product_category', $id, $category['name']);
            Session::flash('success', 'Category deleted. Products in this category are now uncategorized.');
        }
        Response::redirect(admin_url('product-categories'));
    }
}
