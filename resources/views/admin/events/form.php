<?php
/** @var array|null $event */
use App\Core\Money;

$isEdit = $event !== null;
$action = $isEdit
    ? admin_url('events', 'update', ['id' => $event['id']])
    : admin_url('events', 'store');

$val = static function (string $key, $default = '') use ($event, $isEdit) {
    if (has_field_error($key) || (!$isEdit && old($key) !== '')) {
        return old($key, (string) $default);
    }
    return $isEdit ? ($event[$key] ?? $default) : $default;
};

$toDatetimeLocal = static function (?string $value): string {
    if (!$value) {
        return '';
    }
    return str_replace(' ', 'T', substr($value, 0, 16));
};

$startsVal = $isEdit && !has_field_error('starts_at') && old('starts_at') === '' ? $toDatetimeLocal($event['starts_at']) : $val('starts_at');
$endsVal = $isEdit && !has_field_error('ends_at') && old('ends_at') === '' ? $toDatetimeLocal($event['ends_at'] ?? null) : $val('ends_at');
$priceVal = $isEdit && !has_field_error('ticket_price') && old('ticket_price') === '' ? (string) Money::toShillings((int) $event['ticket_price_cents']) : $val('ticket_price');
$currentType = $isEdit ? $event['event_type'] : ($val('event_type') ?: 'free');
?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Edit Event' : 'Add Event' ?></h1>
        <p>Shown in the homepage Events section and the public events listing.</p>
    </div>
    <a href="<?= admin_url('events') ?>" class="btn btn-outline">&larr; Back to Events</a>
</div>

<div class="card">
    <div class="card__body">
        <form method="post" action="<?= $action ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="title">Event title</label>
                    <input class="form-control<?= has_field_error('title') ? ' has-error' : '' ?>" type="text" id="title" name="title" value="<?= e($val('title')) ?>" required>
                    <?php if ($err = field_errors('title')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="slug">Slug</label>
                    <input class="form-control" type="text" id="slug" name="slug" value="<?= e($val('slug')) ?>" placeholder="auto-generated if left blank">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description"><?= e($val('description')) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Event type</label>
                <div style="display:flex;gap:24px;">
                    <label class="form-check"><input type="radio" name="event_type" value="free" id="typeFree" <?= $currentType !== 'paid' ? 'checked' : '' ?>> Free (RSVP)</label>
                    <label class="form-check"><input type="radio" name="event_type" value="paid" id="typePaid" <?= $currentType === 'paid' ? 'checked' : '' ?>> Paid (M-Pesa ticket)</label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" id="ticketPriceGroup">
                    <label class="form-label" for="ticket_price">Ticket price (KES)</label>
                    <input class="form-control<?= has_field_error('ticket_price') ? ' has-error' : '' ?>" type="number" step="0.01" min="0" id="ticket_price" name="ticket_price" value="<?= e($priceVal) ?>">
                    <?php if ($err = field_errors('ticket_price')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="capacity">Capacity <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                    <input class="form-control" type="number" min="1" id="capacity" name="capacity" value="<?= e($val('capacity')) ?>" placeholder="Leave blank for unlimited">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="starts_at">Starts</label>
                    <input class="form-control<?= has_field_error('starts_at') ? ' has-error' : '' ?>" type="datetime-local" id="starts_at" name="starts_at" value="<?= e($startsVal) ?>" required>
                    <?php if ($err = field_errors('starts_at')): ?><div class="form-error"><?= e($err[0]) ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="ends_at">Ends <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                    <input class="form-control" type="datetime-local" id="ends_at" name="ends_at" value="<?= e($endsVal) ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="venue">Venue <span style="font-weight:400;color:#6b7280;">(optional)</span></label>
                <input class="form-control" type="text" id="venue" name="venue" value="<?= e($val('venue')) ?>">
            </div>

            <div class="form-group js-media-field">
                <label class="form-label" for="image">Event image</label>
                <img class="js-media-preview" src="<?= $isEdit && $event['image_path'] ? e(upload_url($event['image_path'])) : '' ?>" alt="" style="width:160px;height:100px;object-fit:cover;border-radius:8px;margin-bottom:10px;<?= $isEdit && $event['image_path'] ? '' : 'display:none;' ?>">
                <input class="form-control" type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp">
                <input type="hidden" name="picked_media_id" value="">
                <div>
                    <button type="button" class="btn btn-outline btn-sm js-open-media-picker" data-picker-mode="single" style="margin-top:8px;">Choose from Library</button>
                </div>
                <div class="form-hint">JPEG, PNG, or WEBP, up to 5MB. <?= $isEdit ? 'Leave blank to keep the current image.' : '' ?></div>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= (!$isEdit || $event['is_active']) ? 'checked' : '' ?>>
                <label for="is_active">Visible on the public site</label>
            </div>

            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Event' ?></button>
        </form>
    </div>
</div>

<script>
(function () {
    var freeRadio = document.getElementById('typeFree');
    var paidRadio = document.getElementById('typePaid');
    var priceGroup = document.getElementById('ticketPriceGroup');
    function toggle() {
        priceGroup.style.display = paidRadio.checked ? 'block' : 'none';
    }
    freeRadio.addEventListener('change', toggle);
    paidRadio.addEventListener('change', toggle);
    toggle();
})();
</script>
