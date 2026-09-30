<!-- Live Status & Security Score Header -->
<!-- Websites & Ownership Verification Section (Mobile Responsive) -->
<div style="background: #0f172a; border: 1px solid #1e293b; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid #1e293b; padding-bottom: 1rem; margin-bottom: 1.25rem;">
        <div>
            <h3 style="margin: 0; font-size: 1.2rem; color: #f8fafc; font-weight: 700;">Protected Websites</h3>
            <p style="margin: 0.25rem 0 0 0; color: #94a3b8; font-size: 0.85rem;">Add your web domain to enable continuous security analysis and traffic protection.</p>
        </div>
        <form method="POST" action="/website/create" style="display: flex; gap: 0.5rem; flex-wrap: wrap; width: 100%; max-width: 480px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="url" name="url" placeholder="https://yourdomain.com" required style="flex: 1; min-width: 200px; padding: 0.65rem 0.85rem; background: #0b0f19; border: 1px solid #334155; border-radius: 6px; color: #f8fafc; font-size: 0.85rem; outline: none;">
            <button type="submit" style="padding: 0.65rem 1.25rem; background: #2563eb; color: #ffffff; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap;">+ Add Website</button>
        </form>
    </div>

    <?php if (empty($websites)): ?>
        <div style="text-align: center; padding: 2.5rem 1rem; color: #64748b; background: #0b0f19; border-radius: 8px; border: 1px dashed #1e293b;">
            <p style="margin: 0 0 0.5rem 0; font-size: 1rem; color: #94a3b8; font-weight: 500;">No websites connected yet.</p>
            <span style="font-size: 0.8rem;">Enter your website URL above to begin ownership verification and security monitoring.</span>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem;">
            <?php foreach ($websites as $site): ?>
                <div style="background: #0b0f19; border: 1px solid #1e293b; border-radius: 8px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <span style="font-weight: 700; color: #38bdf8; font-size: 1.05rem; word-break: break-all;"><?= htmlspecialchars($site['domain']) ?></span>
                            <?php if ($site['status'] === 'PROTECTED'): ?>
                                <span style="background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 9999px; font-weight: 600; white-space: nowrap;">● Protected</span>
                            <?php elseif ($site['status'] === 'PROTECTION_READY'): ?>
                                <span style="background: rgba(56,189,248,0.15); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3); font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 9999px; font-weight: 600; white-space: nowrap;">● Verified (Setup Pending)</span>
                            <?php else: ?>
                                <span style="background: rgba(234,179,8,0.15); color: #facc15; border: 1px solid rgba(234,179,8,0.3); font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 9999px; font-weight: 600; white-space: nowrap;">● Ownership Required</span>
                            <?php endif; ?>
                        </div>
                        <p style="margin: 0; color: #64748b; font-size: 0.75rem; word-break: break-all;">Origin: <?= htmlspecialchars($site['origin_url']) ?></p>

                        <?php if ($site['status'] === 'OWNERSHIP_REQUIRED'): ?>
                            <div style="margin-top: 1rem; padding: 0.75rem; background: #0f172a; border-radius: 6px; border: 1px solid #1e293b; font-size: 0.75rem;">
                                <div style="color: #cbd5e1; font-weight: 600; margin-bottom: 0.35rem;">Verify Ownership:</div>
                                <div style="color: #94a3b8; line-height: 1.4;">Add a DNS <code>TXT</code> record to your domain root:</div>
                                <div style="margin-top: 0.35rem; background: #050811; padding: 0.4rem; border-radius: 4px; font-family: monospace; color: #38bdf8; word-break: break-all;"><?= htmlspecialchars($site['verification_token']) ?></div>
                                <div style="color: #64748b; margin-top: 0.35rem;">Or place a file at: <code>/.well-known/shieldlayer-verification.txt</code></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 1.25rem; padding-top: 0.75rem; border-top: 1px solid #1e293b; display: flex; justify-content: flex-end; gap: 0.5rem;">
                        <?php if ($site['status'] === 'OWNERSHIP_REQUIRED'): ?>
                            <form method="POST" action="/website/verify" style="margin: 0; width: 100%;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="website_id" value="<?= htmlspecialchars($site['id']) ?>">
                                <button type="submit" style="width: 100%; padding: 0.5rem; background: #eab308; color: #000; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Verify Ownership Now</button>
                            </form>
                        <?php else: ?>
                            <div style="display: flex; gap: 0.5rem; align-items: center; width: 100%;">
    <span style="font-size: 0.75rem; color: #4ade80; font-weight: 600; margin-right: auto;">● Verified</span>
    <form method="POST" action="/website/scan" style="margin: 0;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="website_id" value="<?= htmlspecialchars($site['id']) ?>">
        <button type="submit" style="padding: 0.45rem 0.85rem; background: #0284c7; color: #fff; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.35rem;">
            🔍 Scan Now
        </button>
    </form>
