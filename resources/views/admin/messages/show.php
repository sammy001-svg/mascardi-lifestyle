<?php
/** @var array $message */
$mailtoSubject = $message['subject'] ? 'Re: ' . $message['subject'] : 'Re: your enquiry';
?>
<div class="page-header">
    <div>
        <h1>Message</h1>
        <p>Received <?= e(date('M j, Y \a\t g:ia', strtotime($message['created_at']))) ?></p>
    </div>
    <a href="<?= admin_url('messages') ?>" class="btn btn-outline">&larr; Back to Messages</a>
</div>

<div class="card" style="max-width:760px;">
    <div class="card__body">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">From</label>
                <div><?= e($message['name']) ?></div>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <div><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Phone</label>
                <div><?= $message['phone'] ? e($message['phone']) : '<span style="color:var(--color-text-muted);">—</span>' ?></div>
            </div>
            <div class="form-group">
                <label class="form-label">Subject</label>
                <div><?= $message['subject'] ? e($message['subject']) : '<span style="color:var(--color-text-muted);">—</span>' ?></div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Message</label>
            <div style="white-space:pre-wrap;line-height:1.7;background:var(--color-bg);border:1px solid var(--color-border);border-radius:8px;padding:16px;"><?= e($message['message']) ?></div>
        </div>

        <?php if (!empty($message['ip_address'])): ?>
            <div class="form-hint">Submitted from IP <?= e($message['ip_address']) ?></div>
        <?php endif; ?>

        <div style="display:flex;gap:10px;margin-top:22px;align-items:center;">
            <a class="btn btn-primary" href="mailto:<?= e($message['email']) ?>?subject=<?= rawurlencode($mailtoSubject) ?>">Reply by Email</a>
            <form method="post" action="<?= admin_url('messages', 'toggleRead', ['id' => $message['id']]) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline"><?= (int) $message['is_read'] === 0 ? 'Mark as read' : 'Mark as unread' ?></button>
            </form>
            <form method="post" action="<?= admin_url('messages', 'delete', ['id' => $message['id']]) ?>" onsubmit="return confirm('Delete this message?');" style="margin-left:auto;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>
