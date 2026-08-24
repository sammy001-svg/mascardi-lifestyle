<?php
/** @var array|null $post */
/** @var array $categories */
$isEdit = $post !== null;
$action = $isEdit
    ? admin_url('blog-posts', 'update', ['id' => $post['id']])
    : admin_url('blog-posts', 'store');

$val = static function (string $key, $default = '') use ($post, $isEdit) {
    if (has_field_error($key) || (!$isEdit && old($key) !== '')) {
        return old($key, (string) $default);
    }
    return $isEdit ? ($post[$key] ?? $default) : $default;
};
?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Edit Post' : 'New Blog Post' ?></h1>
        <p>Craft and publish articles for the Mascardi blog.</p>
    </div>
    <a href="<?= admin_url('blog-posts') ?>" class="btn btn-outline">&larr; Back to Posts</a>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card__body">
        <form method="post" action="<?= $action ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- Title & Slug -->
            <div class="form-row">
                <div class="form-group" style="flex:2;">
                    <label class="form-label" for="title">Title</label>
                    <input class="form-control<?= has_field_error('title') ? ' has-error' : '' ?>"
                           type="text" id="title" name="title" value="<?= e($val('title')) ?>" required>
                    <?php if ($err = field_errors('title')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label" for="slug">Slug</label>
                    <input class="form-control<?= has_field_error('slug') ? ' has-error' : '' ?>"
                           type="text" id="slug" name="slug" value="<?= e($val('slug')) ?>" placeholder="auto-generated">
                    <?php if ($err = field_errors('slug')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
            </div>

            <!-- Category & Status & Published At -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="category_id">Category</label>
                    <select class="form-control" id="category_id" name="category_id">
                        <option value="">— Uncategorised —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>"
                                <?= (string) $val('category_id') === (string) $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="max-width:180px;">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-control<?= has_field_error('status') ? ' has-error' : '' ?>" id="status" name="status">
                        <option value="draft"     <?= $val('status', 'draft') === 'draft'     ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $val('status', 'draft') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="published_at">Publish date <span style="font-weight:400;color:#6b7280;">(leave blank to use now)</span></label>
                    <input class="form-control" type="datetime-local" id="published_at" name="published_at"
                           value="<?= e($isEdit && $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : $val('published_at')) ?>">
                </div>
            </div>

            <!-- Excerpt -->
            <div class="form-group">
                <label class="form-label" for="excerpt">Excerpt <span style="font-weight:400;color:#6b7280;">(optional — shown on listing cards)</span></label>
                <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?= e($val('excerpt')) ?></textarea>
            </div>

            <!-- Cover image -->
            <div class="form-group js-media-field">
                <label class="form-label">Cover image</label>
                <?php if ($isEdit && $post['cover_image_path']): ?>
                    <div style="margin-bottom:10px;">
                        <img src="<?= e(upload_url($post['cover_image_path'])) ?>" alt="Cover"
                             style="height:120px;border-radius:8px;object-fit:cover;border:1px solid var(--color-border);">
                    </div>
                <?php endif; ?>
                <input class="form-control" type="file" id="cover_image" name="cover_image"
                       accept="image/png,image/jpeg,image/webp">
                <div class="form-hint">Upload a new image to replace the current cover. JPEG, PNG or WEBP.</div>
                <div class="js-picked-list" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;"></div>
                <button type="button" class="btn btn-outline btn-sm js-open-media-picker"
                        data-picker-mode="single" style="margin-top:6px;">Pick from Library</button>
            </div>

            <!-- Rich-text body (TinyMCE) -->
            <div class="form-group">
                <label class="form-label" for="body">Article body</label>
                <textarea class="form-control" id="body" name="body" rows="20"><?= e($val('body')) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Post' ?></button>
        </form>
    </div>
</div>

<!-- TinyMCE (free, CDN) -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#body',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    height: 520,
    skin: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oxide-dark' : 'oxide',
    content_css: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'default',
    promotion: false,
    branding: false,
    images_upload_url: false,
    automatic_uploads: false,
});
</script>
