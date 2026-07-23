<div class="page-header">
    <div>
        <h1>Product Categories</h1>
        <p>Group products in the Shop Mascardi catalog.</p>
    </div>
    <a href="<?= admin_url('products') ?>" class="btn btn-outline">&larr; Back to Products</a>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card__header"><h2>Add Category</h2></div>
    <div class="card__body">
        <form method="post" action="<?= admin_url('product-categories', 'store') ?>">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control<?= has_field_error('name') ? ' has-error' : '' ?>" type="text" id="name" name="name" value="<?= old('name') ?>" required>
                    <?php if ($err = field_errors('name')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="sort_order">Sort order</label>
                    <input class="form-control" type="number" id="sort_order" name="sort_order" value="0">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Slug</th><th>Order</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <form method="post" action="<?= admin_url('product-categories', 'update', ['id' => $category['id']]) ?>" style="display:flex;gap:8px;align-items:center;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="slug" value="<?= e($category['slug']) ?>">
                                <input type="hidden" name="sort_order" value="<?= (int) $category['sort_order'] ?>">
                                <input class="form-control" style="max-width:220px;" type="text" name="name" value="<?= e($category['name']) ?>">
                                <button type="submit" class="btn btn-outline btn-sm">Save</button>
                            </form>
                        </td>
                        <td><?= e($category['slug']) ?></td>
                        <td><?= (int) $category['sort_order'] ?></td>
                        <td>
                            <form method="post" action="<?= admin_url('product-categories', 'delete', ['id' => $category['id']]) ?>" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="4"><div class="empty-state"><p>No categories yet.</p></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
