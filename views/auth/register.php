<div class="card">
    <h2>New Operator</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/register">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="form-group">
            <label for="name">Operator Full Name</label>
            <input type="text" id="name" name="name" required autocomplete="name">
        </div>

        <div class="form-group">
            <label for="email">Work Email</label>
            <input type="email" id="email" name="email" required autocomplete="email">
        </div>

        <div class="form-group">
            <label for="password">Strong Password (8+ chars)</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn">Establish Identity</button>
    </form>

    <div class="form-footer">
        Existing operator? <a href="/login">Sign in</a>
    </div>
</div>
