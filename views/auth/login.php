<?php
use ShieldLayer\Support\Security;
$token = Security::csrfToken();
$displayError = $error ?? $flash_error ?? null;
$displaySuccess = $success ?? $flash_success ?? null;
?>
<div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem;">
    <div style="width: 100%; max-width: 420px; background: #0f172a; border: 1px solid #1e293b; border-radius: 12px; padding: 2.25rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; background: rgba(2, 132, 199, 0.15); border: 1px solid rgba(2, 132, 199, 0.3); border-radius: 10px; margin-bottom: 1rem; font-size: 1.25rem;">
                🛡️
            </div>
            <h1 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #f8fafc; letter-spacing: -0.025em;">Welcome to ShieldLayer</h1>
            <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #94a3b8;">Protect and monitor your website from one place.</p>
        </div>

        <!-- Flash Alerts -->
        <?php if (!empty($displayError)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>⚠️</span>
                <span><?= htmlspecialchars($displayError) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($displaySuccess)): ?>
            <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.25); color: #4ade80; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>✅</span>
                <span><?= htmlspecialchars($displaySuccess) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="/login" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <!-- CsrfMiddleware expects _csrf_token -->
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">

            <div>
                <label for="email" style="display: block; font-size: 0.85rem; font-weight: 500; color: #cbd5e1; margin-bottom: 0.4rem;">Email address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="name@company.com" 
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    style="width: 100%; box-sizing: border-box; background: #0b0f19; border: 1px solid #334155; border-radius: 6px; padding: 0.65rem 0.85rem; color: #f8fafc; font-size: 0.9rem; outline: none;"
                >
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label for="password" style="font-size: 0.85rem; font-weight: 500; color: #cbd5e1;">Password</label>
                    <a href="javascript:void(0)" onclick="alert('Password reset instructions will be sent to your email.')" style="font-size: 0.75rem; color: #38bdf8; text-decoration: none;">Forgot password?</a>
                </div>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="••••••••••••" 
                        style="width: 100%; box-sizing: border-box; background: #0b0f19; border: 1px solid #334155; border-radius: 6px; padding: 0.65rem 2.5rem 0.65rem 0.85rem; color: #f8fafc; font-size: 0.9rem; outline: none;"
                    >
                    <button 
                        type="button" 
                        onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password'; this.innerText = p.type === 'password' ? '👁️' : '🔒';" 
                        style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #94a3b8; font-size: 0.9rem; padding: 0.25rem;">
                        👁️
                    </button>
                </div>
            </div>

            <button 
                type="submit" 
                style="width: 100%; background: #0284c7; color: #ffffff; border: none; border-radius: 6px; padding: 0.75rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; margin-top: 0.5rem;">
                Sign In
            </button>
        </form>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid #1e293b; font-size: 0.85rem; color: #94a3b8;">
            Don't have an account? 
            <a href="/register" style="color: #38bdf8; text-decoration: none; font-weight: 500;">Create account</a>
        </div>

    </div>
</div>
