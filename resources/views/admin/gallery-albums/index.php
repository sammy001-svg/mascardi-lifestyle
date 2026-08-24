<?php /** @var array $albums */ ?>
<div class="page-header">
    <div>
        <h1>Gallery Albums</h1>
        <p>Groups of photos shown on the public gallery page.</p>
    </div>
    <a href="<?= admin_url('gallery', 'create') ?>" class="btn btn-primary">+ New Album</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Name</th>
                    <th>Images</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($albums as $album): ?>
                    <tr>
                        <td>
                            <?php if ($album['cover_image_path']): ?>
                                <img class="table-thumb" src="<?= e(upload_url($album['cover_image_path'])) ?>" alt="">
                            <?php else: ?>
                                <span class="table-thumb-placeholder">No img</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($album['name']) ?></strong>
                            <?php if ($album['description']): ?>
                                <br><span style="font-size:0.8rem;color:#6b7280;"><?= e(mb_strimwidth($album['description'], 0, 70, '…')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-indigo"><?= (int) $album['image_count'] ?></span></td>
                        <td><?= (int) $album['sort_order'] ?></td>
                        <td>
                            <?php if ($album['is_active']): ?>
                                <span class="badge badge-green">Visible</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="btn btn-outline btn-sm" href="<?= admin_url('gallery', 'edit', ['id' => $album['id']]) ?>">Edit</a>
                            <form method="post" action="<?= admin_url('gallery', 'delete', ['id' => $album['id']]) ?>" style="display:inline;" onsubmit="return confirm('Delete this album and all its images?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($albums)): ?>
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state__icon">&#128247;</div>
                            <p>No albums yet. Create your first photo collection.</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
