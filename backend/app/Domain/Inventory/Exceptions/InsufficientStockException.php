<?php

namespace App\Domain\Inventory\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    /**
     * @param  array<int, array{product_id:int, product_name:string, requested:int, available:int}>  $shortages
     */
    public function __construct(public readonly array $shortages)
    {
        parent::__construct('Insufficient stock for one or more items.');
    }

    /**
     * Rendered as 409 Conflict with a machine-readable code + per-line
     * shortages so the SPA can flag the exact cart rows.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'code' => 'INSUFFICIENT_STOCK',
            'message' => $this->getMessage(),
            'shortages' => $this->shortages,
        ], 409);
    }
}
