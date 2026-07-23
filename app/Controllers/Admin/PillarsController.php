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
use App\Models\Pillar;

final class PillarsController
{
    public function index(): void
    {
        View::render('admin/pillars/index', [
            'pageTitle' => 'Pillars',
            'pageSubtitle' => 'The 8 lifestyle pillars shown on the homepage',
            'activeModule' => 'pillars',
            'pillars' => Pillar::all(),
        ]);
    }

    public function create(): void
    {
        View::render('admin/pillars/form', [
            'pageTitle' => 'Add Pillar',
            'activeModule' => 'pillars',
            'pillar' => null,
        ]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(Request::all(['name', 'slug', 'description', 'link_url', 'sort_order']));

        if ($errors) {
            redirect_with_errors(admin_url('pillars', 'create'), $errors, $_POST);
        }

        if (Pillar::slugExists($data['slug'])) {
            redirect_with_errors(admin_url('pillars', 'create'), ['slug' => ['That slug is already in use.']], $_POST);
        }

        $imagePath = null;
        if ($file = Request::file('image')) {
            try {
                $imagePath = Uploader::storeImage($file, 'pillars');
            } catch (\RuntimeException $e) {
                redirect_with_errors(admin_url('pillars', 'create'), ['image' => [$e->getMessage()]], $_POST);
            }
        }

        $data['image_path'] = $imagePath;
        $data['is_active'] = Request::boolInput('is_active') ? 1 : 0;

        $id = Pillar::create($data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'pillar.create', 'pillar', $id, $data['name']);

        Session::flash('success', 'Pillar created.');
        Response::redirect(admin_url('pillars'));
    }

    public function edit(): void
    {
        $id = Request::intInput('id');
        $pillar = Pillar::find($id);
        if (!$pillar) {
            Response::notFound();
        }

        View::render('admin/pillars/form', [
            'pageTitle' => 'Edit Pillar',
            'activeModule' => 'pillars',
            'pillar' => $pillar,
        ]);
    }

    public function update(): void
    {
        $id = Request::intInput('id');
        $pillar = Pillar::find($id);
        if (!$pillar) {
            Response::notFound();
        }

        [$data, $errors] = $this->validate(Request::all(['name', 'slug', 'description', 'link_url', 'sort_order']));

        if ($errors) {
            redirect_with_errors(admin_url('pillars', 'edit', ['id' => $id]), $errors, $_POST);
        }

        if (Pillar::slugExists($data['slug'], $id)) {
            redirect_with_errors(admin_url('pillars', 'edit', ['id' => $id]), ['slug' => ['That slug is already in use.']], $_POST);
        }

        if ($file = Request::file('image')) {
            try {
                $data['image_path'] = Uploader::storeImage($file, 'pillars');
                Uploader::delete($pillar['image_path']);
            } catch (\RuntimeException $e) {
                redirect_with_errors(admin_url('pillars', 'edit', ['id' => $id]), ['image' => [$e->getMessage()]], $_POST);
            }
        }

        $data['is_active'] = Request::boolInput('is_active') ? 1 : 0;

        Pillar::update($id, $data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'pillar.update', 'pillar', $id, $data['name']);

        Session::flash('success', 'Pillar updated.');
        Response::redirect(admin_url('pillars'));
    }

    public function delete(): void
    {
        $id = Request::intInput('id');
        $pillar = Pillar::find($id);
        if ($pillar) {
            Uploader::delete($pillar['image_path']);
            Pillar::delete($id);
            ActivityLog::record(Auth::user()['id'] ?? null, 'pillar.delete', 'pillar', $id, $pillar['name']);
            Session::flash('success', 'Pillar deleted.');
        }
        Response::redirect(admin_url('pillars'));
    }

    private function validate(array $input): array
    {
        if (empty($input['slug']) && !empty($input['name'])) {
            $input['slug'] = slugify($input['name']);
        } else {
            $input['slug'] = slugify((string) ($input['slug'] ?? ''));
        }
        $input['sort_order'] = (int) ($input['sort_order'] ?: 0);

        $v = new Validator($input);
        $v->required('name', 'Name')->maxLength('name', 150, 'Name');
        $v->required('slug', 'Slug')->maxLength('slug', 120, 'Slug');
        $v->url('link_url', 'Link URL');

        return [$input, $v->fails() ? $v->errors() : []];
    }
}
