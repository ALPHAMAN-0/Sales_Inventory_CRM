<?php

namespace App\Http\Controllers\Api\V1\Crm;

use App\Domain\Crm\Enums\CustomerStatus;
use App\Domain\Crm\Models\Customer;
use App\Domain\Crm\Notifications\ReengagementOffer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class CampaignController extends Controller
{
    /**
     * Fire a re-engagement campaign (email/SMS, queued) at lost customers —
     * all of them, or a selected subset. Admin-only.
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'customer_ids' => ['sometimes', 'array'],
            'customer_ids.*' => ['integer', 'exists:customers,id'],
            'campaign' => ['sometimes', 'string', 'max:255'],
        ]);

        $customers = Customer::query()
            ->where('status', CustomerStatus::Lost->value)
            ->when(! empty($data['customer_ids']), fn ($q) => $q->whereIn('id', $data['customer_ids']))
            ->get();

        Notification::send($customers, new ReengagementOffer($data['campaign'] ?? 'We miss you!'));

        return response()->json([
            'message' => "Re-engagement queued for {$customers->count()} customer(s).",
            'count' => $customers->count(),
        ]);
    }
}
