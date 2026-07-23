<?php
/** @var array $items */
/** @var string $search */
/** @var int $page */
/** @var int $totalPages */
use App\Core\View;
?>
<div class="page-header">
    <div>
        <h1>Media Library</h1>
        <p>Every image uploaded across the admin. Reused across Pillars, Partners, Products, and Events.</p>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card__body">
        <form method="post" action="<?= admin_url('media', 'store') ?>" enctype="multipart/form-data" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <?= csrf_field() ?>
            <div class="form-group" style="flex:1;min-width:240px;margin-bottom:0;">
                <label class="form-label" for="images">Upload new images</label>
                <input class="form-control" type="file" id="images" name="images[]" accept="image/png,image/jpeg,image/webp" multiple>
                <div class="form-hint">JPEG, PNG, or WEBP, up to 5MB each.</div>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <form method="get" action="<?= admin_url('media') ?>" style="display:flex;gap:10px;">
            <input type="hidden" name="module" value="media">
            <input type="hidden" name="action" value="index">
            <input class="form-control" type="search" name="search" value="<?= e($search) ?>" placeholder="Search by filename...">
            <button type="submit" class="btn btn-outline">Search</button>
        </form>
    </div>
    <div class="card__body">
        <?= View::renderPartial('admin/media/_grid', ['items' => $items, 'showDelete' => true]) ?>

        <?php if ($totalPages > 1): ?>
            <div style="display:flex;gap:8px;justify-content:center;margin-top:20px;">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>" href="<?= admin_url('media', 'index', ['search' => $search, 'page' => $p]) ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
