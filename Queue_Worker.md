---
tags: [component, Sales_Inventory_CRM]
---
- Path: `backend/app/Console`, `backend/config/queue.php`
- Role: Background job processing (queued email/SMS notifications, PDF invoice email per README); run via `php artisan queue:listen` (`backend/composer.json` `dev` script)
- Talks to: [[Mailpit_SMTP]]
- Back: [[ARCHITECTURE]]
