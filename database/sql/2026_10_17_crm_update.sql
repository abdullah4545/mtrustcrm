-- Select the existing CRM database in phpMyAdmin first. Back up before deployment.
-- Idempotent MySQL/MariaDB equivalent of 2026_10_17_000001_add_contact_name_parts.
-- Legacy title/name columns and all existing contacts remain intact.
SET @crm_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='organization_contacts' AND COLUMN_NAME='first_name')=0,
 'ALTER TABLE organization_contacts ADD COLUMN first_name VARCHAR(255) NULL', 'SELECT 1');
PREPARE crm_stmt FROM @crm_sql;
EXECUTE crm_stmt;
DEALLOCATE PREPARE crm_stmt;
SET @crm_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='organization_contacts' AND COLUMN_NAME='last_name')=0,
 'ALTER TABLE organization_contacts ADD COLUMN last_name VARCHAR(255) NULL', 'SELECT 1');
PREPARE crm_stmt FROM @crm_sql;
EXECUTE crm_stmt;
DEALLOCATE PREPARE crm_stmt;
UPDATE organization_contacts SET first_name=name
WHERE first_name IS NULL AND last_name IS NULL AND name IS NOT NULL;
-- No schema changes needed for existing organizations.created_by,
-- sales.organization_id, activities.department_id, paid_at or paid_by.
-- Running this SQL and then artisan migrate is safe: migration checks both columns.
