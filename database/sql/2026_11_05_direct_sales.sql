-- Alternative to php artisan migrate. MySQL/MariaDB. Back up the database first.
-- Existing sales, line items, payment history and amounts remain unchanged.
SET @has_opening = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'sales' AND column_name = 'opening_paid_total');
SET @opening_sql = IF(@has_opening = 0, 'ALTER TABLE sales ADD COLUMN opening_paid_total DECIMAL(12,2) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE direct_sale_statement FROM @opening_sql;
EXECUTE direct_sale_statement;
DEALLOCATE PREPARE direct_sale_statement;
ALTER TABLE leads MODIFY next_action_type VARCHAR(255) NULL;
ALTER TABLE lead_activities MODIFY next_action_type VARCHAR(255) NULL;
