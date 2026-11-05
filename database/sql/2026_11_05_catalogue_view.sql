-- Alternative to the matching migration. No role reset or destructive reseed.
INSERT INTO permissions (name,guard_name,created_at,updated_at)
SELECT 'catalogue.view','web',NOW(),NOW() WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='catalogue.view' AND guard_name='web');
INSERT INTO permissions (name,guard_name,created_at,updated_at)
SELECT 'catalogue.manage','web',NOW(),NOW() WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='catalogue.manage' AND guard_name='web');
INSERT INTO permissions (name,guard_name,created_at,updated_at)
SELECT 'product_category.manage','web',NOW(),NOW() WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='product_category.manage' AND guard_name='web');
INSERT INTO permissions (name,guard_name,created_at,updated_at)
SELECT 'product.category.manage','web',NOW(),NOW() WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name='product.category.manage' AND guard_name='web');
INSERT INTO role_has_permissions (permission_id,role_id)
SELECT p.id,r.id FROM permissions p CROSS JOIN roles r WHERE p.name='catalogue.view' AND p.guard_name='web' AND r.guard_name='web'
AND NOT EXISTS (SELECT 1 FROM role_has_permissions x WHERE x.permission_id=p.id AND x.role_id=r.id);
INSERT INTO role_has_permissions (permission_id,role_id)
SELECT p.id,r.id FROM permissions p CROSS JOIN roles r WHERE p.name IN ('catalogue.view','catalogue.manage','product_category.manage','product.category.manage') AND p.guard_name='web' AND r.guard_name='web' AND r.name='superadmin'
AND NOT EXISTS (SELECT 1 FROM role_has_permissions x WHERE x.permission_id=p.id AND x.role_id=r.id);
-- Default Laravel User morph class; the migration resolves custom morph maps automatically.
INSERT INTO model_has_permissions (permission_id,model_type,model_id)
SELECT p.id,'App\\Models\\User',u.id FROM permissions p CROSS JOIN users u WHERE p.name='catalogue.view' AND p.guard_name='web'
AND NOT EXISTS (SELECT 1 FROM model_has_roles r WHERE r.model_type='App\\Models\\User' AND r.model_id=u.id)
AND NOT EXISTS (SELECT 1 FROM model_has_permissions x WHERE x.permission_id=p.id AND x.model_type='App\\Models\\User' AND x.model_id=u.id);
