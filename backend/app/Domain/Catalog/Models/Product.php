<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Sales\Models\SaleItem;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
