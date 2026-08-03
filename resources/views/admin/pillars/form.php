<?php
/** @var array|null $pillar */
$isEdit = $pillar !== null;
$action = $isEdit
    ? admin_url('pillars', 'update', ['id' => $pillar['id']])
    : admin_url('pillars', 'store');
$val = static function (string $key, $default = '') use ($pillar, $isEdit) {
    if (has_field_error($key) || (!$isEdit && old($key) !== '')) {
        return old($key, (string) $default);
    }
    return $isEdit ? ($pillar[$key] ?? $default) : $default;
};
?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Edit Pillar' : 'Add Pillar' ?></h1>
        <p>Shown in the homepage grid and on the pillar's own page (<code>/pillars/<?= e($isEdit ? $pillar['slug'] : 'slug') ?></code>).</p>
    </div>
    <div style="display:flex;gap:8px;">
        <?php if ($isEdit): ?><a href="<?= site_url('pillars/' . $pillar['slug']) ?>" target="_blank" class="btn btn-outline">View page &#8599;</a><?php endif; ?>
        <a href="<?= admin_url('pillars') ?>" class="btn btn-outline">&larr; Back to Pillars</a>
    </div>
</div>

<div class="card">
    <div class="card__body">
        <form method="post" action="<?= $action ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control<?= has_field_error('name') ? ' has-error' : '' ?>" type="text" id="name" name="name" value="<?= e($val('name')) ?>" required>
                    <?php if ($err = field_errors('name')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="slug">Slug</label>
                    <input class="form-control<?= has_field_error('slug') ? ' has-error' : '' ?>" type="text" id="slug" name="slug" value="<?= e($val('slug')) ?>" placeholder="auto-generated from name if left blank">
                    <?php if ($err = field_errors('slug')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Intro / summary</label>
                <textarea class="form-control" id="description" name="description" rows="2"><?= e($val('description')) ?></textarea>
                <div class="form-hint">Short line shown under the pillar name on its page.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="body">Full page content</label>
                <textarea class="form-control" id="body" name="body" rows="10"><?= e($val('body')) ?></textarea>
                <div class="form-hint">The main text on the pillar's page. Separate paragraphs with a blank line.</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="link_url">Link URL <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                    <input class="form-control<?= has_field_error('link_url') ? ' has-error' : '' ?>" type="url" id="link_url" name="link_url" value="<?= e($val('link_url')) ?>" placeholder="https://...">
                    <?php if ($err = field_errors('link_url')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="sort_order">Sort order</label>
                    <input class="form-control" type="number" id="sort_order" name="sort_order" value="<?= e($val('sort_order', 0)) ?>">
                    <div class="form-hint">Lower numbers appear first in the grid.</div>
                </div>
            </div>

            <div class="form-group js-media-field">
                <label class="form-label" for="image">Card image</label>
                <img class="js-media-preview" src="<?= $isEdit && $pillar['image_path'] ? e(upload_url($pillar['image_path'])) : '' ?>" alt="" style="width:120px;height:80px;object-fit:cover;border-radius:8px;margin-bottom:10px;<?= $isEdit && $pillar['image_path'] ? '' : 'display:none;' ?>">
                <input class="form-control<?= has_field_error('image') ? ' has-error' : '' ?>" type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp">
                <input type="hidden" name="picked_media_id" value="">
                <div>
                    <button type="button" class="btn btn-outline btn-sm js-open-media-picker" data-picker-mode="single" style="margin-top:8px;">Choose from Library</button>
                </div>
                <div class="form-hint">JPEG, PNG, or WEBP, up to 15MB. <?= $isEdit ? 'Leave blank to keep the current image.' : '' ?></div>
                <?php if ($err = field_errors('image')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= (!$isEdit || $pillar['is_active']) ? 'checked' : '' ?>>
                <label for="is_active">Visible on the homepage</label>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Pillar' ?></button>
        </form>
    </div>
</div>
