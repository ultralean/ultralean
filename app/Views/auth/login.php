<?php $this->extend('layouts/app'); ?>

<?php $this->section('content'); ?>
<section class="hero">
    <h1>Admin login</h1>
    <p>Sign in to open the example administration panel.</p>
    <?php if (!empty($error)): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <form class="form" method="post" action="<?= e(url('login.submit')) ?>">
        <?= csrf_field() ?>
        <div class="field"><label for="username">Username</label><input id="username" name="username" value="admin" autocomplete="username" required></div>
        <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <button class="btn" type="submit">Sign in</button>
    </form>
    <p class="muted">Demo credentials: <strong>admin</strong> / <strong>admin</strong>. Change the password from Account Settings after signing in.</p>
</section>
<?php $this->endSection(); ?>
