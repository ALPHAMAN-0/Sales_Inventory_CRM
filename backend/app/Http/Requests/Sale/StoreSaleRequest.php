<?php

namespace App\Http\Requests\Sale;

use App\Domain\Sales\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Sale::class);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            // Optional: defaults to the authenticated user's employee profile.
            'employee_id' => ['sometimes', 'integer', 'exists:employees,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
