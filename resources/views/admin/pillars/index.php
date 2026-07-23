<div class="page-header">
    <div>
        <h1>Pillars</h1>
        <p>These 8 cards appear on the homepage exactly in this order.</p>
    </div>
    <a href="<?= admin_url('pillars', 'create') ?>" class="btn btn-primary">+ Add Pillar</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pillars as $pillar): ?>
                    <tr>
                        <td>
                            <?php if ($pillar['image_path']): ?>
                                <img class="table-thumb" src="<?= e(upload_url($pillar['image_path'])) ?>" alt="">
                            <?php else: ?>
                                <span class="table-thumb-placeholder">No img</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= e($pillar['name']) ?></strong></td>
                        <td><?= e($pillar['slug']) ?></td>
                        <td><?= (int) $pillar['sort_order'] ?></td>
                        <td>
                            <?php if ($pillar['is_active']): ?>
                                <span class="badge badge-green">Active</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="btn btn-outline btn-sm" href="<?= admin_url('pillars', 'edit', ['id' => $pillar['id']]) ?>">Edit</a>
                            <form method="post" action="<?= admin_url('pillars', 'delete', ['id' => $pillar['id']]) ?>" style="display:inline;" onsubmit="return confirm('Delete this pillar?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($pillars)): ?>
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state__icon">&#9635;</div>
                            <p>No pillars yet. Add your first one to populate the homepage.</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
