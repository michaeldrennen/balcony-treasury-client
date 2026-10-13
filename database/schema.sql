CREATE TABLE IF NOT EXISTS balcony_treasury_yield_curve_rates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    record_date DATE NOT NULL,
    security_type VARCHAR(64) NOT NULL,
    security_desc VARCHAR(64) NOT NULL,
    rate DECIMAL(8,4) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY balcony_treasury_record_security_unique (record_date, security_desc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
