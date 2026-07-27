<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Support\Models\Branch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Branch::query()
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ]);
    }
}
