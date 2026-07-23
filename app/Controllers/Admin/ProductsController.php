<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Money;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Uploader;
use App\Core\Validator;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\MediaUpload;
use App\Models\Product;
use App\Models\ProductCategory;

final class ProductsController
{
    public function index(): void
    {
        View::render('admin/products/index', [
            'pageTitle' => 'Products',
            'pageSubtitle' => 'The Shop Mascardi catalog',
            'activeModule' => 'products',
            'products' => Product::all(),
        ]);
    }

    public function create(): void
    {
        View::render('admin/products/form', [
            'pageTitle' => 'Add Product',
            'activeModule' => 'products',
            'product' => null,
            'images' => [],
            'categories' => ProductCategory::all(),
        ]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(Request::all([
            'name', 'slug', 'sku', 'category_id', 'description', 'price', 'compare_at_price', 'stock_quantity',
        ]));

        if ($errors) {
            redirect_with_errors(admin_url('products', 'create'), $errors, $_POST);
        }

        if (Product::slugExists($data['slug'])) {
            redirect_with_errors(admin_url('products', 'create'), ['slug' => ['That slug is already in use.']], $_POST);
        }

        $data['is_featured'] = Request::boolInput('is_featured') ? 1 : 0;
        $data['is_active'] = Request::boolInput('is_active') ? 1 : 0;

        $id = Product::create($data);
        $this->handleImages($id, Auth::user()['id'] ?? null);

        ActivityLog::record(Auth::user()['id'] ?? null, 'product.create', 'product', $id, $data['name']);
        Session::flash('success', 'Product created.');
        Response::redirect(admin_url('products'));
    }

    public function edit(): void
    {
        $id = Request::intInput('id');
        $product = Product::find($id);
        if (!$product) {
            Response::notFound();
        }

        View::render('admin/products/form', [
            'pageTitle' => 'Edit Product',
            'activeModule' => 'products',
            'product' => $product,
            'images' => Product::images($id),
            'categories' => ProductCategory::all(),
        ]);
    }

    public function update(): void
    {
        $id = Request::intInput('id');
        $product = Product::find($id);
        if (!$product) {
            Response::notFound();
        }

        [$data, $errors] = $this->validate(Request::all([
            'name', 'slug', 'sku', 'category_id', 'description', 'price', 'compare_at_price', 'stock_quantity',
        ]));

        if ($errors) {
            redirect_with_errors(admin_url('products', 'edit', ['id' => $id]), $errors, $_POST);
        }

        if (Product::slugExists($data['slug'], $id)) {
            redirect_with_errors(admin_url('products', 'edit', ['id' => $id]), ['slug' => ['That slug is already in use.']], $_POST);
        }

        $data['is_featured'] = Request::boolInput('is_featured') ? 1 : 0;
        $data['is_active'] = Request::boolInput('is_active') ? 1 : 0;

        Product::update($id, $data);
        $this->handleImages($id, Auth::user()['id'] ?? null);

        ActivityLog::record(Auth::user()['id'] ?? null, 'product.update', 'product', $id, $data['name']);
        Session::flash('success', 'Product updated.');
        Response::redirect(admin_url('products', 'edit', ['id' => $id]));
    }

    public function delete(): void
    {
        $id = Request::intInput('id');
        $product = Product::find($id);
        if ($product) {
            $images = Product::images($id);
            Product::delete($id); // product_images rows cascade-delete via FK
            foreach ($images as $image) {
                if (empty(MediaUpload::usages($image['image_path']))) {
                    Uploader::delete($image['image_path']);
                }
            }
            ActivityLog::record(Auth::user()['id'] ?? null, 'product.delete', 'product', $id, $product['name']);
            Session::flash('success', 'Product deleted.');
        }
        Response::redirect(admin_url('products'));
    }

    public function deleteImage(): void
    {
        $imageId = Request::intInput('image_id');
        $productId = Request::intInput('id');
        $image = Product::findImage($imageId);
        if ($image && (int) $image['product_id'] === $productId) {
            Product::deleteImage($imageId);
            if (empty(MediaUpload::usages($image['image_path']))) {
                Uploader::delete($image['image_path']);
            }
        }
        Response::redirect(admin_url('products', 'edit', ['id' => $productId]));
    }

    public function makePrimaryImage(): void
    {
        $imageId = Request::intInput('image_id');
        $productId = Request::intInput('id');
        Product::makeImagePrimary($productId, $imageId);
        Response::redirect(admin_url('products', 'edit', ['id' => $productId]));
    }

    private function handleImages(int $productId, ?int $adminId): void
    {
        $existingCount = count(Product::images($productId));
        $sortOrder = $existingCount;
        $firstImage = $existingCount === 0;

        foreach (Request::normalizeFiles('images') as $file) {
            try {
                $path = Uploader::storeImage($file, 'products', $adminId);
            } catch (\RuntimeException) {
                // Skip files that fail validation rather than aborting the whole save —
                // the product itself was already created/updated successfully.
                continue;
            }
            Product::addImage($productId, $path, $firstImage, $sortOrder);
            $firstImage = false;
            $sortOrder++;
        }

        foreach ((array) ($_POST['picked_media_ids'] ?? []) as $rawId) {
            $media = MediaUpload::find((int) $rawId);
            if (!$media) {
                continue;
            }
            Product::addImage($productId, $media['file_path'], $firstImage, $sortOrder);
            $firstImage = false;
            $sortOrder++;
        }
    }

    private function validate(array $input): array
    {
        if (empty($input['slug']) && !empty($input['name'])) {
            $input['slug'] = slugify($input['name']);
        } else {
            $input['slug'] = slugify((string) ($input['slug'] ?? ''));
        }

        $v = new Validator($input);
        $v->required('name', 'Name')->maxLength('name', 180, 'Name');
        $v->required('slug', 'Slug');
        $v->required('price', 'Price')->numeric('price', 'Price')->min('price', 0, 'Price');
        if ($input['compare_at_price'] !== '') {
            $v->numeric('compare_at_price', 'Compare-at price');
        }
        if ($input['stock_quantity'] !== '') {
            $v->numeric('stock_quantity', 'Stock quantity');
        }

        if ($v->fails()) {
            return [$input, $v->errors()];
        }

        $data = [
            'name' => $input['name'],
            'slug' => $input['slug'],
            'sku' => $input['sku'] ?: null,
            'category_id' => $input['category_id'] !== '' ? (int) $input['category_id'] : null,
            'description' => $input['description'] ?: null,
            'price_cents' => Money::toCents($input['price']),
            'compare_at_price_cents' => $input['compare_at_price'] !== '' ? Money::toCents($input['compare_at_price']) : null,
            'stock_quantity' => $input['stock_quantity'] !== '' ? (int) $input['stock_quantity'] : 0,
        ];

        return [$data, []];
    }
}
