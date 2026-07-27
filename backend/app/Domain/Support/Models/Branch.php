<?php

namespace App\Domain\Support\Models;

use App\Domain\Crm\Models\Employee;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Sales\Models\Sale;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    protected static function newFactory(): BranchFactory
    {
        return BranchFactory::new();
    }
}
