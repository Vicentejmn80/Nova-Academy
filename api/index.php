<?php

declare(strict_types=1);

/**
 * Vercel serverless entry point for Laravel.
 * Delegates to the standard public front controller.
 */
$root = dirname(__DIR__);

chdir($root . '/public');

if (! isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] === '') {
    $_SERVER['DOCUMENT_ROOT'] = $root . '/public';
}

$_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

require $root . '/public/index.php';
