-- Select your CRM database in phpMyAdmin, then run this file.
-- Use either this SQL or the new migration. The migration tolerates an existing catalogues table.
CREATE TABLE IF NOT EXISTS `catalogues` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `description` LONGTEXT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `catalogues_category_id_index` (`category_id`),
  KEY `catalogues_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`name`,`guard_name`,`created_at`,`updated_at`)
SELECT 'catalogue.manage','web',NOW(),NOW()
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name`='catalogue.manage' AND `guard_name`='web');

INSERT IGNORE INTO `role_has_permissions` (`permission_id`,`role_id`)
SELECT p.id,r.id FROM `permissions` p CROSS JOIN `roles` r
WHERE p.name='catalogue.manage' AND p.guard_name='web'
AND r.name='superadmin' AND r.guard_name='web';

-- After SQL import: System > Roles > Superadmin > Permissions > Save.
-- This refreshes the permission cache without terminal access.
-- Grant catalogue.manage to other roles as needed.
