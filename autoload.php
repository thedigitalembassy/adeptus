<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/*
 * Register custom autoloader if composer autoloader is not installed
 */
if (!class_exists('\\TDE\\Adeptus\\AlertManager')) {
    spl_autoload_register(function ($class) {
        $prefix   = 'TDE\\Adeptus\\';
        $base_dir = __DIR__ . '/src/';

        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file           = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });
}

/*
 * Register custom autoloader for PSR interfaces if composer autoloader is not installed
 */
if (!interface_exists('\\Psr\\Log\\LoggerAwareInterface')) {
    spl_autoload_register(function ($class) {
        $prefix   = 'Psr\\Log\\';
        $base_dir = __DIR__ . '/lib/psr/log/Psr/Log/';

        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file           = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });
}