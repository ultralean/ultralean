<?php $this->extend('layouts/app'); ?>

<?php $this->section('content'); ?>
<section class="hero">
    <p class="muted">A tiny PHP application foundation</p>
    <h1>Build with less.</h1>
    <p>ultralean is a small plain-PHP 8+ application foundation focused on low overhead, lazy loading, readable code, and keeping raw PHP and PDO close at hand.</p>
    <div class="actions">
        <a class="btn" href="<?= e(url('about')) ?>">Learn about ultralean</a>
        <a class="btn secondary" href="https://github.com/ultralean/ultralean">GitHub</a>
        <a class="btn secondary" href="<?= e(url('login')) ?>">Admin panel</a>
    </div>
</section>

<section>
    <h2>Current request</h2>
    <div class="grid">
        <div class="card"><div class="metric"><?= e($elapsed) ?> ms</div><div class="muted">Approx. PHP request time</div></div>
        <div class="card"><div class="metric"><?= e($memory) ?> MB</div><div class="muted">Current PHP memory</div></div>
        <div class="card"><div class="metric">PHP <?= e($php) ?></div><div class="muted">Runtime</div></div>
        <div class="card"><div class="metric">Lazy</div><div class="muted">Classes load only when needed</div></div>
    </div>
    <p class="muted">These figures are simple runtime measurements for this sample request, not a benchmark or a comparison with another PHP package.</p>
</section>
<?php $this->endSection(); ?>
