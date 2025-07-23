-- Enable Row Level Security for Supabase
-- This provides multi-tenant security at the database level

-- Enable RLS on all tables
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE users_hash ENABLE ROW LEVEL SECURITY;
ALTER TABLE webpush ENABLE ROW LEVEL SECURITY;
ALTER TABLE items ENABLE ROW LEVEL SECURITY;
ALTER TABLE item_mvt ENABLE ROW LEVEL SECURITY;
ALTER TABLE suppliers ENABLE ROW LEVEL SECURITY;
ALTER TABLE suppliers_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE customers ENABLE ROW LEVEL SECURITY;
ALTER TABLE purchases ENABLE ROW LEVEL SECURITY;
ALTER TABLE purchases_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE deliveries ENABLE ROW LEVEL SECURITY;
ALTER TABLE deliveries_items ENABLE ROW LEVEL SECURITY;

-- Settings table is global, no RLS needed
-- ALTER TABLE settings ENABLE ROW LEVEL SECURITY;

-- Create policies for authenticated users
-- Users can only see their own profile
CREATE POLICY "Users can view own profile" ON users
    FOR SELECT USING (auth.uid()::text = user_id::text);

CREATE POLICY "Users can update own profile" ON users
    FOR UPDATE USING (auth.uid()::text = user_id::text);

-- Users hash - users can only access their own hash data
CREATE POLICY "Users can access own hash data" ON users_hash
    FOR ALL USING (auth.uid()::text = user_id::text);

-- WebPush - users can only access their own push subscriptions
CREATE POLICY "Users can access own push subscriptions" ON webpush
    FOR ALL USING (auth.uid()::text = user_id::text);

-- Items - all authenticated users can view items
CREATE POLICY "Authenticated users can view items" ON items
    FOR SELECT USING (auth.role() = 'authenticated');

-- Admin users can manage items
CREATE POLICY "Admin users can manage items" ON items
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE user_id::text = auth.uid()::text 
            AND user_level = 'A'
        )
    );

-- Item movements - all authenticated users can view
CREATE POLICY "Authenticated users can view item movements" ON item_mvt
    FOR SELECT USING (auth.role() = 'authenticated');

-- Users can create movements (for stock operations)
CREATE POLICY "Authenticated users can create movements" ON item_mvt
    FOR INSERT WITH CHECK (auth.role() = 'authenticated');

-- Suppliers - all authenticated users can view
CREATE POLICY "Authenticated users can view suppliers" ON suppliers
    FOR SELECT USING (auth.role() = 'authenticated');

-- Admin users can manage suppliers
CREATE POLICY "Admin users can manage suppliers" ON suppliers
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE user_id::text = auth.uid()::text 
            AND user_level = 'A'
        )
    );

-- Supplier items - follows supplier policy
CREATE POLICY "Authenticated users can view supplier items" ON suppliers_items
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Admin users can manage supplier items" ON suppliers_items
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE user_id::text = auth.uid()::text 
            AND user_level = 'A'
        )
    );

-- Customers - all authenticated users can view
CREATE POLICY "Authenticated users can view customers" ON customers
    FOR SELECT USING (auth.role() = 'authenticated');

-- Admin users can manage customers
CREATE POLICY "Admin users can manage customers" ON customers
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE user_id::text = auth.uid()::text 
            AND user_level = 'A'
        )
    );

-- Purchases - all authenticated users can view
CREATE POLICY "Authenticated users can view purchases" ON purchases
    FOR SELECT USING (auth.role() = 'authenticated');

-- Authenticated users can create purchases
CREATE POLICY "Authenticated users can create purchases" ON purchases
    FOR INSERT WITH CHECK (auth.role() = 'authenticated');

-- Admin users can update/delete purchases
CREATE POLICY "Admin users can manage purchases" ON purchases
    FOR UPDATE USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE user_id::text = auth.uid()::text 
            AND user_level = 'A'
        )
    );

CREATE POLICY "Admin users can delete purchases" ON purchases
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE user_id::text = auth.uid()::text 
            AND user_level = 'A'
        )
    );

-- Purchase items - follows purchase policy
CREATE POLICY "Authenticated users can view purchase items" ON purchases_items
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Authenticated users can manage purchase items" ON purchases_items
    FOR ALL USING (auth.role() = 'authenticated');

-- Deliveries - all authenticated users can view
CREATE POLICY "Authenticated users can view deliveries" ON deliveries
    FOR SELECT USING (auth.role() = 'authenticated');

-- Authenticated users can create deliveries
CREATE POLICY "Authenticated users can create deliveries" ON deliveries
    FOR INSERT WITH CHECK (auth.role() = 'authenticated');

-- Admin users can update/delete deliveries
CREATE POLICY "Admin users can manage deliveries" ON deliveries
    FOR UPDATE USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE user_id::text = auth.uid()::text 
            AND user_level = 'A'
        )
    );

CREATE POLICY "Admin users can delete deliveries" ON deliveries
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE user_id::text = auth.uid()::text 
            AND user_level = 'A'
        )
    );

-- Delivery items - follows delivery policy
CREATE POLICY "Authenticated users can view delivery items" ON deliveries_items
    FOR SELECT USING (auth.role() = 'authenticated');

CREATE POLICY "Authenticated users can manage delivery items" ON deliveries_items
    FOR ALL USING (auth.role() = 'authenticated');