<?php /** @var array $posts */ ?>
<div class="page-header">
    <div>
        <h1>Blog Posts</h1>
        <p>Articles published on the Mascardi Lifestyle blog.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?= admin_url('blog-categories') ?>" class="btn btn-outline">Manage Categories</a>
        <a href="<?= admin_url('blog-posts', 'create') ?>" class="btn btn-primary">+ New Post</a>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <?php if ($post['cover_image_path']): ?>
                                <img class="table-thumb" src="<?= e(upload_url($post['cover_image_path'])) ?>" alt="">
                            <?php else: ?>
                                <span class="table-thumb-placeholder">No img</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($post['title']) ?></strong>
                            <?php if ($post['excerpt']): ?>
                                <br><span style="font-size:0.8rem;color:#6b7280;"><?= e(mb_strimwidth($post['excerpt'], 0, 80, '…')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($post['category_name'] ?? '—') ?></td>
                        <td>
                            <?php if ($post['status'] === 'published'): ?>
                                <span class="badge badge-green">Published</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.85rem;white-space:nowrap;">
                            <?= $post['published_at'] ? e(date('d M Y', strtotime($post['published_at']))) : '—' ?>
                        </td>
                        <td>
                            <a class="btn btn-outline btn-sm" href="<?= admin_url('blog-posts', 'edit', ['id' => $post['id']]) ?>">Edit</a>
                            <form method="post" action="<?= admin_url('blog-posts', 'delete', ['id' => $post['id']]) ?>" style="display:inline;" onsubmit="return confirm('Delete this post permanently?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($posts)): ?>
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state__icon">&#9998;</div>
                            <p>No posts yet. Write your first article.</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
