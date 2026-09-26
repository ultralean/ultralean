<?php $this->extend('layouts/app'); ?>

<?php $this->section('content'); ?>
<section class="hero">
    <p class="muted">Authenticated example</p>
    <h1>Dashboard</h1>
    <p>Welcome, <?= e($user['name'] ?? 'Administrator') ?>. This is a small example of session-based authentication, middleware, database access, views, CSRF protection, and account management.</p>
    <div class="actions">
        <a class="btn" href="<?= e(url('admin.account')) ?>">Account Settings</a>
        <form class="logout" method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="btn secondary" type="submit">Log out</button></form>
    </div>
</section>
<section class="grid">
    <div class="card"><div class="metric"><?= e($elapsed) ?> ms</div><div class="muted">Approx. request time</div></div>
    <div class="card"><div class="metric"><?= e($memory) ?> MB</div><div class="muted">Current PHP memory</div></div>
    <div class="card"><div class="metric">Lazy</div><div class="muted">Components initialize on demand</div></div>
</section>
<?php $this->endSection(); ?>
