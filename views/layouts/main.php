<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'ShieldLayer') ?></title>
    <style>
        :root {
            --bg-color: #0b0f19;
            --surface-color: #111827;
            --border-color: #1f2937;
            --accent-color: #0284c7;
            --accent-hover: #0369a1;
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
            --danger: #ef4444;
            --success: #10b981;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); display: flex; flex-direction: column; min-height: 100vh; }
        nav { background: var(--surface-color); border-bottom: 1px solid var(--border-color); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-weight: 700; font-size: 1.25rem; letter-spacing: 0.05em; color: #38bdf8; display: flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        .nav-links a { color: var(--text-muted); text-decoration: none; margin-left: 1.5rem; font-size: 0.95rem; transition: color 0.2s; }
        .nav-links a:hover { color: var(--text-main); }
        .container { flex: 1; display: flex; justify-content: center; align-items: center; padding: 2rem; }
        .card { background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 2rem; width: 100%; max-width: 420px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5); }
        h1, h2 { margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600; text-align: center; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: 0.85rem; margin-bottom: 0.4rem; color: var(--text-muted); }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; background: #0b0f19; border: 1px solid var(--border-color); color: var(--text-main);
            padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.95rem; outline: none; transition: border-color 0.2s;
        }
        input:focus { border-color: var(--accent-color); }
        button.btn {
            width: 100%; background: var(--accent-color); color: #fff; border: none; padding: 0.75rem;
            border-radius: 6px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s;
        }
        button.btn:hover { background: var(--accent-hover); }
        .alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.25rem; font-size: 0.9rem; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: #fca5a5; }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid var(--success); color: #6ee7b7; }
        .form-footer { margin-top: 1.5rem; text-align: center; font-size: 0.85rem; color: var(--text-muted); }
        .form-footer a { color: #38bdf8; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <a href="/shieldlayer/public/" class="logo">🛡️ ShieldLayer</a>
        <div class="nav-links">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/shieldlayer/public/dashboard">Dashboard</a>
                <a href="/shieldlayer/public/logout">Logout</a>
            <?php else: ?>
                <a href="/shieldlayer/public/login">Sign In</a>
                <a href="/shieldlayer/public/register">Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container">
        <?= $content ?>
    </main>
</body>
</html>
