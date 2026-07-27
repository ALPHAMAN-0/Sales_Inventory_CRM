<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Resources\CustomerResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = Customer::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate((int) $request->integer('per_page', 20));

        return CustomerResource::collection($customers);
    }

    public function show(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer->load(['sales' => fn ($q) => $q->latest('sold_at'), 'sales.items']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $customer = Customer::create([...$data, 'status' => 'active']);

        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }
}