</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <!-- Protection Mode Status -->
    <div style="background: #0f172a; border: 1px solid #1e293b; border-radius: 10px; padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Protection Mode</span>
            <span style="background: rgba(34, 197, 94, 0.2); color: #4ade80; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 9999px; font-weight: 600;">● Protected</span>
        </div>
        <h4 style="margin: 0 0 0.3rem 0; color: #f8fafc; font-size: 1.1rem;">Traffic Filter Active</h4>
        <p style="margin: 0; color: #94a3b8; font-size: 0.8rem;">Actively intercepting malicious requests, SQL injections, and suspicious bots.</p>
    </div>

    <!-- Security Score -->
    <div style="background: #0f172a; border: 1px solid #1e293b; border-radius: 10px; padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Security Score</span>
                <div style="font-size: 1.8rem; font-weight: 800; color: #38bdf8; margin-top: 0.2rem;">87 <span style="font-size: 0.9rem; color: #64748b;">/ 100</span></div>
            </div>
            <span style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 6px;">Good Posture</span>
        </div>
        <div style="margin-top: 0.75rem; font-size: 0.75rem; color: #94a3b8; display: flex; gap: 0.75rem;">
            <span>Critical: <b style="color: #ef4444;">0</b></span>
            <span>High: <b style="color: #f97316;">1</b></span>
            <span>Medium: <b style="color: #eab308;">2</b></span>
        </div>
    </div>
</div>
﻿<div style="width: 100%; max-width: 1000px; margin: 0 auto;">
    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
    <?php endif; ?>

    <!-- Scope Header -->
    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Current Organization Scope</div>
            <div style="font-size: 1.3rem; font-weight: 700; color: #38bdf8;">
                <?= htmlspecialchars($current_tenant['name'] ?? 'No Organization Selected') ?>
            </div>
            <?php if (!empty($current_tenant['slug'])): ?>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">
                    Slug: <?= htmlspecialchars($current_tenant['slug']) ?> | Plan: <?= strtoupper(htmlspecialchars($current_tenant['plan'])) ?> | 
                    <span style="color: #34d399; font-weight: 600;">Your Access Level: <?= strtoupper(htmlspecialchars($tenant_role)) ?></span>
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
                <div style="font-weight: 600; font-size: 1.05rem;">Website Security Score: <?= (int)$health_status['score'] ?>/100</div>
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.2rem;">Core server primitives and baseline runtime posture monitored by ShieldLayer.</div>
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
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">BLOCKED REQUESTS</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #ef4444; margin-top: 0.25rem;"><?= (int) $total_threats ?></div>
        </div>
        <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">SQL INJECTIONS ATTEMPTED</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #f87171; margin-top: 0.25rem;"><?= (int) ($threat_metrics['SQL_INJECTION'] ?? 0) ?></div>
        </div>
        <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">XSS ATTEMPTS</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #fbbf24; margin-top: 0.25rem;"><?= (int) ($threat_metrics['CROSS_SITE_SCRIPTING'] ?? 0) ?></div>
        </div>
        <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">BLOCKED IPS</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #a78bfa; margin-top: 0.25rem;"><?= (int) ($threat_metrics['IP_BLACKLISTED'] ?? 0) ?></div>
        </div>
        <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; text-align: center;">
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">RATE-LIMITED REQUESTS</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #38bdf8; margin-top: 0.25rem;"><?= (int) ($threat_metrics['RATE_LIMIT_EXCEEDED'] ?? 0) ?></div>
        </div>
    </div>

    <!-- Live Security Activity Table -->
    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Live Security Activity (Live Security Activity)</h3>
        
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

    <!-- Traffic Protection & Access Rules -->
    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Traffic Protection & Access Rules</h3>
        
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

                <button type="submit" class="btn" style="flex: 1; min-width: 140px; padding: 0.65rem;">Add Rule</button>
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
            <p style="color: var(--text-muted); font-size: 0.85rem;">No active traffic rules added yet.</p>
        <?php endif; ?>
    </div>

    <!-- Team Access Control (Access Control) Section -->
    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
        <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Team Members & Access</h3>

        <?php if ($tenant_role === 'owner'): ?>
            <form method="POST" action="/team/invite" style="display: flex; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div style="flex: 2; min-width: 200px;">
                    <input type="email" name="email" placeholder="Team member email address..." required style="width: 100%; background: #0b0f19; border: 1px solid var(--border-color); color: var(--text-main); padding: 0.65rem 1rem; border-radius: 6px; outline: none;">
                </div>

                <div style="flex: 1; min-width: 130px;">
                    <select name="role" style="width: 100%; background: #0b0f19; border: 1px solid var(--border-color); color: var(--text-main); padding: 0.65rem; border-radius: 6px; outline: none;">
                        <option value="analyst">Analyst</option>
                        <option value="viewer">Viewer</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>

                <button type="submit" class="btn" style="flex: 1; min-width: 130px; padding: 0.65rem;">Add Team Member</button>
            </form>
        <?php endif; ?>

        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color); text-align: left; color: var(--text-muted);">
                    <th style="padding: 0.6rem;">Team Member</th>
                    <th style="padding: 0.6rem;">Email</th>
                    <th style="padding: 0.6rem;">Access Level</th>
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
            <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Your Account Profile</h3>
            <p style="font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($user_name) ?></p>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;"><?= htmlspecialchars($user_email) ?></p>
            <div style="margin-top: 1rem; display: inline-block; background: rgba(56, 189, 248, 0.15); border: 1px solid #0284c7; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: #38bdf8;">
                Access Level: <?= htmlspecialchars($user_role) ?>
            </div>
        </div>

        <div class="card" style="max-width: none;">
            <h3 style="font-size: 1.1rem; color: #38bdf8; margin-bottom: 1rem;">Create an Organization</h3>
            <form method="POST" action="/tenant/create">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="org_name">Organization Name</label>
                    <input type="text" id="org_name" name="org_name" placeholder="e.g. Acme Cyber Defense" required>
                </div>
                <button type="submit" class="btn" style="padding: 0.6rem;">Create Organization</button>
            </form>
        </div>
    </div>
