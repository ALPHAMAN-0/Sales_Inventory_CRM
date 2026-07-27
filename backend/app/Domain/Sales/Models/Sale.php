<?php

namespace App\Domain\Sales\Models;

use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\Employee;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Sales\Enums\SaleStatus;
use App\Domain\Support\Models\Branch;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => SaleStatus::class,
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'sold_at' => 'datetime',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    /**
     * Recompute money fields from the line items (queried, never stale) and
     * the configured tax rate. Chainable; caller persists with ->save().
     */
    public function recalculateTotals(): static
    {
        $subtotal = (float) $this->items()->sum('line_total');
        $tax = round($subtotal * (float) config('crm.tax_rate'), 2);

        $this->subtotal = $subtotal;
        $this->tax = $tax;
        $this->total = round($subtotal + $tax, 2);

        return $this;
    }

    protected static function newFactory(): SaleFactory
    {
        return SaleFactory::new();
    }
}
