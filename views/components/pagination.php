<?php

declare(strict_types=1);

/**
 * Pagination accessible, conservant les filtres courants dans l'URL.
 *
 * @var array{page:int,perPage:int,offset:int,pages:int,total:int} $pagination
 * @var array<string, mixed> $filters
 */

$page = $pagination['page'];
$pages = $pagination['pages'];

/** Construit une URL en conservant les filtres actifs. */
$pageUrl = static function (int $target) use ($filters): string {
    $query = array_filter(
        array_merge($filters, ['page' => $target]),
        static fn ($value): bool => $value !== '' && $value !== null && $value !== false,
    );

    return '?' . http_build_query($query);
};

$window = 2;
$start = max(1, $page - $window);
$end = min($pages, $page + $window);
?>
<div class="pagination">
    <span>
        <?php if ($pagination['total'] === 0): ?>
            Aucun résultat
        <?php else: ?>
            Résultats <?= $pagination['offset'] + 1 ?> – <?= min($pagination['offset'] + $pagination['perPage'], $pagination['total']) ?>
            sur <strong><?= number_format($pagination['total'], 0, ',', ' ') ?></strong>
        <?php endif; ?>
    </span>

    <?php if ($pages > 1): ?>
        <nav class="pagination__pages" aria-label="Pagination">
            <?php if ($page > 1): ?>
                <a href="<?= e($pageUrl($page - 1)) ?>" aria-label="Page précédente"><?= icon('chevron-left', 15) ?></a>
            <?php endif; ?>

            <?php if ($start > 1): ?>
                <a href="<?= e($pageUrl(1)) ?>">1</a>
                <?php if ($start > 2): ?><span aria-hidden="true">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $page): ?>
                    <span aria-current="page"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= e($pageUrl($i)) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $pages): ?>
                <?php if ($end < $pages - 1): ?><span aria-hidden="true">…</span><?php endif; ?>
                <a href="<?= e($pageUrl($pages)) ?>"><?= $pages ?></a>
            <?php endif; ?>

            <?php if ($page < $pages): ?>
                <a href="<?= e($pageUrl($page + 1)) ?>" aria-label="Page suivante"><?= icon('chevron-right', 15) ?></a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>
