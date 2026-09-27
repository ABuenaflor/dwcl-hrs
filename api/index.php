<?php

/*
| Vercel entry point (vercel-php runtime).
|
| Vercel's filesystem is read-only except /tmp, so storage and the SQLite
| demo database live there. Each serverless instance builds and seeds its
| own database on first boot; data resets whenever the instance is recycled.
| For a persistent deployment, point DB_* at a hosted MySQL instead.
*/

$storage = '/tmp/storage';
foreach (['app/private', 'framework/views', 'framework/cache/data', 'logs'] as $dir) {
    is_dir("{$storage}/{$dir}") || mkdir("{$storage}/{$dir}", 0777, true);
}

require __DIR__.'/../vendor/autoload.php';

$boot = function () use ($storage) {
    $app = require __DIR__.'/../bootstrap/app.php';
    $app->useStoragePath($storage);

    return $app;
};

$database = getenv('DB_DATABASE') ?: '/tmp/dwcl.sqlite';
if (getenv('DB_CONNECTION') === 'sqlite' && ! file_exists($database.'.ready')) {
    $lock = fopen('/tmp/dwcl.lock', 'c');
    flock($lock, LOCK_EX);

    if (! file_exists($database.'.ready')) {
        touch($database);
        $boot()->make(Illuminate\Contracts\Console\Kernel::class)
            ->call('migrate', ['--force' => true, '--seed' => true]);
        touch($database.'.ready');
    }

    flock($lock, LOCK_UN);
}

$boot()->handleRequest(Illuminate\Http\Request::capture());
