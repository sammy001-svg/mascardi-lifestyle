<?php /** @var array $categories */ ?>
<div class="page-header">
    <div>
        <h1>Blog Categories</h1>
        <p>Organise your blog posts into topics.</p>
    </div>
    <a href="<?= admin_url('blog-categories', 'create') ?>" class="btn btn-primary">+ Add Category</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Sort Order</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><strong><?= e($category['name']) ?></strong></td>
                        <td><code style="font-size:0.82rem;color:#6b7280;"><?= e($category['slug']) ?></code></td>
                        <td><?= (int) $category['sort_order'] ?></td>
                        <td>
                            <a class="btn btn-outline btn-sm" href="<?= admin_url('blog-categories', 'edit', ['id' => $category['id']]) ?>">Edit</a>
                            <form method="post" action="<?= admin_url('blog-categories', 'delete', ['id' => $category['id']]) ?>" style="display:inline;" onsubmit="return confirm('Delete this category? Posts will become uncategorised.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="4">
                        <div class="empty-state">
                            <div class="empty-state__icon">&#128214;</div>
                            <p>No categories yet. Create one to organise your posts.</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
