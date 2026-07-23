<div class="page-header">
    <div>
        <h1>Welcome back<?= $adminUser ? ', ' . e(explode(' ', $adminUser['name'])[0]) : '' ?></h1>
        <p>Here's what's happening across Mascardi Lifestyle right now.</p>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card stat-card--indigo">
        <div>
            <div class="stat-card__label">Pillars</div>
            <div class="stat-card__value"><?= (int) $pillarCount ?></div>
            <div class="stat-card__hint"><?= (int) $activePillarCount ?> active on the homepage</div>
        </div>
        <div class="stat-card__icon">&#9635;</div>
    </div>
    <div class="stat-card stat-card--teal">
        <div>
            <div class="stat-card__label">Partners</div>
            <div class="stat-card__value"><?= (int) $partnerCount ?></div>
            <div class="stat-card__hint">Brand partnerships listed</div>
        </div>
        <div class="stat-card__icon">&#9670;</div>
    </div>
    <div class="stat-card stat-card--amber">
        <div>
            <div class="stat-card__label">Orders</div>
            <div class="stat-card__value"><?= (int) $orderCount ?></div>
            <div class="stat-card__hint"><a href="<?= admin_url('orders') ?>">View orders &rarr;</a></div>
        </div>
        <div class="stat-card__icon">&#128179;</div>
    </div>
    <div class="stat-card stat-card--rose">
        <div>
            <div class="stat-card__label">Events</div>
            <div class="stat-card__value"><?= (int) $eventCount ?></div>
            <div class="stat-card__hint">Event ticketing launches in Phase 4</div>
        </div>
        <div class="stat-card__icon">&#127903;</div>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <h2>Recent activity</h2>
    </div>
    <div class="card__body">
        <?php if (empty($recentActivity)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">&#128203;</div>
                <p>No activity yet. Changes you make to pillars, partners, and settings will show up here.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Action</th><th>By</th><th>When</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivity as $entry): ?>
                            <tr>
                                <td><?= e($entry['action']) ?></td>
                                <td><?= e($entry['admin_name'] ?? 'System') ?></td>
                                <td><?= e($entry['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
