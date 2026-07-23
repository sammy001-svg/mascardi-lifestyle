<?php use App\Core\Money; ?>
<div class="page-header">
    <div>
        <h1>Orders</h1>
        <p>All Shop Mascardi orders, newest first.</p>
    </div>
</div>

<div class="stat-grid">
    <?php
    $statusMeta = [
        'pending_payment' => ['label' => 'Pending Payment', 'color' => 'amber'],
        'paid' => ['label' => 'Paid', 'color' => 'green'],
        'processing' => ['label' => 'Processing', 'color' => 'blue'],
        'completed' => ['label' => 'Completed', 'color' => 'teal'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'gray'],
        'failed' => ['label' => 'Failed', 'color' => 'rose'],
        'refunded' => ['label' => 'Refunded', 'color' => 'indigo'],
    ];
    foreach ($statusMeta as $key => $meta):
    ?>
        <a href="<?= admin_url('orders', 'index', ['status' => $key]) ?>" class="stat-card stat-card--<?= $meta['color'] ?>" style="text-decoration:none;color:inherit;">
            <div>
                <div class="stat-card__label"><?= e($meta['label']) ?></div>
                <div class="stat-card__value"><?= (int) ($counts[$key] ?? 0) ?></div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card__header">
        <h2><?= $currentStatus ? e($statusMeta[$currentStatus]['label']) . ' Orders' : 'All Orders' ?></h2>
        <?php if ($currentStatus): ?><a href="<?= admin_url('orders') ?>" class="btn btn-outline btn-sm">Clear filter</a><?php endif; ?>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Placed</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?= e($order['order_number']) ?></strong></td>
                        <td><?= e($order['customer_name']) ?><br><span style="color:#6b7280;font-size:0.8rem;"><?= e($order['customer_phone']) ?></span></td>
                        <td><?= e(Money::format((int) $order['total_cents'])) ?></td>
                        <td>
                            <?php if ($order['payment_status'] === 'paid'): ?>
                                <span class="badge badge-green">Paid</span>
                            <?php elseif ($order['payment_status'] === 'failed'): ?>
                                <span class="badge badge-rose">Failed</span>
                            <?php elseif ($order['payment_status'] === 'pending'): ?>
                                <span class="badge badge-amber">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-gray">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?= $statusMeta[$order['status']]['color'] ?? 'gray' ?>"><?= e($statusMeta[$order['status']]['label'] ?? $order['status']) ?></span></td>
                        <td><?= e($order['created_at']) ?></td>
                        <td><a class="btn btn-outline btn-sm" href="<?= admin_url('orders', 'show', ['id' => $order['id']]) ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7"><div class="empty-state"><div class="empty-state__icon">&#128179;</div><p>No orders yet.</p></div></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
