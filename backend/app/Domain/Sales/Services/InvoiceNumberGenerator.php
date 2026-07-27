<?php

namespace App\Domain\Sales\Services;

use App\Domain\Sales\Models\InvoiceSequence;

/**
 * Generates gap-free, globally-unique invoice numbers like INV-2026-01-000042.
 *
 * The sequence is per branch+year (invoice_sequences is keyed that way), so the
 * BRANCH must be encoded in the string — otherwise two branches would each start
 * their year at 000001 and collide on the global-unique sales.invoice_number.
 *
 * MUST be called inside the sale's DB transaction: it locks the per
 * branch+year sequence row so two concurrent sales can't mint the same number.
 */
class InvoiceNumberGenerator
{
    public function next(int $branchId): string
    {
        $year = (int) now()->format('Y');

        $sequence = InvoiceSequence::query()
            ->where('branch_id', $branchId)
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            InvoiceSequence::query()->firstOrCreate(
                ['branch_id' => $branchId, 'year' => $year],
                ['last_number' => 0],
            );

            $sequence = InvoiceSequence::query()
                ->where('branch_id', $branchId)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();
        }

        $sequence->last_number++;
        $sequence->save();

        return sprintf(
            '%s-%d-%02d-%06d',
            config('crm.invoice.prefix', 'INV'),
            $year,
            $branchId,
            $sequence->last_number,
        );
    }
}
