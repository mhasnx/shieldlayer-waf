<?php
use ShieldLayer\Support\Security;
Security::startSession();
$isLoggedIn = !empty($_SESSION['user_id']);
$userEmail = $_SESSION['user_email'] ?? 'Account';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'ShieldLayer — Website Security & Protection') ?></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      background-color: #090d16;
      color: #e2e8f0;
      line-height: 1.5;
    }
    a { color: inherit; text-decoration: none; }
    
    /* Layout Framework */
    .app-wrapper {
      display: flex;
      min-height: 100vh;
    }
    
    /* Sidebar */
    .app-sidebar {
      width: 250px;
      background: #0f172a;
      border-right: 1px solid #1e293b;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
    }
    .brand-header {
      padding: 1.25rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      border-bottom: 1px solid #1e293b;
    }
    .brand-logo {
      font-size: 1.25rem;
      font-weight: 700;
      color: #38bdf8;
      letter-spacing: -0.02em;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .nav-menu {
      padding: 1.25rem 0.75rem;
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
      flex-grow: 1;
    }
    .nav-label {
      font-size: 0.7rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 0.5rem 0.75rem 0.25rem;
    }
    .nav-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.6rem 0.75rem;
      border-radius: 6px;
      font-size: 0.875rem;
      color: #94a3b8;
      font-weight: 500;
      transition: background 0.15s, color 0.15s;
    }
    .nav-item:hover, .nav-item.active {
      background: #1e293b;
      color: #38bdf8;
    }
    
    /* Main Area */
    .app-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-width: 0;
    }
    .top-navbar {
      height: 60px;
      background: #0f172a;
      border-bottom: 1px solid #1e293b;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1.75rem;
    }
    .main-body {
      flex: 1;
      padding: 1.75rem;
      max-width: 1400px;
      width: 100%;
      margin: 0 auto;
    }

    /* Auth Clean Container (Non-Logged in) */
    .auth-wrapper {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background: #0b0f19;
      padding: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .app-wrapper { flex-direction: column; }
      .app-sidebar { width: 100%; border-right: none; border-bottom: 1px solid #1e293b; }
      .main-body { padding: 1rem; }
    }
  </style>
</head>
<body>

<?php if ($isLoggedIn): ?>
  <div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="app-sidebar">
      <div class="brand-header">
        <a href="/dashboard" class="brand-logo">
          <span>🛡️</span> ShieldLayer
        </a>
      </div>
      <nav class="nav-menu">
        <span class="nav-label">Security Command</span>
        <a href="/dashboard" class="nav-item active">
          <span>📊</span> Overview
        </a>
        <a href="#traffic-protection" class="nav-item">
          <span>🛡️</span> Website Protection
        </a>
        <a href="#live-activity" class="nav-item">
          <span>⚡</span> Live Activity
        </a>

        <span class="nav-label" style="margin-top: 1rem;">Workspace</span>
        <a href="#team-members" class="nav-item">
          <span>👥</span> Team Members
        </a>
        <a href="#organization-settings" class="nav-item">
          <span>⚙️</span> Organization Settings
        </a>
      </nav>

      <div style="padding: 1rem; border-top: 1px solid #1e293b; font-size: 0.8rem; color: #64748b;">
        <div style="color: #cbd5e1; font-weight: 600;"><?= htmlspecialchars($userEmail) ?></div>
        <div style="color: #10b981; font-size: 0.75rem; margin-top: 0.15rem;">● System Online</div>
      </div>
    </aside>

    <!-- Main Container -->
    <div class="app-main">
      <header class="top-navbar">
        <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.875rem;">
          <span style="color: #64748b;">Active Workspace:</span>
          <span style="background: #1e293b; padding: 0.25rem 0.65rem; border-radius: 6px; font-weight: 600; color: #38bdf8; border: 1px solid #334155;">
            Default Organization
          </span>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
          <a href="/logout" style="background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
            Sign Out
          </a>
        </div>
      </header>

      <main class="main-body">
        <?= $content ?>
      </main>
    </div>
  </div>

<?php else: ?>
  <!-- Public / Auth Screens -->
  <div class="auth-wrapper">
    <?= $content ?>
  </div>
<?php endif; ?>

</body>
</html>
