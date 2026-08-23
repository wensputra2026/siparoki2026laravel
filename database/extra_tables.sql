CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email varchar(255) NOT NULL,
    token varchar(255) NOT NULL,
    created_at timestamp NULL,
    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    tokenable_type varchar(255) NOT NULL,
    tokenable_id bigint unsigned NOT NULL,
    name varchar(255) NOT NULL,
    token varchar(64) NOT NULL,
    abilities text NULL,
    last_used_at timestamp NULL,
    expires_at timestamp NULL,
    created_at timestamp NULL,
    updated_at timestamp NULL,
    PRIMARY KEY (id),
    UNIQUE KEY personal_access_tokens_token_unique (token),
    KEY personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
