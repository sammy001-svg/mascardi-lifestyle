<?php
/** @var array|null $album */
/** @var array $images */
$isEdit = $album !== null;
$action = $isEdit
    ? admin_url('gallery', 'update', ['id' => $album['id']])
    : admin_url('gallery', 'store');

$val = static function (string $key, $default = '') use ($album, $isEdit) {
    if (has_field_error($key) || (!$isEdit && old($key) !== '')) {
        return old($key, (string) $default);
    }
    return $isEdit ? ($album[$key] ?? $default) : $default;
};
?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Edit Album' : 'New Album' ?></h1>
        <p>Upload photos and organise them into a shareable collection.</p>
    </div>
    <a href="<?= admin_url('gallery') ?>" class="btn btn-outline">&larr; Back to Albums</a>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card__body">
        <form method="post" action="<?= $action ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group" style="flex:2;">
                    <label class="form-label" for="name">Album name</label>
                    <input class="form-control<?= has_field_error('name') ? ' has-error' : '' ?>"
                           type="text" id="name" name="name" value="<?= e($val('name')) ?>" required>
                    <?php if ($err = field_errors('name')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label" for="slug">Slug</label>
                    <input class="form-control<?= has_field_error('slug') ? ' has-error' : '' ?>"
                           type="text" id="slug" name="slug" value="<?= e($val('slug')) ?>" placeholder="auto-generated">
                    <?php if ($err = field_errors('slug')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group" style="max-width:140px;">
                    <label class="form-label" for="sort_order">Sort order</label>
                    <input class="form-control" type="number" min="0" id="sort_order" name="sort_order" value="<?= e($val('sort_order', 0)) ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= e($val('description')) ?></textarea>
            </div>

            <!-- Cover image (upload OR pick one from the library) -->
            <div class="form-group js-media-field" data-picked-name="picked_cover_ids" data-picker-single="true">
                <label class="form-label">Cover image</label>
                <?php if ($isEdit && $album['cover_image_path']): ?>
                    <div style="margin-bottom:10px;">
                        <img src="<?= e(upload_url($album['cover_image_path'])) ?>" alt="Cover"
                             style="height:120px;border-radius:8px;object-fit:cover;border:1px solid var(--color-border);">
                    </div>
                <?php endif; ?>
                <input class="form-control" type="file" id="cover_image" name="cover_image"
                       accept="image/png,image/jpeg,image/webp">
                <div class="form-hint">Used as the album card thumbnail. Upload one, or pick from the library.</div>
                <div class="js-picked-list" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;"></div>
                <button type="button" class="btn btn-outline btn-sm js-open-media-picker"
                        data-picker-mode="multi" style="margin-top:6px;">Pick cover from Library</button>
            </div>

            <!-- Gallery photos (upload multiple OR pick from the library) -->
            <div class="form-group js-media-field" data-picked-name="picked_media_ids">
                <label class="form-label" for="images">Add photos to album</label>
                <input class="form-control" type="file" id="images" name="images[]"
                       accept="image/png,image/jpeg,image/webp" multiple>
                <div class="form-hint">Upload multiple at once, or pick existing images from the library below.</div>
                <div class="js-picked-list" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;"></div>
                <button type="button" class="btn btn-outline btn-sm js-open-media-picker"
                        data-picker-mode="multi" style="margin-top:6px;">Pick photos from Library</button>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       <?= (!$isEdit || $album['is_active']) ? 'checked' : '' ?>>
                <label for="is_active">Visible on the public gallery page</label>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Album' ?></button>
        </form>
    </div>
</div>

<?php if ($isEdit && !empty($images)): ?>
<div class="card">
    <div class="card__header"><h2>Photos in this album (<?= count($images) ?>)</h2></div>
    <div class="card__body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;">
            <?php foreach ($images as $image): ?>
                <div style="border:1px solid var(--color-border);border-radius:10px;overflow:hidden;">
                    <img src="<?= e(upload_url($image['image_path'])) ?>" alt="<?= e($image['caption'] ?? '') ?>"
                         style="width:100%;height:130px;object-fit:cover;">
                    <div style="padding:10px;">
                        <?php if ($image['caption']): ?>
                            <p style="font-size:0.8rem;color:#6b7280;margin:0 0 8px;"><?= e($image['caption']) ?></p>
                        <?php endif; ?>
                        <form method="post"
                              action="<?= admin_url('gallery', 'deleteImage', ['id' => $album['id'], 'image_id' => $image['id']]) ?>"
                              onsubmit="return confirm('Remove this image from the album?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php elseif ($isEdit): ?>
<div class="card">
    <div class="card__body">
        <div class="empty-state">
            <div class="empty-state__icon">&#128247;</div>
            <p>No photos in this album yet. Upload some above.</p>
        </div>
    </div>
</div>
<?php endif; ?>
