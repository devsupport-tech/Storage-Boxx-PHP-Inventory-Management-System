-- Storage Boxx PostgreSQL Schema for Supabase
-- Converted from MySQL to PostgreSQL

-- (A) SETTINGS
CREATE TABLE settings (
    setting_name VARCHAR(255) NOT NULL PRIMARY KEY,
    setting_description VARCHAR(255) DEFAULT NULL,
    setting_value VARCHAR(255) NOT NULL,
    setting_group INTEGER NOT NULL DEFAULT 1
);

CREATE INDEX idx_settings_group ON settings(setting_group);

INSERT INTO settings (setting_name, setting_description, setting_value, setting_group) VALUES
('APP_VER', 'App version', '1', 0),
('CACHE_VER', 'Client storage cache timestamp', '0', 1),
('EMAIL_FROM', 'System email from', 'sys@site.com', 1),
('PAGE_PER', 'Number of entries per page', '20', 1),
('SUGGEST_LIMIT', 'Autocomplete suggestion limit', '5', 1),
('D_LONG', 'PostgreSQL date format (long)', 'DD Month YYYY', 1),
('D_SHORT', 'PostgreSQL date format (short)', 'YYYY-MM-DD', 1),
('DT_LONG', 'PostgreSQL date time format (long)', 'DD Month YYYY HH12:MI:SS AM', 1),
('DT_SHORT', 'PostgreSQL date time format (short)', 'YYYY-MM-DD HH24:MI:SS', 1),
('DELIVER_STAT', 'Delivery status code', '["Processing","Completed","Cancelled"]', 2),
('PURCHASE_STAT', 'Purchase status code', '["Processing","Completed","Cancelled"]', 2),
('STOCK_MVT', 'Stock movement code', '{"I":"Stock In (Receive)","O":"Stock Out (Dispatch)","T":"Stock Take (Audit)","D":"Stock Discard (Dispose)"}', 2);

-- (B) USERS
CREATE TABLE users (
    user_id BIGSERIAL PRIMARY KEY,
    user_level VARCHAR(1) NOT NULL DEFAULT 'U',
    user_name VARCHAR(255) NOT NULL,
    user_email VARCHAR(255) NOT NULL UNIQUE,
    user_password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_users_name ON users(user_name);
CREATE INDEX idx_users_level ON users(user_level);

-- (C) USERS HASH
CREATE TABLE users_hash (
    user_id BIGINT NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    hash_for VARCHAR(3) NOT NULL,
    hash_code TEXT NOT NULL,
    hash_time TIMESTAMP WITH TIME ZONE NOT NULL,
    hash_tries INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id, hash_for)
);

