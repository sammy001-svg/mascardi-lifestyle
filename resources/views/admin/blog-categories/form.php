<?php
/** @var array|null $category */
$isEdit = $category !== null;
$action = $isEdit
    ? admin_url('blog-categories', 'update', ['id' => $category['id']])
    : admin_url('blog-categories', 'store');

$val = static function (string $key, $default = '') use ($category, $isEdit) {
    if (has_field_error($key) || (!$isEdit && old($key) !== '')) {
        return old($key, (string) $default);
    }
    return $isEdit ? ($category[$key] ?? $default) : $default;
};
?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Edit Category' : 'Add Blog Category' ?></h1>
        <p>Used to filter posts on the blog listing page.</p>
    </div>
    <a href="<?= admin_url('blog-categories') ?>" class="btn btn-outline">&larr; Back to Categories</a>
</div>

<div class="card">
    <div class="card__body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Category name</label>
                    <input class="form-control<?= has_field_error('name') ? ' has-error' : '' ?>" type="text" id="name" name="name" value="<?= e($val('name')) ?>" required>
                    <?php if ($err = field_errors('name')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="slug">Slug</label>
                    <input class="form-control<?= has_field_error('slug') ? ' has-error' : '' ?>" type="text" id="slug" name="slug" value="<?= e($val('slug')) ?>" placeholder="auto-generated if left blank">
                    <?php if ($err = field_errors('slug')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group" style="max-width:160px;">
                    <label class="form-label" for="sort_order">Sort order</label>
                    <input class="form-control" type="number" min="0" id="sort_order" name="sort_order" value="<?= e($val('sort_order', 0)) ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Category' ?></button>
        </form>
    </div>
</div>
