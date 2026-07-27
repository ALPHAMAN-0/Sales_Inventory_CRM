<?php

namespace App\Http\Requests\Crm;

use App\Domain\Crm\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class AssignCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign', Customer::class);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
        ];
    }
}
