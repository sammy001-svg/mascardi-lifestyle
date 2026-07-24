<?php
/** @var array $messages */
/** @var string $filter */
/** @var int $unreadCount */
$tab = static function (string $key, string $label, string $current): string {
    $active = $key === $current ? ' btn-primary' : ' btn-outline';
    return '<a class="btn btn-sm' . $active . '" href="' . admin_url('messages', 'index', $key === 'all' ? [] : ['filter' => $key]) . '">' . e($label) . '</a>';
};
?>
<div class="page-header">
    <div>
        <h1>Messages</h1>
        <p>Contact form submissions from the public site<?= $unreadCount > 0 ? ' — <strong>' . (int) $unreadCount . ' unread</strong>' : '' ?>.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <?= $tab('all', 'All', $filter) ?>
        <?= $tab('unread', 'Unread', $filter) ?>
        <?= $tab('read', 'Read', $filter) ?>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                    <?php $unread = (int) $m['is_read'] === 0; ?>
                    <tr style="<?= $unread ? 'font-weight:600;' : '' ?>">
                        <td>
                            <?php if ($unread): ?>
                                <span class="badge badge-rose">New</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Read</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= admin_url('messages', 'show', ['id' => $m['id']]) ?>"><?= e($m['name']) ?></a>
                            <div style="font-weight:400;color:var(--color-text-muted);font-size:0.82rem;"><?= e($m['email']) ?></div>
                        </td>
                        <td><?= $m['subject'] ? e($m['subject']) : '<span style="color:var(--color-text-muted);">—</span>' ?></td>
                        <td><?= e(date('M j, Y g:ia', strtotime($m['created_at']))) ?></td>
                        <td style="white-space:nowrap;">
                            <a class="btn btn-outline btn-sm" href="<?= admin_url('messages', 'show', ['id' => $m['id']]) ?>">View</a>
                            <form method="post" action="<?= admin_url('messages', 'toggleRead', ['id' => $m['id']]) ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline btn-sm"><?= $unread ? 'Mark read' : 'Mark unread' ?></button>
                            </form>
                            <form method="post" action="<?= admin_url('messages', 'delete', ['id' => $m['id']]) ?>" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <div class="empty-state__icon">&#9993;</div>
                            <p>No messages<?= $filter !== 'all' ? ' in this view' : ' yet' ?>. Submissions from the public contact page appear here.</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
