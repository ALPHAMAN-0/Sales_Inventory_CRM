-- Dedicated test schema so the Pest suite (which runs against MySQL for
-- FOR UPDATE / CHECK fidelity) never touches dev data.
CREATE DATABASE IF NOT EXISTS `sales_crm_testing`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant the app user full access to the test schema too.
GRANT ALL PRIVILEGES ON `sales_crm_testing`.* TO 'sales'@'%';
FLUSH PRIVILEGES;
