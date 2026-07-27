<?php

namespace App\Domain\Crm\Services;

use App\Domain\Crm\Enums\AssignmentStatus;
use App\Domain\Crm\Enums\CustomerStatus;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Models\CustomerAssignment;
use App\Domain\Sales\Enums\SaleStatus;
use App\Domain\Sales\Models\Sale;

class CustomerLifecycleService
{
    /**
     * Recompute the denormalized purchase aggregates from the sales ledger
     * (idempotent — safe under queue retries, never double-counts) and advance
     * the lifecycle state machine.
     */
    public function syncPurchaseStats(Customer $customer, Sale $sale): void
    {
        $stats = Sale::query()
            ->where('customer_id', $customer->id)
            ->where('status', SaleStatus::Completed->value)
            ->selectRaw('COUNT(*) AS c, COALESCE(SUM(total), 0) AS s, MIN(sold_at) AS f, MAX(sold_at) AS l')
            ->first();

        $customer->total_orders = (int) $stats->c;
        $customer->total_spent = (float) $stats->s;
        $customer->first_purchase_at = $stats->f;
        $customer->last_purchase_at = $stats->l;

        // Lost -> Recovered if the customer was in a recovery flow (open
        // assignment, OR one already closed by THIS sale — order-independent
        // w.r.t. the CreditRecoveryKpi listener); otherwise Lost -> Active.
        if ($customer->status === CustomerStatus::Lost) {
            $customer->status = $this->wasInRecovery($customer, $sale)
                ? CustomerStatus::Recovered
                : CustomerStatus::Active;
        }

        $customer->save();
    }

    public function markLost(Customer $customer): void
    {
        $customer->update(['status' => CustomerStatus::Lost]);
    }

    private function wasInRecovery(Customer $customer, Sale $sale): bool
    {
        $openValues = array_map(fn ($s) => $s->value, AssignmentStatus::open());

        return CustomerAssignment::query()
            ->where('customer_id', $customer->id)
            ->where(function ($query) use ($openValues, $sale) {
                $query->whereIn('status', $openValues)
                    ->orWhere('sale_id', $sale->id);
            })
            ->exists();
    }
}
