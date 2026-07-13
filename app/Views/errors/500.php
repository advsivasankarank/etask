<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Server Error') ?></title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(180deg, #edf9fc 0%, #f8fcfd 100%);
            color: #15313b;
        }
        .card {
            max-width: 560px;
            padding: 36px;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 24px 60px rgba(20, 113, 135, 0.12);
            border: 1px solid rgba(20, 113, 135, 0.08);
            text-align: center;
        }
        .code {
            color: #b42318;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }
        h1 { margin: 12px 0 10px; font-size: 2.4rem; }
        p { color: #607b86; line-height: 1.6; }
        .incident {
            margin: 22px 0 0;
            padding: 14px 16px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #d8e1eb;
            color: #405b66;
            font-size: 0.9rem;
        }
        .incident strong { color: #15313b; }
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 22px;
        }
        .action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            font: inherit;
            cursor: pointer;
        }
        .action-primary { border: 1px solid #0d7987; background: #0d7987; color: #fff; }
        .action-secondary { border: 1px solid #b7c7cf; background: #fff; color: #15313b; }
        .action:focus-visible { outline: 3px solid #0b6b78; outline-offset: 3px; }
        .support { margin: 18px 0 0; font-size: 0.88rem; }
        @media (max-width: 520px) {
            .card { padding: 28px 20px; }
            .actions { display: grid; justify-content: stretch; }
            .action { width: 100%; box-sizing: border-box; }
        }
    </style>
</head>
<body>
    <main class="card" role="alert" aria-labelledby="errorTitle">
        <div class="code">500</div>
        <h1 id="errorTitle">Something Went Wrong</h1>
        <p>The application encountered an unexpected error. Your work on the previous screen has not been confirmed.</p>
        <div class="incident">
            <div><strong>Support reference:</strong> <?= e($incidentReference ?? 'Unavailable') ?></div>
            <div><strong>Time:</strong> <?= e(isset($occurredAt) ? date('d M Y, h:i:s A T', strtotime((string) $occurredAt)) : date('d M Y, h:i:s A T')) ?></div>
        </div>
        <div class="actions">
            <?php if (!empty($retryPath)): ?>
                <a class="action action-primary" href="<?= e($retryPath) ?>">Try Again</a>
            <?php endif; ?>
            <button class="action action-secondary" type="button" id="returnPrevious">Previous Page</button>
            <a class="action action-secondary" href="<?= e(url('/dashboard')) ?>">Dashboard</a>
        </div>
        <p class="support">If the problem continues, contact your system administrator and provide the support reference above.</p>
    </main>
    <script>
        document.getElementById('returnPrevious').addEventListener('click', function() {
            if (window.history.length > 1) window.history.back();
        });
    </script>
</body>
</html>
