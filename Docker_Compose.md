---
tags: [component, Sales_Inventory_CRM]
---
- Path: `docker-compose.yml`, `docker/php`, `docker/nginx`, `docker/mysql`
- Role: Orchestrates all services (nginx, php-fpm, queue worker, scheduler, mysql, redis, mailpit, vite per README); only host prerequisite is Docker
- Talks to: [[React_SPA]], [[Laravel_API]], [[MySQL_Database]], [[Redis_Cache]], [[Mailpit_SMTP]]
- Back: [[ARCHITECTURE]]
