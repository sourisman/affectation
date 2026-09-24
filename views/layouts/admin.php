<?php

declare(strict_types=1);

/**
 * Layout d'administration — hérite de la coquille de la console
 * avec une navigation marquée « administration ».
 *
 * @var string $content
 * @var array<string, mixed> $meta
 */

$meta = array_merge([
    'title'  => 'Administration — ' . site_name(),
    'robots' => 'noindex, nofollow',
], $meta ?? []);

$scope = 'admin';

require BASE_PATH . '/views/layouts/console.php';
