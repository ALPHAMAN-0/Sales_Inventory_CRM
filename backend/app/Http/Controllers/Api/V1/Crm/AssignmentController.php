<?php

namespace App\Http\Controllers\Api\V1\Crm;

use App\Domain\Crm\Actions\AssignCustomerAction;
use App\Domain\Crm\DTOs\AssignCustomerData;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\CustomerAssignment;
use App\Domain\Crm\Resources\AssignmentResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\AssignCustomerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AssignmentController extends Controller
{
    public function __construct(private readonly AssignCustomerAction $assign) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $assignments = CustomerAssignment::query()
            ->with(['customer', 'employee'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->integer('employee_id')))
            ->latest('assigned_at')
            ->paginate((int) $request->integer('per_page', 20));

        return AssignmentResource::collection($assignments);
    }

    public function store(AssignCustomerRequest $request, Customer $customer): JsonResponse
    {
        $assignment = $this->assign->execute(new AssignCustomerData(
            customerId: $customer->id,
            employeeId: (int) $request->validated('employee_id'),
            assignedBy: $request->user()->id,
        ));

        return (new AssignmentResource($assignment->load(['customer', 'employee'])))
            ->response()
            ->setStatusCode(201);
    }
}
