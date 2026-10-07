<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Page size for list endpoints, clamped to 1..$max. The query builder
     * treats a negative LIMIT as "no limit", so an unchecked per_page=-1 (or a
     * huge value) would return a whole table in one response.
     */
    protected function perPage(Request $request, int $default = 20, int $max = 100): int
    {
        $perPage = $request->integer('per_page', $default);

        return $perPage < 1 ? $default : min($perPage, $max);
    }
}
