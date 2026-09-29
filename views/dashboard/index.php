<div style="width: 100%; max-width: 1000px; margin: 0 auto;">
    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
    <?php endif; ?>

    <!-- Scope Header -->
    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Current Tenant Scope</div>
            <div style="font-size: 1.3rem; font-weight: 700; color: #38bdf8;">
                <?= htmlspecialchars($current_tenant['name'] ?? 'No Organization Selected') ?>
            </div>
            <?php if (!empty($current_tenant['slug'])): ?>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">
                    Slug: <?= htmlspecialchars($current_tenant['slug']) ?> | Plan: <?= strtoupper(htmlspecialchars($current_tenant['plan'])) ?> | 
                    <span style="color: #34d399; font-weight: 600;">Your Role: <?= strtoupper(htmlspecialchars($tenant_role)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 1rem; align-items: center;">
            <a href="/dashboard/export" class="btn" style="text-decoration: none; padding: 0.5rem 0.9rem; font-size: 0.85rem; background: #374151; display: inline-flex; align-items: center; gap: 0.35rem;">
                📥 Export CSV
            </a>

            <?php if (!empty($user_tenants) && count($user_tenants) > 1): ?>
                <form method="GET" action="/tenant/switch" style="display: flex; align-items: center; gap: 0.5rem;">
                    <select name="tenant_id" onchange="this.form.submit()" style="background: #0b0f19; border: 1px solid var(--border-color); color: var(--text-main); padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.9rem; outline: none;">
                        <?php foreach ($user_tenants as $t): ?>
                            <option value="<?= htmlspecialchars($t['id']) ?>" <?= (!empty($current_tenant['id']) && $current_tenant['id'] === $t['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['role']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Security Posture & Health Status Card -->
    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 55px; height: 55px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); border: 2px solid #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; font-weight: 700; color: #10b981;">
                <?= htmlspecialchars($health_status['grade'] ?? 'A') ?>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 1.05rem;">WAF Security Posture Score: <?= (int)$health_status['score'] ?>/100</div>
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem;">All core security primitives, strict session guards, and DoS mitigators active.</div>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <?php foreach ($health_status['checks'] as $check): ?>
                <span style="font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 4px; background: <?= $check['status'] === 'PASS' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(234, 179, 8, 0.15)' ?>; color: <?= $check['status'] === 'PASS' ? '#34d399' : '#facc15' ?>; border: 1px solid <?= $check['status'] === 'PASS' ? '#059669' : '#ca8a04' ?>;">
                    <?= htmlspecialchars($check['name']) ?>: <?= $check['status'] ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Threat Breakdown Matrix -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">TOTAL INTERCEPTIONS</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #ef4444; margin-top: 0.25rem;"><?= (int) $total_threats ?></div>
        </div>
        <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">SQL INJECTIONS</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #f87171; margin-top: 0.25rem;"><?= (int) ($threat_metrics['SQL_INJECTION'] ?? 0) ?></div>
        </div>
        <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">XSS VECTORS</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #fbbf24; margin-top: 0.25rem;"><?= (int) ($threat_metrics['CROSS_SITE_SCRIPTING'] ?? 0) ?></div>
        </div>
        <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">IP BLACKLISTED</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #a78bfa; margin-top: 0.25rem;"><?= (int) ($threat_metrics['IP_BLACKLISTED'] ?? 0) ?></div>
        </div>
        <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">RATE THROTTLED</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #38bdf8; margin-top: 0.25rem;"><?= (int) ($threat_metrics['RATE_LIMIT_EXCEEDED'] ?? 0) ?></div>
        </div>
    </div>

    <!-- Live Threat Telemetry Table -->
    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Live Threat Telemetry (Recent Interceptions)</h3>
        
        <?php if (empty($security_events)): ?>
            <p style="color: var(--text-muted); font-size: 0.9rem;">No threat vectors detected. System operating securely under ShieldLayer WAF.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                            <th style="padding: 0.75rem 0.5rem;">Timestamp</th>
                            <th style="padding: 0.75rem 0.5rem;">Threat Vector</th>
                            <th style="padding: 0.75rem 0.5rem;">Severity</th>
                            <th style="padding: 0.75rem 0.5rem;">Source IP</th>
                            <th style="padding: 0.75rem 0.5rem;">Intercepted Payload</th>
                            <th style="padding: 0.75rem 0.5rem;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($security_events as $event): ?>
                            <tr style="border-bottom: 1px solid #1f2937;">
                                <td style="padding: 0.75rem 0.5rem; color: var(--text-muted); font-size: 0.8rem;"><?= htmlspecialchars($event['created_at']) ?></td>
                                <td style="padding: 0.75rem 0.5rem; font-weight: 600; color: #f87171;"><?= htmlspecialchars($event['threat_type']) ?></td>
                                <td style="padding: 0.75rem 0.5rem;">
                                    <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 0.15rem 0.5rem; border-radius: 4px; text-transform: uppercase; font-size: 0.75rem; font-weight: 600;">
                                        <?= htmlspecialchars($event['severity']) ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; font-family: monospace; color: #9ca3af;"><?= htmlspecialchars($event['source_ip']) ?></td>
                                <td style="padding: 0.75rem 0.5rem; font-family: monospace; color: #fbbf24; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($event['payload_sample']) ?>">
                                    <?= htmlspecialchars($event['payload_sample']) ?>
                                </td>
                                <td style="padding: 0.75rem 0.5rem;">
                                    <span style="color: #34d399; font-weight: 600;"><?= htmlspecialchars($event['action_taken']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tenant Firewall Policy & IP Blacklist -->
    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Tenant Firewall Policy & IP Blacklist</h3>
        
        <?php if ($tenant_role === 'owner' || $tenant_role === 'analyst'): ?>
            <form method="POST" action="/waf/rule/create" style="display: flex; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div style="flex: 1; min-width: 140px;">
                    <select name="rule_type" style="width: 100%; background: #0b0f19; border: 1px solid var(--border-color); color: var(--text-main); padding: 0.65rem; border-radius: 6px; outline: none;">
                        <option value="ip_block">IP Blocklist</option>
                    </select>
                </div>

                <div style="flex: 2; min-width: 200px;">
                    <input type="text" name="pattern" placeholder="e.g. 192.168.1.100 or ::1" required style="width: 100%; background: #0b0f19; border: 1px solid var(--border-color); color: var(--text-main); padding: 0.65rem 1rem; border-radius: 6px; outline: none;">
                </div>

                <button type="submit" class="btn" style="flex: 1; min-width: 140px; padding: 0.65rem;">Deploy Rule</button>
            </form>
        <?php endif; ?>

        <?php if (!empty($waf_rules)): ?>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                        <th style="padding: 0.6rem;">Policy Type</th>
                        <th style="padding: 0.6rem;">Pattern / Target</th>
                        <th style="padding: 0.6rem;">Action</th>
                        <?php if ($tenant_role === 'owner' || $tenant_role === 'analyst'): ?>
                            <th style="padding: 0.6rem; text-align: right;">Operations</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($waf_rules as $r): ?>
                        <tr style="border-bottom: 1px solid #1f2937;">
                            <td style="padding: 0.6rem; color: #38bdf8; font-weight: 500;"><?= strtoupper(htmlspecialchars($r['rule_type'])) ?></td>
                            <td style="padding: 0.6rem; font-family: monospace;"><?= htmlspecialchars($r['pattern']) ?></td>
                            <td style="padding: 0.6rem;"><span style="color: #ef4444; font-weight: 600; text-transform: uppercase;"><?= htmlspecialchars($r['action']) ?></span></td>
                            <?php if ($tenant_role === 'owner' || $tenant_role === 'analyst'): ?>
                                <td style="padding: 0.6rem; text-align: right;">
                                    <a href="/waf/rule/delete?id=<?= urlencode($r['id']) ?>" style="color: #f87171; text-decoration: none; font-size: 0.8rem;" onclick="return confirm('Revoke this firewall rule?');">Revoke</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: var(--text-muted); font-size: 0.85rem;">No active tenant-level rules deployed yet.</p>
        <?php endif; ?>
    </div>

    <!-- Team Access Control (RBAC) Section -->
    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Organization Operators & RBAC</h3>

        <?php if ($tenant_role === 'owner'): ?>
            <form method="POST" action="/team/invite" style="display: flex; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div style="flex: 2; min-width: 200px;">
                    <input type="email" name="email" placeholder="Registered operator work email..." required style="width: 100%; background: #0b0f19; border: 1px solid var(--border-color); color: var(--text-main); padding: 0.65rem 1rem; border-radius: 6px; outline: none;">
                </div>

                <div style="flex: 1; min-width: 130px;">
                    <select name="role" style="width: 100%; background: #0b0f19; border: 1px solid var(--border-color); color: var(--text-main); padding: 0.65rem; border-radius: 6px; outline: none;">
                        <option value="analyst">Analyst</option>
                        <option value="viewer">Viewer</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>

                <button type="submit" class="btn" style="flex: 1; min-width: 130px; padding: 0.65rem;">Assign Seat</button>
            </form>
        <?php endif; ?>

        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                    <th style="padding: 0.6rem;">Operator</th>
                    <th style="padding: 0.6rem;">Email</th>
                    <th style="padding: 0.6rem;">Role</th>
                    <?php if ($tenant_role === 'owner'): ?>
                        <th style="padding: 0.6rem; text-align: right;">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($team_members as $m): ?>
                    <tr style="border-bottom: 1px solid #1f2937;">
                        <td style="padding: 0.6rem; font-weight: 500;"><?= htmlspecialchars($m['name']) ?></td>
                        <td style="padding: 0.6rem; color: var(--text-muted);"><?= htmlspecialchars($m['email']) ?></td>
                        <td style="padding: 0.6rem;">
                            <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid #0284c7; padding: 0.15rem 0.5rem; border-radius: 4px; text-transform: uppercase; font-size: 0.75rem; font-weight: 600;">
                                <?= htmlspecialchars($m['role']) ?>
                            </span>
                        </td>
                        <?php if ($tenant_role === 'owner'): ?>
                            <td style="padding: 0.6rem; text-align: right;">
                                <?php if ($m['email'] !== $user_email): ?>
                                    <a href="/team/remove?user_id=<?= urlencode($m['id']) ?>" style="color: #f87171; text-decoration: none; font-size: 0.8rem;" onclick="return confirm('Revoke operator access?');">Revoke</a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Current</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Identity & Provisioning Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <div class="card" style="max-width: none;">
            <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Operator Context</h3>
            <p style="font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($user_name) ?></p>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;"><?= htmlspecialchars($user_email) ?></p>
            <div style="margin-top: 1rem; display: inline-block; background: rgba(56, 189, 248, 0.15); border: 1px solid #0284c7; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #38bdf8;">
                Role: <?= htmlspecialchars($user_role) ?>
            </div>
        </div>

        <div class="card" style="max-width: none;">
            <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Provision Tenant</h3>
            <form method="POST" action="/tenant/create">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="org_name">Organization Name</label>
                    <input type="text" id="org_name" name="org_name" placeholder="e.g. Acme Cyber Defense" required>
                </div>
                <button type="submit" class="btn" style="padding: 0.6rem;">Deploy Organization</button>
            </form>
        </div>
    </div>
</div>
