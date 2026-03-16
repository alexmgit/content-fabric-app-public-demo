-- Важно: Это демонстрационная схема, а не точная замена production migrations.

BEGIN;

CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    current_team_id BIGINT NULL,
    profile_photo_path VARCHAR(2048) NULL,
    partner_id BIGINT NULL,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS teams (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    personal_team BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_teams_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

ALTER TABLE users
    ADD CONSTRAINT fk_users_partner
        FOREIGN KEY (partner_id) REFERENCES users (id) ON DELETE SET NULL;

ALTER TABLE users
    ADD CONSTRAINT fk_users_current_team
        FOREIGN KEY (current_team_id) REFERENCES teams (id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS team_user (
    id BIGSERIAL PRIMARY KEY,
    team_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_team_user_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE,
    CONSTRAINT fk_team_user_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT uq_team_user UNIQUE (team_id, user_id)
);

CREATE TABLE IF NOT EXISTS team_invitations (
    id BIGSERIAL PRIMARY KEY,
    team_id BIGINT NOT NULL,
    email VARCHAR(255) NOT NULL,
    role VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_team_invitations_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_telegrams (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    chat_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_user_telegrams_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT uq_user_telegrams_user UNIQUE (user_id)
);

CREATE TABLE IF NOT EXISTS plans (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NULL,
    price NUMERIC(12, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS subscriptions (
    id BIGSERIAL PRIMARY KEY,
    plan_id BIGINT NULL,
    subscriber_id BIGINT NULL,
    subscriber_type VARCHAR(255) NULL,
    name VARCHAR(255) NULL,
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    trial_ends_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_subscriptions_plan
        FOREIGN KEY (plan_id) REFERENCES plans (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS apify_jobs (
    id BIGSERIAL PRIMARY KEY,
    actor VARCHAR(255) NOT NULL,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    job_options JSONB NULL,
    job_data JSONB NULL,
    job_result JSONB NULL,
    job_id VARCHAR(255) NULL,
    job_status VARCHAR(100) NULL,
    job_error TEXT NULL,
    price NUMERIC(12, 4) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_apify_jobs_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_apify_jobs_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS billing_payments (
    id BIGSERIAL PRIMARY KEY,
    uuid UUID NOT NULL UNIQUE,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    amount NUMERIC(12, 2) NOT NULL,
    currency VARCHAR(16) NOT NULL DEFAULT 'RUB',
    description TEXT NOT NULL,
    status VARCHAR(100) NOT NULL,
    payment_id VARCHAR(255) NULL,
    confirmation_token TEXT NULL,
    extra_data JSONB NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_billing_payments_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_billing_payments_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS billing_payment_methods (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    payment_method_id VARCHAR(255) NOT NULL,
    payment_method_title VARCHAR(255) NULL,
    payment_method_data JSONB NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_billing_payment_methods_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_billing_payment_methods_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS billing_transactions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    type VARCHAR(100) NOT NULL,
    direction VARCHAR(50) NOT NULL,
    amount NUMERIC(12, 2) NOT NULL,
    currency VARCHAR(16) NOT NULL DEFAULT 'RUB',
    description TEXT NOT NULL,
    payment_id BIGINT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_billing_transactions_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_billing_transactions_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL,
    CONSTRAINT fk_billing_transactions_payment
        FOREIGN KEY (payment_id) REFERENCES billing_payments (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS billing_use_promocode (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    promocode VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_billing_use_promocode_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_billing_use_promocode_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS billing_subscription_payments (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    payment_id BIGINT NULL,
    subscription_id BIGINT NOT NULL,
    subscription_starts_at TIMESTAMP NULL,
    subscription_ends_at TIMESTAMP NULL,
    status VARCHAR(100) NOT NULL,
    amount NUMERIC(12, 2) NOT NULL,
    retry_count INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_billing_subscription_payments_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_billing_subscription_payments_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL,
    CONSTRAINT fk_billing_subscription_payments_payment
        FOREIGN KEY (payment_id) REFERENCES billing_payments (id) ON DELETE SET NULL,
    CONSTRAINT fk_billing_subscription_payments_subscription
        FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS searches (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    search_type VARCHAR(100) NOT NULL,
    query TEXT NOT NULL,
    query_type VARCHAR(100) NOT NULL,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    last_parsed_at TIMESTAMP NULL,
    tags JSONB NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    parse_count INTEGER NOT NULL DEFAULT 0,
    schedule_type VARCHAR(100) NULL,
    schedule_period VARCHAR(50) NULL,
    schedule_days JSONB NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_searches_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_searches_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS searches_runs (
    id BIGSERIAL PRIMARY KEY,
    search_id BIGINT NOT NULL,
    status VARCHAR(100) NOT NULL,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    search_job_id BIGINT NULL,
    source_job_id BIGINT NULL,
    is_post_processed BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_searches_runs_search
        FOREIGN KEY (search_id) REFERENCES searches (id) ON DELETE CASCADE,
    CONSTRAINT fk_searches_runs_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_searches_runs_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL,
    CONSTRAINT fk_searches_runs_search_job
        FOREIGN KEY (search_job_id) REFERENCES apify_jobs (id) ON DELETE SET NULL,
    CONSTRAINT fk_searches_runs_source_job
        FOREIGN KEY (source_job_id) REFERENCES apify_jobs (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS searches_sources (
    id BIGSERIAL PRIMARY KEY,
    search_id BIGINT NOT NULL,
    run_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    source_type VARCHAR(100) NOT NULL,
    source_url TEXT NOT NULL,
    source_follows_count INTEGER NULL,
    source_followers_count INTEGER NULL,
    source_posts_count INTEGER NULL,
    hash VARCHAR(255) NULL,
    search_views_count INTEGER NULL,
    search_likes_count INTEGER NULL,
    search_comments_count INTEGER NULL,
    interest_level VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_searches_sources_search
        FOREIGN KEY (search_id) REFERENCES searches (id) ON DELETE CASCADE,
    CONSTRAINT fk_searches_sources_run
        FOREIGN KEY (run_id) REFERENCES searches_runs (id) ON DELETE CASCADE,
    CONSTRAINT fk_searches_sources_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_searches_sources_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS search_events (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    search_id BIGINT NOT NULL,
    search_run_id BIGINT NULL,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    data JSONB NULL,
    tags JSONB NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_search_events_search
        FOREIGN KEY (search_id) REFERENCES searches (id) ON DELETE CASCADE,
    CONSTRAINT fk_search_events_run
        FOREIGN KEY (search_run_id) REFERENCES searches_runs (id) ON DELETE SET NULL,
    CONSTRAINT fk_search_events_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_search_events_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS sources (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    url TEXT NOT NULL,
    type VARCHAR(100) NOT NULL,
    source_type VARCHAR(100) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    last_parsed_at TIMESTAMP NULL,
    tags JSONB NULL,
    post_parse_count INTEGER NOT NULL DEFAULT 0,
    post_schedule_type VARCHAR(100) NULL,
    post_schedule_period VARCHAR(50) NULL,
    post_schedule_days JSONB NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_sources_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_sources_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS source_runs (
    id BIGSERIAL PRIMARY KEY,
    source_id BIGINT NOT NULL,
    status VARCHAR(100) NOT NULL,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    profile_job_id BIGINT NULL,
    post_job_id BIGINT NULL,
    is_post_processed BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_source_runs_source
        FOREIGN KEY (source_id) REFERENCES sources (id) ON DELETE CASCADE,
    CONSTRAINT fk_source_runs_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_source_runs_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL,
    CONSTRAINT fk_source_runs_profile_job
        FOREIGN KEY (profile_job_id) REFERENCES apify_jobs (id) ON DELETE SET NULL,
    CONSTRAINT fk_source_runs_post_job
        FOREIGN KEY (post_job_id) REFERENCES apify_jobs (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS source_posts (
    id BIGSERIAL PRIMARY KEY,
    source_id BIGINT NOT NULL,
    run_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    profile_url TEXT NULL,
    profile_followers_count INTEGER NULL,
    post_type VARCHAR(100) NULL,
    post_caption TEXT NULL,
    post_created_at TIMESTAMP NULL,
    post_likes_count INTEGER NULL,
    post_likes_count_avg INTEGER NULL,
    post_likes_count_median INTEGER NULL,
    post_comments_count INTEGER NULL,
    post_comments_count_avg INTEGER NULL,
    post_comments_count_median INTEGER NULL,
    post_views_count INTEGER NULL,
    post_views_count_avg INTEGER NULL,
    post_views_count_median INTEGER NULL,
    post_shared_count INTEGER NULL,
    post_shared_count_avg INTEGER NULL,
    post_shared_count_median INTEGER NULL,
    post_hash VARCHAR(255) NULL,
    post_url TEXT NULL,
    post_location VARCHAR(255) NULL,
    viral_level VARCHAR(100) NULL,
    metric_engagement_rate NUMERIC(12, 6) NULL,
    metric_engagement_rate_followers NUMERIC(12, 6) NULL,
    metric_views_followers_ratio NUMERIC(12, 6) NULL,
    metric_likes_views_ratio NUMERIC(12, 6) NULL,
    metric_comments_views_ratio NUMERIC(12, 6) NULL,
    metric_engagement_velocity NUMERIC(12, 6) NULL,
    metric_quality_score NUMERIC(12, 6) NULL,
    metric_viral_level VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_source_posts_source
        FOREIGN KEY (source_id) REFERENCES sources (id) ON DELETE CASCADE,
    CONSTRAINT fk_source_posts_run
        FOREIGN KEY (run_id) REFERENCES source_runs (id) ON DELETE CASCADE,
    CONSTRAINT fk_source_posts_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_source_posts_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS source_events (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    source_id BIGINT NOT NULL,
    source_run_id BIGINT NULL,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    data JSONB NULL,
    tags JSONB NULL,
    hash VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_source_events_source
        FOREIGN KEY (source_id) REFERENCES sources (id) ON DELETE CASCADE,
    CONSTRAINT fk_source_events_run
        FOREIGN KEY (source_run_id) REFERENCES source_runs (id) ON DELETE SET NULL,
    CONSTRAINT fk_source_events_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_source_events_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS source_post_transcribes (
    id BIGSERIAL PRIMARY KEY,
    job_id BIGINT NULL,
    post_id BIGINT NOT NULL,
    file_url TEXT NULL,
    transcription TEXT NULL,
    result JSONB NULL,
    status VARCHAR(100) NOT NULL,
    user_id BIGINT NOT NULL,
    team_id BIGINT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_source_post_transcribes_job
        FOREIGN KEY (job_id) REFERENCES apify_jobs (id) ON DELETE SET NULL,
    CONSTRAINT fk_source_post_transcribes_post
        FOREIGN KEY (post_id) REFERENCES source_posts (id) ON DELETE CASCADE,
    CONSTRAINT fk_source_post_transcribes_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_source_post_transcribes_team
        FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL,
    CONSTRAINT uq_source_post_transcribes_post UNIQUE (post_id)
);

CREATE INDEX IF NOT EXISTS idx_apify_jobs_job_id ON apify_jobs (job_id);
CREATE INDEX IF NOT EXISTS idx_billing_payments_payment_id ON billing_payments (payment_id);
CREATE INDEX IF NOT EXISTS idx_billing_payment_methods_method_id ON billing_payment_methods (payment_method_id);
CREATE INDEX IF NOT EXISTS idx_billing_transactions_user_id ON billing_transactions (user_id);
CREATE INDEX IF NOT EXISTS idx_searches_user_id ON searches (user_id);
CREATE INDEX IF NOT EXISTS idx_searches_runs_search_id ON searches_runs (search_id);
CREATE INDEX IF NOT EXISTS idx_searches_sources_hash ON searches_sources (hash);
CREATE INDEX IF NOT EXISTS idx_sources_user_id ON sources (user_id);
CREATE INDEX IF NOT EXISTS idx_source_runs_source_id ON source_runs (source_id);
CREATE INDEX IF NOT EXISTS idx_source_posts_post_hash ON source_posts (post_hash);
CREATE INDEX IF NOT EXISTS idx_source_events_hash ON source_events (hash);

COMMIT;
