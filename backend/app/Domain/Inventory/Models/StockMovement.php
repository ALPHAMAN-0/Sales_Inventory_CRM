<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Support\Models\Branch;
use App\Models\User;
use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    // Append-only ledger: created_at only, never updated.
    const UPDATED_AT = null;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'quantity' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): StockMovementFactory
    {
        return StockMovementFactory::new();
    }
}
