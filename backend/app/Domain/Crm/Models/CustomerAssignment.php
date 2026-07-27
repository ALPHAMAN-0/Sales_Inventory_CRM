<?php

namespace App\Domain\Crm\Models;

use App\Domain\Crm\Enums\AssignmentStatus;
use App\Domain\Sales\Models\Sale;
use App\Models\User;
use Database\Factories\CustomerAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerAssignment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => AssignmentStatus::class,
            'assigned_at' => 'datetime',
            'recovered_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function kpiEvents(): HasMany
    {
        return $this->hasMany(KpiEvent::class, 'assignment_id');
    }

    protected static function newFactory(): CustomerAssignmentFactory
    {
        return CustomerAssignmentFactory::new();
    }
}