-- (D) PUSH NOTIFICATIONS
CREATE TABLE webpush (
    endpoint VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT REFERENCES users(user_id) ON DELETE CASCADE,
    data TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_webpush_user ON webpush(user_id);

-- (E) ITEMS
CREATE TABLE items (
    item_sku VARCHAR(255) NOT NULL PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    item_desc VARCHAR(255) DEFAULT NULL,
    item_unit VARCHAR(255) NOT NULL,
    item_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    item_low DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    item_qty DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_items_low ON items(item_low);
CREATE INDEX idx_items_qty ON items(item_qty);
CREATE INDEX idx_items_name ON items(item_name);

-- (F) ITEM MOVEMENT
CREATE TABLE item_mvt (
    item_sku VARCHAR(255) NOT NULL REFERENCES items(item_sku) ON DELETE CASCADE,
    mvt_date TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    mvt_direction VARCHAR(1) NOT NULL,
    mvt_qty DECIMAL(12,2) NOT NULL,
    mvt_notes TEXT DEFAULT NULL,
    item_left DECIMAL(12,2) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    d_id BIGINT DEFAULT NULL,
    p_id BIGINT DEFAULT NULL,
    PRIMARY KEY (item_sku, mvt_date)
);

CREATE INDEX idx_item_mvt_direction ON item_mvt(mvt_direction);
CREATE INDEX idx_item_mvt_user ON item_mvt(user_name);
CREATE INDEX idx_item_mvt_delivery ON item_mvt(d_id);
CREATE INDEX idx_item_mvt_purchase ON item_mvt(p_id);
CREATE INDEX idx_item_mvt_date ON item_mvt(mvt_date);

-- (G) SUPPLIERS
CREATE TABLE suppliers (
    sup_id BIGSERIAL PRIMARY KEY,
    sup_name VARCHAR(255) NOT NULL,
    sup_tel VARCHAR(32) NOT NULL,
    sup_email VARCHAR(255) NOT NULL UNIQUE,
    sup_address VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_suppliers_name ON suppliers(sup_name);

-- (H) SUPPLIER ITEMS
CREATE TABLE suppliers_items (
    sup_id BIGINT NOT NULL REFERENCES suppliers(sup_id) ON DELETE CASCADE,
    item_sku VARCHAR(255) NOT NULL REFERENCES items(item_sku) ON DELETE CASCADE,
    sup_sku VARCHAR(255) DEFAULT NULL,
    unit_price DECIMAL(12,2) DEFAULT 0.00,
    PRIMARY KEY (sup_id, item_sku)
);

CREATE INDEX idx_suppliers_items_sku ON suppliers_items(sup_sku);

-- (I) CUSTOMERS
CREATE TABLE customers (
    cus_id BIGSERIAL PRIMARY KEY,
    cus_name VARCHAR(255) NOT NULL,
    cus_tel VARCHAR(32) NOT NULL,
    cus_email VARCHAR(255) NOT NULL UNIQUE,
    cus_address VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_customers_name ON customers(cus_name);

-- (J) PURCHASES
CREATE TABLE purchases (
    p_id BIGSERIAL PRIMARY KEY,
    sup_id BIGINT NOT NULL REFERENCES suppliers(sup_id) ON DELETE RESTRICT,
    p_name VARCHAR(255) NOT NULL,
    p_tel VARCHAR(32) NOT NULL,
    p_email VARCHAR(255) NOT NULL,
    p_address VARCHAR(255) NOT NULL,
    p_notes TEXT DEFAULT NULL,
    p_date DATE NOT NULL DEFAULT CURRENT_DATE,
    p_status SMALLINT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_purchases_supplier ON purchases(sup_id);
CREATE INDEX idx_purchases_name ON purchases(p_name);
CREATE INDEX idx_purchases_date ON purchases(p_date);
CREATE INDEX idx_purchases_status ON purchases(p_status);

-- (K) PURCHASE ITEMS
CREATE TABLE purchases_items (
    p_id BIGINT NOT NULL REFERENCES purchases(p_id) ON DELETE CASCADE,
    item_sku VARCHAR(255) NOT NULL REFERENCES items(item_sku) ON DELETE CASCADE,
    item_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    item_qty DECIMAL(12,2) NOT NULL,
    item_sort BIGINT NOT NULL DEFAULT 0,
    PRIMARY KEY (p_id, item_sku)
);

CREATE INDEX idx_purchases_items_sort ON purchases_items(item_sort);

-- (L) DELIVERIES
CREATE TABLE deliveries (
    d_id BIGSERIAL PRIMARY KEY,
    cus_id BIGINT NOT NULL REFERENCES customers(cus_id) ON DELETE RESTRICT,
    d_name VARCHAR(255) NOT NULL,
    d_tel VARCHAR(32) NOT NULL,
    d_email VARCHAR(255) NOT NULL,
    d_address VARCHAR(255) NOT NULL,
    d_notes TEXT DEFAULT NULL,
    d_date DATE NOT NULL DEFAULT CURRENT_DATE,
    d_status SMALLINT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_deliveries_customer ON deliveries(cus_id);
CREATE INDEX idx_deliveries_name ON deliveries(d_name);
CREATE INDEX idx_deliveries_date ON deliveries(d_date);
CREATE INDEX idx_deliveries_status ON deliveries(d_status);

-- (M) DELIVERY ITEMS
CREATE TABLE deliveries_items (
    d_id BIGINT NOT NULL REFERENCES deliveries(d_id) ON DELETE CASCADE,
    item_sku VARCHAR(255) NOT NULL REFERENCES items(item_sku) ON DELETE CASCADE,
    item_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    item_qty DECIMAL(12,2) NOT NULL,
    item_sort BIGINT NOT NULL DEFAULT 0,
    PRIMARY KEY (d_id, item_sku)
);

CREATE INDEX idx_deliveries_items_sort ON deliveries_items(item_sort);

-- Add foreign key constraints for item movements
ALTER TABLE item_mvt ADD CONSTRAINT fk_item_mvt_delivery 
    FOREIGN KEY (d_id) REFERENCES deliveries(d_id) ON DELETE SET NULL;

ALTER TABLE item_mvt ADD CONSTRAINT fk_item_mvt_purchase 
    FOREIGN KEY (p_id) REFERENCES purchases(p_id) ON DELETE SET NULL;

-- Create functions for updated_at timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at timestamps
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_items_updated_at BEFORE UPDATE ON items
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_suppliers_updated_at BEFORE UPDATE ON suppliers
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_customers_updated_at BEFORE UPDATE ON customers
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_purchases_updated_at BEFORE UPDATE ON purchases
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_deliveries_updated_at BEFORE UPDATE ON deliveries
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();