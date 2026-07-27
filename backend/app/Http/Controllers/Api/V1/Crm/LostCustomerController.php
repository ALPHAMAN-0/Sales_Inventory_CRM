<?php

namespace App\Http\Controllers\Api\V1\Crm;

use App\Domain\Crm\Enums\CustomerStatus;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Resources\LostCustomerResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LostCustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = Customer::query()
            ->where('status', CustomerStatus::Lost->value)
            ->with(['assignments.employee'])
            ->orderBy('last_purchase_at')
            ->paginate((int) $request->integer('per_page', 20));

        return LostCustomerResource::collection($customers);
    }
}
