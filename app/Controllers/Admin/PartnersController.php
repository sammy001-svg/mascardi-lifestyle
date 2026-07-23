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
use App\Models\Partner;
use App\Models\Pillar;

final class PartnersController
{
    public function index(): void
    {
        View::render('admin/partners/index', [
            'pageTitle' => 'Partners',
            'pageSubtitle' => 'Brand partners shown in the Partners section',
            'activeModule' => 'partners',
            'partners' => Partner::all(),
        ]);
    }

    public function create(): void
    {
        View::render('admin/partners/form', [
            'pageTitle' => 'Add Partner',
            'activeModule' => 'partners',
            'partner' => null,
            'pillars' => Pillar::all(),
        ]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(Request::all(['name', 'pillar_id', 'website_url', 'category', 'sort_order']));

        $file = Request::file('logo');
        if (!$file) {
            $errors['logo'] = ['A partner logo is required.'];
        }

        if ($errors) {
            redirect_with_errors(admin_url('partners', 'create'), $errors, $_POST);
        }

        try {
            $data['logo_path'] = Uploader::storeImage($file, 'partners');
        } catch (\RuntimeException $e) {
            redirect_with_errors(admin_url('partners', 'create'), ['logo' => [$e->getMessage()]], $_POST);
        }

        $data['is_active'] = Request::boolInput('is_active') ? 1 : 0;

        $id = Partner::create($data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'partner.create', 'partner', $id, $data['name']);

        Session::flash('success', 'Partner added.');
        Response::redirect(admin_url('partners'));
    }

    public function edit(): void
    {
        $id = Request::intInput('id');
        $partner = Partner::find($id);
        if (!$partner) {
            Response::notFound();
        }

        View::render('admin/partners/form', [
            'pageTitle' => 'Edit Partner',
            'activeModule' => 'partners',
            'partner' => $partner,
            'pillars' => Pillar::all(),
        ]);
    }

    public function update(): void
    {
        $id = Request::intInput('id');
        $partner = Partner::find($id);
        if (!$partner) {
            Response::notFound();
        }

        [$data, $errors] = $this->validate(Request::all(['name', 'pillar_id', 'website_url', 'category', 'sort_order']));

        if ($errors) {
            redirect_with_errors(admin_url('partners', 'edit', ['id' => $id]), $errors, $_POST);
        }

        if ($file = Request::file('logo')) {
            try {
                $data['logo_path'] = Uploader::storeImage($file, 'partners');
                Uploader::delete($partner['logo_path']);
            } catch (\RuntimeException $e) {
                redirect_with_errors(admin_url('partners', 'edit', ['id' => $id]), ['logo' => [$e->getMessage()]], $_POST);
            }
        }

        $data['is_active'] = Request::boolInput('is_active') ? 1 : 0;

        Partner::update($id, $data);
        ActivityLog::record(Auth::user()['id'] ?? null, 'partner.update', 'partner', $id, $data['name']);

        Session::flash('success', 'Partner updated.');
        Response::redirect(admin_url('partners'));
    }

    public function delete(): void
    {
        $id = Request::intInput('id');
        $partner = Partner::find($id);
        if ($partner) {
            Uploader::delete($partner['logo_path']);
            Partner::delete($id);
            ActivityLog::record(Auth::user()['id'] ?? null, 'partner.delete', 'partner', $id, $partner['name']);
            Session::flash('success', 'Partner removed.');
        }
        Response::redirect(admin_url('partners'));
    }

    private function validate(array $input): array
    {
        $input['sort_order'] = (int) ($input['sort_order'] ?: 0);
        $input['pillar_id'] = $input['pillar_id'] !== '' ? (int) $input['pillar_id'] : null;

        $v = new Validator($input);
        $v->required('name', 'Name')->maxLength('name', 150, 'Name');
        $v->url('website_url', 'Website URL');

        return [$input, $v->fails() ? $v->errors() : []];
    }
}
