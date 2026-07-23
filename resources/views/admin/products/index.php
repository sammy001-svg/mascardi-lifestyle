<?php use App\Core\Money; ?>
<div class="page-header">
    <div>
        <h1>Products</h1>
        <p>Everything shown in the Shop Mascardi section.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?= admin_url('product-categories') ?>" class="btn btn-outline">Manage Categories</a>
        <a href="<?= admin_url('products', 'create') ?>" class="btn btn-primary">+ Add Product</a>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['primary_image']): ?>
                                <img class="table-thumb" src="<?= e(upload_url($product['primary_image'])) ?>" alt="">
                            <?php else: ?>
                                <span class="table-thumb-placeholder">No img</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($product['name']) ?></strong>
                            <?php if ($product['is_featured']): ?> <span class="badge badge-indigo">Featured</span><?php endif; ?>
                        </td>
                        <td><?= e($product['category_name'] ?? '—') ?></td>
                        <td><?= e(Money::format((int) $product['price_cents'])) ?></td>
                        <td>
                            <?= (int) $product['stock_quantity'] ?>
                            <?php if ((int) $product['stock_quantity'] === 0): ?><span class="badge badge-rose">Out of stock</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($product['is_active']): ?>
                                <span class="badge badge-green">Active</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="btn btn-outline btn-sm" href="<?= admin_url('products', 'edit', ['id' => $product['id']]) ?>">Edit</a>
                            <form method="post" action="<?= admin_url('products', 'delete', ['id' => $product['id']]) ?>" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state__icon">&#128722;</div>
                            <p>No products yet. Add your first one to populate Shop Mascardi.</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
