<div class="auth-card">
    <div class="auth-card__brand">
        <span class="dot"></span><strong>MASCARDI LIFESTYLE</strong>
    </div>
    <h1>Admin Sign In</h1>
    <p class="sub">Manage pillars, partners, shop, and events</p>

    <?php if ($error = field_errors('email')): ?>
        <div class="alert alert-error"><?= e($error[0]) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= admin_url('auth', 'attempt') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label class="form-label" for="email">Email address</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= old('email') ?>" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Sign In</button>
    </form>

    <div class="auth-card__meta">Mascardi Lifestyle &middot; Invitation-only ecosystem</div>
</div>
