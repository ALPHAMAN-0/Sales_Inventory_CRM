<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lost-customer threshold
    |--------------------------------------------------------------------------
    | A customer with no purchase in this many days is flagged "lost" by the
    | scheduled customers:detect-lost command. This is the config DEFAULT; an
    | admin can override it at runtime via the `settings` table (read through
    | SettingsRepository), so the period is configurable without a redeploy.
    */
    'lost_threshold_days' => (int) env('CRM_LOST_THRESHOLD_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Sales tax rate
    |--------------------------------------------------------------------------
    | Applied to the subtotal when a sale's totals are recalculated.
    */
    'tax_rate' => (float) env('CRM_TAX_RATE', 0.05),

    /*
    |--------------------------------------------------------------------------
    | KPI scoring
    |--------------------------------------------------------------------------
    | Points credited to the assigned employee when a lost customer is
    | recovered (i.e. purchases again while an open assignment exists).
    */
    'kpi' => [
        'recovery_points' => (int) env('CRM_KPI_RECOVERY_POINTS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | E-commerce feed
    |--------------------------------------------------------------------------
    | TTL (seconds) for the Redis-cached third-party product feed.
    */
    'store' => [
        'cache_ttl' => (int) env('CRM_STORE_CACHE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoicing
    |--------------------------------------------------------------------------
    */
    'invoice' => [
        'prefix' => env('CRM_INVOICE_PREFIX', 'INV'),
    ],
];
