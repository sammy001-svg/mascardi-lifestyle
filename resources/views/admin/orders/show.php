<?php use App\Core\Money; ?>
<div class="page-header">
    <div>
        <h1>Order <?= e($order['order_number']) ?></h1>
        <p>Placed <?= e($order['created_at']) ?></p>
    </div>
    <a href="<?= admin_url('orders') ?>" class="btn btn-outline">&larr; Back to Orders</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <div class="card">
        <div class="card__header"><h2>Items</h2></div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Product</th><th>Unit Price</th><th>Qty</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['product_name_snapshot']) ?></td>
                            <td><?= e(Money::format((int) $item['unit_price_cents_snapshot'])) ?></td>
                            <td><?= (int) $item['quantity'] ?></td>
                            <td><?= e(Money::format((int) $item['line_total_cents'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="3" style="text-align:right;font-weight:700;">Total</td><td style="font-weight:700;"><?= e(Money::format((int) $order['total_cents'])) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
            <div class="card__header"><h2>Customer</h2></div>
            <div class="card__body">
                <p style="margin:0 0 6px;"><strong><?= e($order['customer_name']) ?></strong></p>
                <p style="margin:0 0 6px;color:#6b7280;"><?= e($order['customer_phone']) ?></p>
                <?php if ($order['customer_email']): ?><p style="margin:0 0 6px;color:#6b7280;"><?= e($order['customer_email']) ?></p><?php endif; ?>
                <?php if ($order['delivery_notes']): ?><p style="margin:10px 0 0;font-size:0.85rem;"><strong>Notes:</strong> <?= e($order['delivery_notes']) ?></p><?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card__header"><h2>M-Pesa Payment</h2></div>
            <div class="card__body">
                <?php if ($transaction): ?>
                    <p style="margin:0 0 6px;">Status: <span class="badge badge-<?= $transaction['status'] === 'success' ? 'green' : ($transaction['status'] === 'failed' ? 'rose' : 'amber') ?>"><?= e(ucfirst($transaction['status'])) ?></span></p>
                    <p style="margin:0 0 6px;color:#6b7280;font-size:0.85rem;">Phone: <?= e($transaction['phone_number']) ?></p>
                    <?php if ($transaction['mpesa_receipt_number']): ?><p style="margin:0 0 6px;color:#6b7280;font-size:0.85rem;">Receipt: <?= e($transaction['mpesa_receipt_number']) ?></p><?php endif; ?>
                    <?php if ($transaction['result_desc']): ?><p style="margin:0;color:#6b7280;font-size:0.85rem;"><?= e($transaction['result_desc']) ?></p><?php endif; ?>
                <?php else: ?>
                    <p style="color:#6b7280;font-size:0.9rem;">No M-Pesa transaction recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card__header"><h2>Manage Order</h2></div>
            <div class="card__body">
                <form method="post" action="<?= admin_url('orders', 'updateStatus', ['id' => $order['id']]) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="status">Order status</label>
                        <select class="form-control" id="status" name="status">
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= e($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $s))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="admin_notes">Internal notes</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes"><?= e($order['admin_notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
