<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Document Workspace</div>
            <h3 style="margin:0 0 6px;"><?= e($document['document_name']) ?></h3>
            <div class="subtle"><?= e($document['client_name']) ?> | <?= e($document['linked_module']) ?> | <?= e($document['document_category']) ?></div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button">Download</a>
            <?php if ($previewable): ?>
                <a href="<?= e(url('/documents/' . $document['id'] . '/preview')) ?>" class="button button-secondary" target="_blank" rel="noopener">Preview</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>Current File</strong>
            <div style="margin-top:8px;"><?= e($document['latest_file_name'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>Current Version</strong>
            <div style="margin-top:8px;">V<?= e((string) ($document['current_version_no'] ?? 1)) ?></div>
        </div>
        <div class="metric">
            <strong>MIME Type</strong>
            <div style="margin-top:8px;"><?= e($document['mime_type'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>Uploaded</strong>
            <div style="margin-top:8px;"><?= e($document['uploaded_at'] ?: '-') ?></div>
        </div>
    </div>

    <?php if ($previewable): ?>
        <div class="panel" style="box-shadow:none;margin-top:18px;background:#fff;">
            <h4 style="margin-top:0;">Preview</h4>
            <?php if (str_starts_with((string) ($document['mime_type'] ?? ''), 'image/')): ?>
                <img src="<?= e(url('/documents/' . $document['id'] . '/preview')) ?>" alt="<?= e($document['document_name']) ?>" style="max-width:100%;border-radius:14px;border:1px solid #d8e1eb;">
            <?php else: ?>
                <iframe src="<?= e(url('/documents/' . $document['id'] . '/preview')) ?>" title="<?= e($document['document_name']) ?>" style="width:100%;min-height:640px;border:1px solid #d8e1eb;border-radius:14px;"></iframe>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($replaceAllowed): ?>
        <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Replace Current Version</h4>
            <form method="post" enctype="multipart/form-data" action="<?= e(url('/documents/replace')) ?>" style="display:grid;gap:10px;">
                <?= \App\Core\Csrf::inputField() ?>
                <input type="hidden" name="document_id" value="<?= e((string) $document['id']) ?>">
                <input type="file" name="replacement_file" required style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                <input type="text" name="change_note" placeholder="Change note (optional)" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                <button type="submit" class="button">Upload Replacement</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6fafb);">
        <h4 style="margin-top:0;">Version History</h4>
        <?php if (($versions ?? []) === []): ?>
            <p class="subtle">No version history is available.</p>
        <?php else: ?>
            <div style="overflow:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>File</th>
                            <th>Uploaded At</th>
                            <th>Change Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($versions as $version): ?>
                            <tr>
                                <td>V<?= e((string) $version['version_no']) ?></td>
                                <td><?= e($version['file_name']) ?></td>
                                <td><?= e($version['uploaded_at']) ?></td>
                                <td><?= e($version['change_note'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
