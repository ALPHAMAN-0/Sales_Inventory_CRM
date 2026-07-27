<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Sales\Actions\CreateSaleAction;
use App\Domain\Sales\DTOs\CreateSaleData;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Resources\SaleResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\StoreSaleRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct(private readonly CreateSaleAction $createSale) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $sales = Sale::query()
            ->with(['customer', 'employee', 'items'])
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->latest('sold_at')
            ->paginate((int) $request->integer('per_page', 20));

        return SaleResource::collection($sales);
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $employeeId = $validated['employee_id'] ?? $request->user()->employee?->id;

        if (! $employeeId) {
            throw ValidationException::withMessages([
                'employee_id' => ['No employee profile for the current user; provide employee_id.'],
            ]);
        }

        $sale = $this->createSale->execute(CreateSaleData::fromArray([
            ...$validated,
            'employee_id' => $employeeId,
        ]));

        return (new SaleResource($sale))->response()->setStatusCode(201);
    }

    public function show(Sale $sale): SaleResource
    {
        return new SaleResource($sale->load(['items', 'customer', 'employee']));
    }

    public function invoice(Sale $sale): Response
    {
        $pdf = Pdf::loadView('invoices.invoice', [
            'sale' => $sale->load(['items.product', 'customer', 'employee', 'branch']),
        ]);

        return $pdf->stream("{$sale->invoice_number}.pdf");
    }
}
