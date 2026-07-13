<form method="get" action="<?= e(url($path)) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Client / SO / PSO / Invoice / Title">
        <select name="reminder_type">
            <option value="">All Reminder Types</option>
            <?php foreach (($options['reminder_types'] ?? []) as $type): ?>
                <option value="<?= e($type) ?>" <?= ($filters['reminder_type'] ?? '') === $type ? 'selected' : '' ?>><?= e(label_case($type)) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">All Statuses</option>
            <?php foreach (['PENDING', 'SENT', 'OVERDUE', 'DONE', 'SKIPPED', 'OPEN_ONLY'] as $status): ?>
                <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(label_case($status)) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
        <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
        <button type="submit" class="button">Apply Filters</button>
    </div>
</form>
