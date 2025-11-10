-- MySQL schema for the Fundación Ecuador Crece Contigo data access platform
-- Compatible with MySQL 8.0+

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    institution VARCHAR(190) NULL,
    country VARCHAR(100) NULL,
    role ENUM('administrator', 'reviewer', 'internal_researcher', 'external_researcher') NOT NULL DEFAULT 'external_researcher',
    status ENUM('pending', 'active', 'suspended') NOT NULL DEFAULT 'pending',
    orcid VARCHAR(32) NULL,
    phone_number VARCHAR(30) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE datasets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(160) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    keywords TEXT NULL,
    category VARCHAR(120) NULL,
    geographic_scope VARCHAR(120) NULL,
    publication_year YEAR NULL,
    contact_name VARCHAR(190) NULL,
    contact_email VARCHAR(190) NULL,
    legal_restrictions TEXT NULL,
    visibility ENUM('public', 'internal', 'restricted') NOT NULL DEFAULT 'restricted',
    storage_uri VARCHAR(500) NOT NULL,
    default_access_level ENUM('preview', 'download') NOT NULL DEFAULT 'preview',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_datasets_created_by FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dataset_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dataset_id BIGINT UNSIGNED NOT NULL,
    version_label VARCHAR(50) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_format ENUM('csv', 'dta', 'rds', 'zip', 'other') NOT NULL,
    file_size_bytes BIGINT UNSIGNED NULL,
    checksum VARCHAR(128) NULL,
    storage_uri VARCHAR(500) NOT NULL,
    change_log TEXT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dataset_versions_dataset FOREIGN KEY (dataset_id) REFERENCES datasets(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_dataset_versions_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dataset_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dataset_version_id BIGINT UNSIGNED NOT NULL,
    file_label VARCHAR(150) NOT NULL,
    file_format ENUM('csv', 'dta', 'rds', 'documentation', 'codebook', 'other') NOT NULL,
    storage_uri VARCHAR(500) NOT NULL,
    file_size_bytes BIGINT UNSIGNED NULL,
    checksum VARCHAR(128) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dataset_files_version FOREIGN KEY (dataset_version_id) REFERENCES dataset_versions(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE access_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dataset_id BIGINT UNSIGNED NOT NULL,
    requester_id BIGINT UNSIGNED NOT NULL,
    intended_use TEXT NOT NULL,
    methodology TEXT NULL,
    institution VARCHAR(190) NULL,
    expected_publication TEXT NULL,
    safeguards TEXT NULL,
    agreement_version VARCHAR(50) NULL,
    status ENUM('submitted', 'in_review', 'approved', 'rejected', 'needs_more_info', 'revoked') NOT NULL DEFAULT 'submitted',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_status_change TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_access_requests_dataset FOREIGN KEY (dataset_id) REFERENCES datasets(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_access_requests_requester FOREIGN KEY (requester_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE access_request_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    access_request_id BIGINT UNSIGNED NOT NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    decision ENUM('approved', 'rejected', 'needs_more_info') NOT NULL,
    decision_notes TEXT NULL,
    decided_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_request FOREIGN KEY (access_request_id) REFERENCES access_requests(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE access_agreements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    access_request_id BIGINT UNSIGNED NOT NULL,
    dataset_id BIGINT UNSIGNED NOT NULL,
    requester_id BIGINT UNSIGNED NOT NULL,
    agreement_text LONGTEXT NOT NULL,
    agreement_signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    signature_ip VARCHAR(45) NULL,
    CONSTRAINT fk_agreements_request FOREIGN KEY (access_request_id) REFERENCES access_requests(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_agreements_dataset FOREIGN KEY (dataset_id) REFERENCES datasets(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_agreements_requester FOREIGN KEY (requester_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE download_audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dataset_version_id BIGINT UNSIGNED NOT NULL,
    access_request_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    download_token VARCHAR(120) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_download_logs_version FOREIGN KEY (dataset_version_id) REFERENCES dataset_versions(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_download_logs_request FOREIGN KEY (access_request_id) REFERENCES access_requests(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_download_logs_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notification_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL UNIQUE,
    subject VARCHAR(190) NOT NULL,
    body LONGTEXT NOT NULL,
    locale VARCHAR(10) NOT NULL DEFAULT 'es',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(120) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(190) NULL,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at DATETIME NULL,
    CONSTRAINT fk_api_tokens_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional seed data for notification templates
INSERT INTO notification_templates (code, subject, body, locale)
VALUES
    ('request_submitted', 'Solicitud de acceso recibida', 'Hola {{nombre}}, hemos recibido tu solicitud para el dataset {{dataset}}. Te notificaremos cuando sea revisada.', 'es'),
    ('request_status_changed', 'Actualización en tu solicitud', 'Tu solicitud para el dataset {{dataset}} ha cambiado al estado {{estado}}. Comentarios: {{comentarios}}', 'es'),
    ('request_approved', 'Solicitud aprobada', 'Tu solicitud para el dataset {{dataset}} ha sido aprobada. Usa el enlace proporcionado para descargar los archivos.', 'es');

-- Recommended indexes for frequent queries
CREATE INDEX idx_datasets_visibility ON datasets (visibility);
CREATE INDEX idx_datasets_category ON datasets (category);
CREATE INDEX idx_access_requests_status ON access_requests (status);
CREATE INDEX idx_download_logs_user ON download_audit_logs (user_id);

