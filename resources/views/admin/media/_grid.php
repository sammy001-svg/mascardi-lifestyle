<?php
/** @var array $items */
/** @var bool $showDelete */
$showDelete = $showDelete ?? false;
?>
<?php if (empty($items)): ?>
    <div class="empty-state">
        <div class="empty-state__icon">&#128247;</div>
        <p>No images found.</p>
    </div>
<?php else: ?>
    <div class="media-grid">
        <?php foreach ($items as $item): ?>
            <?php if ($showDelete): ?>
                <div class="media-grid__card">
                    <img src="<?= e(upload_url($item['file_path'])) ?>" alt="<?= e($item['original_filename']) ?>" loading="lazy">
                    <div class="media-grid__caption" title="<?= e($item['original_filename']) ?>"><?= e($item['original_filename']) ?></div>
                    <form method="post" action="<?= admin_url('media', 'delete', ['id' => $item['id']]) ?>" onsubmit="return confirm('Delete this file from the library?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">Delete</button>
                    </form>
                </div>
            <?php else: ?>
                <button type="button" class="media-grid__item" data-media-id="<?= (int) $item['id'] ?>" data-preview="<?= e(upload_url($item['file_path'])) ?>" title="<?= e($item['original_filename']) ?>">
                    <img src="<?= e(upload_url($item['file_path'])) ?>" alt="<?= e($item['original_filename']) ?>" loading="lazy">
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
