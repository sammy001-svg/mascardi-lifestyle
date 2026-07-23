<?php use App\Core\Money; ?>
<div class="page-header">
    <div>
        <h1>Registrations</h1>
        <p>Free RSVPs and paid ticket sales across all events.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?= admin_url('registrations', 'export', $currentEventId !== '' ? ['event_id' => $currentEventId] : []) ?>" class="btn btn-outline">Export CSV</a>
        <a href="<?= admin_url('events') ?>" class="btn btn-outline">&larr; Back to Events</a>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card__body" style="display:flex;gap:12px;align-items:center;">
        <label class="form-label" for="event_id" style="margin:0;">Filter by event:</label>
        <select class="form-control" id="event_id" style="max-width:320px;" onchange="window.location.href='<?= admin_url('registrations') ?>&event_id=' + this.value">
            <option value="">All events</option>
            <?php foreach ($events as $event): ?>
                <option value="<?= (int) $event['id'] ?>" <?= (string) $currentEventId === (string) $event['id'] ? 'selected' : '' ?>><?= e($event['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Event</th><th>Attendee</th><th>Qty</th><th>Amount</th><th>Status</th><th>Ticket</th><th>Checked In</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $r): ?>
                    <tr>
                        <td><?= e($r['event_title']) ?></td>
                        <td><?= e($r['attendee_name']) ?><br><span style="color:#6b7280;font-size:0.8rem;"><?= e($r['attendee_phone']) ?></span></td>
                        <td><?= (int) $r['quantity'] ?></td>
                        <td><?= $r['total_amount_cents'] > 0 ? e(Money::format((int) $r['total_amount_cents'])) : 'Free' ?></td>
                        <td>
                            <?php if ($r['status'] === 'confirmed'): ?>
                                <span class="badge badge-green">Confirmed</span>
                            <?php elseif ($r['status'] === 'cancelled'): ?>
                                <span class="badge badge-gray">Cancelled</span>
                            <?php else: ?>
                                <span class="badge badge-amber">Pending Payment</span>
                            <?php endif; ?>
                            <?php if (!empty($r['admin_notes'])): ?>
                                <div style="color:var(--color-rose);font-size:0.75rem;margin-top:4px;"><?= e($r['admin_notes']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= e($r['ticket_code'] ?: '—') ?></td>
                        <td>
                            <?php if ($r['checked_in_at']): ?>
                                <span class="badge badge-indigo">✓ <?= e($r['checked_in_at']) ?></span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['status'] === 'confirmed' && !$r['checked_in_at']): ?>
                                <form method="post" action="<?= admin_url('registrations', 'checkIn', ['id' => $r['id']]) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI'] ?? '') ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">Check In</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($registrations)): ?>
                    <tr><td colspan="8"><div class="empty-state"><p>No registrations yet.</p></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
