<?php $this->extend('layouts/app'); ?>

<?php $this->section('content'); ?>
<section class="hero">
    <h1>About ultralean</h1>
    <p>ultralean is designed as a small, reusable PHP foundation rather than a large application stack. The core keeps routing, requests, responses, views, sessions, authentication, validation, CSRF protection, PDO database access, migrations, middleware, logging, and error handling available without forcing everything to initialize on every request.</p>
</section>
<section class="grid">
    <div class="card"><h3>Plain PHP</h3><p class="muted">No ORM, template compiler, dependency container, or unnecessary abstraction.</p></div>
    <div class="card"><h3>Lazy by default</h3><p class="muted">Controllers, middleware, database connections, sessions, authentication, and views are used only when required.</p></div>
    <div class="card"><h3>PDO first</h3><p class="muted">Use the small database helpers or drop directly down to PDO whenever raw SQL is the clearest solution.</p></div>
    <div class="card"><h3>UTC storage</h3><p class="muted">Persist timestamps in UTC and convert them explicitly only when a display or business operation needs another timezone.</p></div>
</section>
<section class="hero" style="padding-bottom:20px">
    <h2>Further details</h2>
    <p>Documentation, source code, examples, and the latest project information are maintained on GitHub.</p>
    <p><a class="btn" href="https://github.com/ultralean/ultralean">github.com/ultralean/ultralean</a></p>
</section>
<?php $this->endSection(); ?>
