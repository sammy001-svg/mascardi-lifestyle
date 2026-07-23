<div class="page-header">
    <div>
        <h1>Partners</h1>
        <p>Brand logos shown in the homepage Partners section.</p>
    </div>
    <a href="<?= admin_url('partners', 'create') ?>" class="btn btn-primary">+ Add Partner</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>Pillar</th>
                    <th>Category</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($partners as $partner): ?>
                    <tr>
                        <td><img class="table-thumb" src="<?= e(upload_url($partner['logo_path'])) ?>" alt=""></td>
                        <td><strong><?= e($partner['name']) ?></strong></td>
                        <td><?= e($partner['pillar_name'] ?? '—') ?></td>
                        <td><?= e($partner['category'] ?: '—') ?></td>
                        <td><?= (int) $partner['sort_order'] ?></td>
                        <td>
                            <?php if ($partner['is_active']): ?>
                                <span class="badge badge-green">Active</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="btn btn-outline btn-sm" href="<?= admin_url('partners', 'edit', ['id' => $partner['id']]) ?>">Edit</a>
                            <form method="post" action="<?= admin_url('partners', 'delete', ['id' => $partner['id']]) ?>" style="display:inline;" onsubmit="return confirm('Remove this partner?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($partners)): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state__icon">&#9670;</div>
                            <p>No partners yet. Add your first brand partnership.</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
