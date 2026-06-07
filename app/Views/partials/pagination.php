<?php
declare(strict_types=1);

$pagination = is_array($pagination ?? null) ? $pagination : null;
if ($pagination === null || (int) ($pagination['total_pages'] ?? 1) <= 1) {
    return;
}

$currentPage = max(1, (int) ($pagination['page'] ?? 1));
$totalPages = max(1, (int) ($pagination['total_pages'] ?? 1));
$perPage = max(1, (int) ($pagination['per_page'] ?? 1));
$total = max(0, (int) ($pagination['total'] ?? 0));
$start = $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1;
$end = min($total, $currentPage * $perPage);
$windowStart = max(1, $currentPage - 2);
$windowEnd = min($totalPages, $currentPage + 2);
?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px;padding-top:10px;border-top:1px solid rgba(15,76,92,0.12);">
    <div class="subtle">Showing <?= e((string) $start) ?>-<?= e((string) $end) ?> of <?= e((string) $total) ?></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <?php if ($currentPage > 1): ?>
            <a href="<?= e(pagination_url($path ?? '/', $query ?? [], $currentPage - 1)) ?>" class="button button-secondary">Previous</a>
        <?php endif; ?>
        <?php for ($pageNo = $windowStart; $pageNo <= $windowEnd; $pageNo++): ?>
            <a href="<?= e(pagination_url($path ?? '/', $query ?? [], $pageNo)) ?>" class="button <?= $pageNo === $currentPage ? '' : 'button-secondary' ?>"><?= e((string) $pageNo) ?></a>
        <?php endfor; ?>
        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= e(pagination_url($path ?? '/', $query ?? [], $currentPage + 1)) ?>" class="button button-secondary">Next</a>
        <?php endif; ?>
    </div>
</div>
