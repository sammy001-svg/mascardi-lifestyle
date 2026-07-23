<?php use App\Core\Money; ?>
<div class="page-header">
    <div>
        <h1>Events</h1>
        <p>Scheduled experiences shown in the homepage Events section.</p>
    </div>
    <a href="<?= admin_url('events', 'create') ?>" class="btn btn-primary">+ Add Event</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Event</th><th>Type</th><th>Starts</th><th>Venue</th><th>Capacity</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><strong><?= e($event['title']) ?></strong></td>
                        <td>
                            <?php if ($event['event_type'] === 'paid'): ?>
                                <span class="badge badge-indigo"><?= e(Money::format((int) $event['ticket_price_cents'])) ?></span>
                            <?php else: ?>
                                <span class="badge badge-green">Free RSVP</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($event['starts_at']) ?></td>
                        <td><?= e($event['venue'] ?: '—') ?></td>
                        <td><?= $event['capacity'] !== null ? (int) $event['capacity'] : 'Unlimited' ?></td>
                        <td>
                            <?php if ($event['is_active']): ?>
                                <span class="badge badge-green">Active</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="btn btn-outline btn-sm" href="<?= admin_url('registrations', 'index', ['event_id' => $event['id']]) ?>">Registrations</a>
                            <a class="btn btn-outline btn-sm" href="<?= admin_url('events', 'edit', ['id' => $event['id']]) ?>">Edit</a>
                            <form method="post" action="<?= admin_url('events', 'delete', ['id' => $event['id']]) ?>" style="display:inline;" onsubmit="return confirm('Delete this event? All registrations will be removed too.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($events)): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state__icon">&#127903;</div>
                            <p>No events yet. Schedule your first one.</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
