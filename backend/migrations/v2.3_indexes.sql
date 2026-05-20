-- UTM Generator v2.3 - Performance Indexes
-- Run after v2.2_add_tables.sql

-- Composite indexes for dashboard queries
CREATE INDEX IF NOT EXISTS idx_urls_enabled_clicks 
    ON urls(is_enabled, clicks DESC);

CREATE INDEX IF NOT EXISTS idx_urls_generation_date 
    ON urls(generation_date DESC);

CREATE INDEX IF NOT EXISTS idx_urls_username_date 
    ON urls(username, generation_date DESC);

-- Index for the long_url source extraction queries
CREATE INDEX IF NOT EXISTS idx_urls_enabled_longurl 
    ON urls(is_enabled, long_url(100));
