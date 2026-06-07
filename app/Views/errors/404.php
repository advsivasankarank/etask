<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Page Not Found') ?></title>
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
            color: #1499a8;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }
        h1 { margin: 12px 0 10px; font-size: 2.4rem; }
        p { color: #607b86; line-height: 1.6; }
        a {
            display: inline-block;
            margin-top: 16px;
            padding: 12px 18px;
            border-radius: 14px;
            background: linear-gradient(135deg, #1499a8, #0d7987);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <section class="card">
        <div class="code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you requested could not be found or may have been moved.</p>
        <a href="<?= e(url('/dashboard')) ?>">Return to Dashboard</a>
    </section>
</body>
</html>
