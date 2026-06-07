<?php $appName = config('app.name', 'Compliance Management System'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'Login') . ' | ' . $appName) ?></title>
    <style>
        :root {
            --bg: #eff6ff;
            --card: rgba(255,255,255,0.95);
            --text: #10233d;
            --muted: #5d6b82;
            --primary: #0f766e;
            --primary-dark: #115e59;
            --border: #d7e2ec;
            --danger: #b42318;
            --success: #047857;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(15,118,110,0.15), transparent 35%),
                radial-gradient(circle at bottom right, rgba(16,35,61,0.12), transparent 35%),
                linear-gradient(135deg, #ecfeff 0%, #eff6ff 45%, #f8fafc 100%);
        }
        .auth-wrap {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: var(--card);
            border: 1px solid rgba(255,255,255,0.7);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(16, 35, 61, 0.14);
            backdrop-filter: blur(14px);
        }
        .auth-copy {
            padding: 48px;
            background: linear-gradient(160deg, #102d24 0%, #17624d 60%, #16352e 100%);
            color: #e2e8f0;
        }
        .auth-form {
            padding: 48px;
        }
        .eyebrow {
            color: #ffd5c3;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.75rem;
            font-weight: 700;
        }
        h1 {
            margin: 12px 0 18px;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.05;
        }
        .copy-text, .hint {
            color: #cbd5e1;
            line-height: 1.7;
        }
        .badge-grid {
            display: grid;
            gap: 12px;
            margin-top: 28px;
        }
        .badge {
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.08);
        }
        form { display: grid; gap: 16px; }
        label {
            display: grid;
            gap: 8px;
            font-size: 0.95rem;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid var(--border);
            border-radius: 14px;
            font-size: 1rem;
        }
        input:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.12);
        }
        .button {
            border: 0;
            padding: 14px 18px;
            border-radius: 14px;
            background: var(--primary);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .button:hover { background: var(--primary-dark); }
        .alert {
            padding: 14px 16px;
            border-radius: 14px;
            font-size: 0.95rem;
        }
        .alert-error {
            background: #fef3f2;
            color: var(--danger);
            border: 1px solid #fecdca;
        }
        .alert-success {
            background: #ecfdf3;
            color: var(--success);
            border: 1px solid #abefc6;
        }
        @media (max-width: 860px) {
            .auth-wrap { grid-template-columns: 1fr; }
            .auth-copy, .auth-form { padding: 28px; }
        }
    </style>
</head>
<body>
    <?= $content ?>
</body>
</html>
