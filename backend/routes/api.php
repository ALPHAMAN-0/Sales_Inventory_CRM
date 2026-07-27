<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\Crm\AssignmentController;
use App\Http\Controllers\Api\V1\Crm\CampaignController;
use App\Http\Controllers\Api\V1\Crm\LostCustomerController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\KpiDashboardController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\Store\ProductFeedController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ---- Public ----
    Route::post('login', [AuthController::class, 'login']);

    // ---- Authenticated SPA (Sanctum session cookie) ----
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'me']);

        // Reference data
        Route::get('branches', [BranchController::class, 'index']);

        // Catalog
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);

        // Sales
        Route::get('sales', [SaleController::class, 'index']);
        Route::post('sales', [SaleController::class, 'store']);
        Route::get('sales/{sale}', [SaleController::class, 'show']);
        Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice']);

        // Customers
        Route::get('customers', [CustomerController::class, 'index']);
        Route::post('customers', [CustomerController::class, 'store']);
        Route::get('customers/{customer}', [CustomerController::class, 'show']);

        // CRM
        Route::get('crm/lost-customers', [LostCustomerController::class, 'index']);
        Route::get('crm/assignments', [AssignmentController::class, 'index']);
        Route::post('crm/customers/{customer}/assign', [AssignmentController::class, 'store']);
        Route::post('crm/campaigns', [CampaignController::class, 'store']);

        // KPI
        Route::get('kpi/dashboard', [KpiDashboardController::class, 'index']);
        Route::get('kpi/employees/{employee}', [KpiDashboardController::class, 'show']);
    });

    // ---- Third-party e-commerce feed (token ability + dedicated throttle) ----
    Route::middleware(['auth:sanctum', 'ability:store:read', 'throttle:store'])
        ->prefix('store')
        ->group(function () {
            Route::get('products', [ProductFeedController::class, 'index']);
            Route::get('products/{sku}', [ProductFeedController::class, 'show']);
        });
});
