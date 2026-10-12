<?php

declare(strict_types=1);

// The module leans on the core (Symfony components, Thelia models): the autoloader of the shop the module is
// installed in provides them. THELIA_VENDOR_AUTOLOAD points at another vendor/autoload.php (module checked out
// outside the shop, or linked into it by symlink).
$candidates = array_filter([
    getenv('THELIA_VENDOR_AUTOLOAD') ?: null,
    // local/modules/AdminOrderCreation
    __DIR__ . '/../../../../vendor/autoload.php',
    // vendor/thelia/modules/AdminOrderCreation
    __DIR__ . '/../../../../../vendor/autoload.php',
    // composer install at the module root
    dirname(__DIR__) . '/vendor/autoload.php',
]);

$autoload = null;

foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $autoload = $candidate;
        break;
    }
}

if ($autoload === null) {
    fwrite(\STDERR, "No vendor/autoload.php found: install the module in a shop, or set THELIA_VENDOR_AUTOLOAD.\n");
    exit(1);
}

// The shop defines the path constants in its root bootstrap.php before loading the vendor: do the same.
$shopBootstrap = dirname($autoload, 2) . '/bootstrap.php';

if (is_file($shopBootstrap)) {
    require $shopBootstrap;
}

require $autoload;

// The module and its tests, when the shop autoloader does not map them already. Longest prefix first.
spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'AdminOrderCreation\\Tests\\' => __DIR__ . '/',
        'AdminOrderCreation\\' => dirname(__DIR__) . '/',
    ];

    foreach ($prefixes as $prefix => $directory) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $file = $directory . str_replace('\\', '/', substr($class, \strlen($prefix))) . '.php';

        if (is_file($file)) {
            require $file;
        }

        return;
    }
});

// Unit tests build Thelia models without booting the kernel: the Propel classes it generated (Base classes, table
// maps) live in the shop, by default under var/propel/test/model, or in PROPEL_MODEL_DIR.
$modelDirectory = getenv('PROPEL_MODEL_DIR') ?: dirname($autoload, 2) . '/var/propel/test/model';

if (is_dir($modelDirectory)) {
    spl_autoload_register(static function (string $class) use ($modelDirectory): void {
        $file = $modelDirectory . '/' . str_replace('\\', '/', $class) . '.php';

        if (is_file($file)) {
            require $file;
        }
    });
}
