<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ultralean - a tiny, fast, plain PHP application foundation.">
    <title><?= e($title ?? 'ultralean') ?></title>
    <?= $this->yield('head') ?>
    <style>
        :root{color-scheme:light dark}*{box-sizing:border-box}body{margin:0;font:16px/1.6 system-ui,-apple-system,Segoe UI,sans-serif;background:#0b1120;color:#e2e8f0}a{color:#7dd3fc;text-decoration:none}a:hover{text-decoration:underline}.wrap{max-width:980px;margin:auto;padding:0 20px}.nav{border-bottom:1px solid #1e293b}.navin{display:flex;align-items:center;justify-content:space-between;min-height:64px}.brand{font-weight:800;color:#fff}.links{display:flex;gap:18px;align-items:center}.hero{padding:72px 0 44px}.hero h1{font-size:clamp(42px,8vw,76px);line-height:1;margin:0 0 18px;letter-spacing:-.05em}.hero p{max-width:700px;color:#94a3b8;font-size:19px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px}.card{border:1px solid #1e293b;border-radius:14px;padding:20px;background:#111827}.metric{font-size:30px;font-weight:800}.muted{color:#94a3b8}.btn{display:inline-block;padding:10px 15px;border-radius:9px;background:#38bdf8;color:#082f49;font-weight:700;border:0;cursor:pointer}.btn.secondary{background:#1e293b;color:#e2e8f0}.actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:24px}.form{max-width:520px}.field{margin:0 0 16px}.field label{display:block;margin-bottom:6px;font-weight:600}.field input{width:100%;padding:11px 12px;border:1px solid #334155;border-radius:8px;background:#0f172a;color:#e2e8f0}.alert{padding:12px 14px;border-radius:9px;background:#422006;color:#fed7aa;margin-bottom:18px}.success{background:#052e16;color:#bbf7d0}.footer{border-top:1px solid #1e293b;margin-top:60px;padding:28px 0;color:#64748b;font-size:14px}.logout{display:inline}.logout button{background:#1e293b;color:#e2e8f0;border:1px solid #334155;padding:10px 15px;border-radius:9px;font:inherit;font-weight:700;cursor:pointer}.logout button:hover{background:#334155}.table{width:100%;border-collapse:collapse}.table th,.table td{text-align:left;padding:10px;border-bottom:1px solid #1e293b}@media(max-width:600px){.links{gap:10px;font-size:14px}.hero{padding-top:48px}}
    </style>
</head>
<body>
<header class="nav"><div class="wrap navin">
    <a class="brand" href="/">ultralean</a>
    <nav class="links">
        <a href="<?= e(url('home')) ?>">Home</a>
        <a href="<?= e(url('about')) ?>">About</a>
        <a href="<?= e(url('login')) ?>">Admin</a>
    </nav>
</div></header>
<main class="wrap">
    <?= $this->yield('content') ?>
</main>
<footer class="footer"><div class="wrap">ultralean · plain PHP · lazy loading · minimal overhead</div></footer>
</body>
</html>
