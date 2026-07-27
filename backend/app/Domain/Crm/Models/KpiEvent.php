<?php

namespace App\Domain\Crm\Models;

use App\Domain\Crm\Enums\KpiReason;
use App\Domain\Sales\Models\Sale;
use Database\Factories\KpiEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiEvent extends Model
{
    use HasFactory;

    // Append-only ledger: created_at only, never updated.
    const UPDATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'reason' => KpiReason::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(CustomerAssignment::class, 'assignment_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    protected static function newFactory(): KpiEventFactory
    {
        return KpiEventFactory::new();
    }
}
