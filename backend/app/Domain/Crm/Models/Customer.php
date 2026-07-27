<?php

namespace App\Domain\Crm\Models;

use App\Domain\Sales\Models\Sale;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => \App\Domain\Crm\Enums\CustomerStatus::class,
            'first_purchase_at' => 'datetime',
            'last_purchase_at' => 'datetime',
            'total_orders' => 'integer',
            'total_spent' => 'decimal:2',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CustomerAssignment::class);
    }

    /** Route SMS notifications (re-engagement) to the customer's phone. */
    public function routeNotificationForVonage(object $notification): ?string
    {
        return $this->phone;
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
