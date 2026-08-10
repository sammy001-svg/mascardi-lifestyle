<?php
/** @var array|null $partner */
/** @var array $pillars */
$isEdit = $partner !== null;
$action = $isEdit
    ? admin_url('partners', 'update', ['id' => $partner['id']])
    : admin_url('partners', 'store');
$val = static function (string $key, $default = '') use ($partner, $isEdit) {
    if (has_field_error($key) || (!$isEdit && old($key) !== '')) {
        return old($key, (string) $default);
    }
    return $isEdit ? ($partner[$key] ?? $default) : $default;
};
?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Edit Partner' : 'Add Partner' ?></h1>
        <p>Shown in the homepage Partners logo grid.</p>
    </div>
    <a href="<?= admin_url('partners') ?>" class="btn btn-outline">&larr; Back to Partners</a>
</div>

<div class="card">
    <div class="card__body">
        <form method="post" action="<?= $action ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Partner name</label>
                    <input class="form-control<?= has_field_error('name') ? ' has-error' : '' ?>" type="text" id="name" name="name" value="<?= e($val('name')) ?>" required>
                    <?php if ($err = field_errors('name')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="website_url">Website URL <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                    <input class="form-control<?= has_field_error('website_url') ? ' has-error' : '' ?>" type="url" id="website_url" name="website_url" value="<?= e($val('website_url')) ?>" placeholder="https://...">
                    <?php if ($err = field_errors('website_url')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="pillar_id">Associated pillar <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                    <select class="form-control" id="pillar_id" name="pillar_id">
                        <option value="">— None —</option>
                        <?php foreach ($pillars as $pillar): ?>
                            <option value="<?= (int) $pillar['id'] ?>" <?= (string) $val('pillar_id') === (string) $pillar['id'] ? 'selected' : '' ?>><?= e($pillar['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="category">Category tag <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                    <input class="form-control" type="text" id="category" name="category" value="<?= e($val('category')) ?>" placeholder="e.g. Hotels, Fashion">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="sort_order">Sort order</label>
                <input class="form-control" type="number" id="sort_order" name="sort_order" value="<?= e($val('sort_order', 0)) ?>" style="max-width:160px;">
            </div>

            <div class="form-group js-media-field">
                <label class="form-label" for="logo">Logo</label>
                <img class="js-media-preview" src="<?= $isEdit && $partner['logo_path'] ? e(upload_url($partner['logo_path'])) : '' ?>" alt="" style="width:120px;height:80px;object-fit:contain;border-radius:8px;background:#f3f4f8;margin-bottom:10px;<?= $isEdit && $partner['logo_path'] ? '' : 'display:none;' ?>">
                <input class="form-control<?= has_field_error('logo') ? ' has-error' : '' ?>" type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
                <input type="hidden" name="picked_media_id" value="">
                <div>
                    <button type="button" class="btn btn-outline btn-sm js-open-media-picker" data-picker-mode="single" style="margin-top:8px;">Choose from Library</button>
                </div>
                <div class="form-hint">JPEG, PNG, or WEBP, up to 50MB. <?= $isEdit ? 'Leave blank to keep the current logo.' : '' ?></div>
                <?php if ($err = field_errors('logo')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= (!$isEdit || $partner['is_active']) ? 'checked' : '' ?>>
                <label for="is_active">Visible on the homepage</label>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Add Partner' ?></button>
        </form>
    </div>
</div>
