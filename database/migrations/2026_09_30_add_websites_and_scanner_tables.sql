-- ShieldLayer Platform: Website Management & Scanner Schema

CREATE TABLE IF NOT EXISTS websites (
    id VARCHAR(36) PRIMARY KEY,
    tenant_id VARCHAR(36) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    origin_url VARCHAR(500) NOT NULL,
    status ENUM('ANALYSIS_ONLY', 'OWNERSHIP_REQUIRED', 'VERIFIED', 'PROTECTION_READY', 'PROTECTED', 'PROTECTION_DEGRADED') DEFAULT 'OWNERSHIP_REQUIRED',
    verification_token VARCHAR(64) NOT NULL,
    verified_at DATETIME NULL,
    traffic_verified_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_domain (domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS website_verifications (
    id VARCHAR(36) PRIMARY KEY,
    website_id VARCHAR(36) NOT NULL,
    method ENUM('DNS_TXT', 'HTTP_FILE', 'META_TAG') DEFAULT 'DNS_TXT',
    token VARCHAR(64) NOT NULL,
    status ENUM('PENDING', 'VERIFIED', 'FAILED') DEFAULT 'PENDING',
    last_checked_at DATETIME NULL,
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_website (website_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS security_scans (
    id VARCHAR(36) PRIMARY KEY,
    website_id VARCHAR(36) NOT NULL,
    score INT DEFAULT 100,
    status ENUM('PENDING', 'RUNNING', 'COMPLETED', 'FAILED') DEFAULT 'PENDING',
    summary JSON NULL,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_website_scan (website_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS scan_findings (
    id VARCHAR(36) PRIMARY KEY,
    scan_id VARCHAR(36) NOT NULL,
    website_id VARCHAR(36) NOT NULL,
    severity ENUM('CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'INFORMATIONAL') DEFAULT 'LOW',
    category VARCHAR(64) NOT NULL,
    title VARCHAR(255) NOT NULL,
    impact TEXT NOT NULL,
    recommendation TEXT NOT NULL,
    evidence TEXT NULL,
    status ENUM('OPEN', 'RESOLVED', 'ACCEPTED_RISK') DEFAULT 'OPEN',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_website_findings (website_id),
    INDEX idx_scan (scan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
