<?php

namespace App\Domain\Crm\Models;

use App\Domain\Sales\Models\Sale;
use App\Domain\Support\Models\Branch;
use App\Models\User;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'kpi_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function kpiEvents(): HasMany
    {
        return $this->hasMany(KpiEvent::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CustomerAssignment::class);
    }

    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }
}