</div>

<!-- Security Event Inspection Drawer -->
<div id="eventDetailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); z-index:99999; justify-content:center; align-items:center;">
    <div style="background:#0f172a; border:1px solid #1e293b; border-radius:12px; max-width:600px; width:90%; padding:1.75rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.7); max-height:85vh; overflow-y:auto; color:#f8fafc;">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #1e293b; padding-bottom:0.75rem; margin-bottom:1rem;">
            <div>
                <h3 id="modalTitle" style="margin:0; font-size:1.15rem; color:#38bdf8; display:flex; align-items:center; gap:0.5rem;">
                    <span>🛡️</span> Security Activity Details
                </h3>
                <small id="modalTime" style="color:#94a3b8; font-size:0.75rem;"></small>
            </div>
            <button onclick="document.getElementById('eventDetailModal').style.display='none'" style="background:transparent; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer;">✕</button>
        </div>

        <div style="margin-bottom:1.25rem;">
            <div style="font-size:0.85rem; color:#cbd5e1; margin-bottom:0.35rem; font-weight:600;">What happened?</div>
            <div id="modalSummary" style="background:#1e293b; padding:0.75rem; border-radius:8px; font-size:0.85rem; color:#e2e8f0; line-height:1.4;">
                A suspicious pattern was intercepted and prevented from reaching your server.
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1.25rem; font-size:0.8rem;">
            <div style="background:#0b0f19; padding:0.6rem; border-radius:6px; border:1px solid #1e293b;">
                <span style="color:#64748b; display:block;">Action Taken:</span>
                <span id="modalAction" style="color:#4ade80; font-weight:600;">Blocked</span>
            </div>
            <div style="background:#0b0f19; padding:0.6rem; border-radius:6px; border:1px solid #1e293b;">
                <span style="color:#64748b; display:block;">Target Path:</span>
                <span id="modalPath" style="color:#38bdf8; font-family:monospace;">/</span>
            </div>
        </div>

        <details style="margin-top:1rem; background:#0b0f19; border:1px solid #1e293b; border-radius:8px; padding:0.75rem;">
            <summary style="color:#38bdf8; font-size:0.8rem; font-weight:600; cursor:pointer;">Technical Details & Evidence</summary>
            <div style="margin-top:0.75rem; font-family:monospace; font-size:0.75rem; color:#cbd5e1; display:flex; flex-direction:column; gap:0.4rem;">
                <div><strong style="color:#64748b;">Source IP:</strong> <span id="modalIp">127.0.0.1</span></div>
                <div><strong style="color:#64748b;">HTTP Method:</strong> <span id="modalMethod">POST</span></div>
                <div><strong style="color:#64748b;">Detection Engine:</strong> <span id="modalEngine">ShieldLayer Core WAF</span></div>
                <div style="margin-top:0.35rem;"><strong style="color:#64748b;">Inspected Payload / Query:</strong></div>
                <pre id="modalPayload" style="background:#050811; padding:0.5rem; border-radius:4px; overflow-x:auto; color:#f87171; margin:0.25rem 0 0 0;"></pre>
            </div>
        </details>
    </div>
</div>

<script>
function showSecurityDetail(title, time, summary, action, path, ip, method, payload) {
    document.getElementById('modalTitle').innerText = title || 'Security Event';
    document.getElementById('modalTime').innerText = time || '';
    document.getElementById('modalSummary').innerText = summary || 'ShieldLayer examined the incoming request and applied traffic protection.';
    document.getElementById('modalAction').innerText = action || 'Blocked';
    document.getElementById('modalPath').innerText = path || '/';
    document.getElementById('modalIp').innerText = ip || 'Protected';
    document.getElementById('modalMethod').innerText = method || 'GET';
    document.getElementById('modalPayload').innerText = payload || 'None / Cleared';
    document.getElementById('eventDetailModal').style.display = 'flex';
}
</script>