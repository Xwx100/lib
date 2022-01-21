<?php

try {
    require __DIR__ . '/../vendor/autoload.php';

    $app = new Illuminate\Foundation\Application(
        $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
    );

    $app->singleton(Illuminate\Contracts\Http\Kernel::class, \Illuminate\Foundation\Http\Kernel::class);

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    \Illuminate\Support\Facades\Log::info('11');
    \Lib\Helper::log();
} catch (\Error|\Exception $all) {
    var_dump($all->getMessage());
}
