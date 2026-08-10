<?php
/** @var array|null $product */
/** @var array $images */
/** @var array $categories */
use App\Core\Money;

$isEdit = $product !== null;
$action = $isEdit
    ? admin_url('products', 'update', ['id' => $product['id']])
    : admin_url('products', 'store');

$val = static function (string $key, $default = '') use ($product, $isEdit) {
    if (has_field_error($key) || (!$isEdit && old($key) !== '')) {
        return old($key, (string) $default);
    }
    return $isEdit ? ($product[$key] ?? $default) : $default;
};

$priceVal = $isEdit && !has_field_error('price') && old('price') === ''
    ? (string) Money::toShillings((int) $product['price_cents'])
    : $val('price');
$compareVal = $isEdit && !has_field_error('compare_at_price') && old('compare_at_price') === '' && $product['compare_at_price_cents']
    ? (string) Money::toShillings((int) $product['compare_at_price_cents'])
    : $val('compare_at_price');
?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h1>
        <p>Shown in the Shop Mascardi carousel and catalog.</p>
    </div>
    <a href="<?= admin_url('products') ?>" class="btn btn-outline">&larr; Back to Products</a>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card__body">
        <form method="post" action="<?= $action ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Product name</label>
                    <input class="form-control<?= has_field_error('name') ? ' has-error' : '' ?>" type="text" id="name" name="name" value="<?= e($val('name')) ?>" required>
                    <?php if ($err = field_errors('name')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="slug">Slug</label>
                    <input class="form-control<?= has_field_error('slug') ? ' has-error' : '' ?>" type="text" id="slug" name="slug" value="<?= e($val('slug')) ?>" placeholder="auto-generated if left blank">
                    <?php if ($err = field_errors('slug')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description"><?= e($val('description')) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="category_id">Category</label>
                    <select class="form-control" id="category_id" name="category_id">
                        <option value="">— Uncategorized —</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (string) $val('category_id') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="sku">SKU <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                    <input class="form-control" type="text" id="sku" name="sku" value="<?= e($val('sku')) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="price">Price (KES)</label>
                    <input class="form-control<?= has_field_error('price') ? ' has-error' : '' ?>" type="number" step="0.01" min="0" id="price" name="price" value="<?= e($priceVal) ?>" required>
                    <?php if ($err = field_errors('price')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="compare_at_price">Compare-at price <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                    <input class="form-control" type="number" step="0.01" min="0" id="compare_at_price" name="compare_at_price" value="<?= e($compareVal) ?>">
                    <div class="form-hint">Shown crossed out for a "was/now" price.</div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="stock_quantity">Stock quantity</label>
                    <input class="form-control" type="number" min="0" id="stock_quantity" name="stock_quantity" value="<?= e($val('stock_quantity', 0)) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group form-check">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" <?= $isEdit && (int) $product['is_featured'] === 1 ? 'checked' : '' ?>>
                    <label for="is_featured">Featured (shown first in the homepage carousel)</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?= (!$isEdit || $product['is_active']) ? 'checked' : '' ?>>
                    <label for="is_active">Visible in the shop</label>
                </div>
            </div>

            <div class="form-group js-media-field">
                <label class="form-label" for="images">Add photos</label>
                <input class="form-control" type="file" id="images" name="images[]" accept="image/png,image/jpeg,image/webp" multiple>
                <div class="form-hint">JPEG, PNG, or WEBP, up to 50MB each. The first photo uploaded becomes the primary image.</div>
                <div class="js-picked-list" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;"></div>
                <button type="button" class="btn btn-outline btn-sm js-open-media-picker" data-picker-mode="multi" style="margin-top:8px;">Add from Library</button>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Product' ?></button>
        </form>
    </div>
</div>

<?php if ($isEdit): ?>
    <div class="card">
        <div class="card__header"><h2>Photos</h2></div>
        <div class="card__body">
            <?php if (empty($images)): ?>
                <p style="color:#6b7280;font-size:0.9rem;">No photos uploaded yet.</p>
            <?php else: ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:16px;">
                    <?php foreach ($images as $image): ?>
                        <div style="border:1px solid var(--color-border);border-radius:10px;overflow:hidden;">
                            <img src="<?= e(upload_url($image['image_path'])) ?>" alt="" style="width:100%;height:120px;object-fit:cover;">
                            <div style="padding:10px;display:flex;flex-direction:column;gap:6px;">
                                <?php if ($image['is_primary']): ?>
                                    <span class="badge badge-indigo" style="align-self:flex-start;">Primary</span>
                                <?php else: ?>
                                    <form method="post" action="<?= admin_url('products', 'makePrimaryImage', ['id' => $product['id'], 'image_id' => $image['id']]) ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-outline btn-sm" style="width:100%;">Make Primary</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="<?= admin_url('products', 'deleteImage', ['id' => $product['id'], 'image_id' => $image['id']]) ?>" onsubmit="return confirm('Remove this photo?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
