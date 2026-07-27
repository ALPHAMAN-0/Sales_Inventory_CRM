<?php

namespace Database\Factories;

use App\Domain\Crm\Enums\KpiReason;
use App\Domain\Crm\Models\Employee;
use App\Domain\Crm\Models\KpiEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KpiEvent>
 */
class KpiEventFactory extends Factory
{
    protected $model = KpiEvent::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'points' => 10,
            'reason' => KpiReason::CustomerRecovery,
        ];
    }
}
