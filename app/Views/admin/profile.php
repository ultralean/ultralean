<?php $this->extend('layouts/app'); ?>

<?php $this->section('content'); ?>
<section class="hero">
    <h1>Account Settings</h1>
    <p>Update the administrator name, username, email address, and password.</p>
    <?php if (!empty($success)): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <form class="form" method="post" action="<?= e(url('admin.account.update')) ?>">
        <?= csrf_field() ?>
        <div class="field"><label for="name">Name</label><input id="name" name="name" value="<?= e($user['name'] ?? '') ?>" required></div>
        <div class="field"><label for="username">Username</label><input id="username" name="username" value="<?= e($user['username'] ?? '') ?>" autocomplete="username" required></div>
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="<?= e($user['email'] ?? '') ?>" autocomplete="email" required></div>
        <div class="field"><label for="password">New password</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password"><small class="muted">Leave blank to keep the current password.</small></div>
        <div class="field"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password"></div>
        <button class="btn" type="submit">Save changes</button>
        <a class="btn secondary" href="<?= e(url('admin.home')) ?>">Back to dashboard</a>
    </form>
</section>
<?php $this->endSection(); ?>
