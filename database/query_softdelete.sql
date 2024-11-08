ALTER TABLE agents
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE agent_master_prices
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE areas
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE area_cities
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE banks
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE branchs
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE cities
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE customers
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE customer_branchs
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE customer_brands
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE customer_master_prices
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE customer_mous
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE customer_pics
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE customer_trucking_prices
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE departemens
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE drivers
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE locations
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE payment_types
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE provinces
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE services
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE service_groups
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE trucking_prices
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE trucks
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE truck_types
ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE users
-- ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE user_agents
-- ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

ALTER TABLE user_customers
-- ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`,
ADD COLUMN `deleted_by` BIGINT(10) UNSIGNED NULL AFTER `deleted_at`;

