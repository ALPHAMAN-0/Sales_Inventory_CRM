<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 13px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #6b7280; }
        .meta { margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; }
        td.num, th.num { text-align: right; }
        tfoot td { font-weight: bold; border-bottom: none; }
    </style>
</head>
<body>
    <h1>Invoice {{ $sale->invoice_number }}</h1>
    <div class="muted">Sold {{ optional($sale->sold_at)->format('Y-m-d H:i') }}</div>

    <div class="meta">
        <strong>Billed to:</strong> {{ $sale->customer?->name }}
        @if ($sale->customer?->email) &lt;{{ $sale->customer->email }}&gt; @endif<br>
        <strong>Branch:</strong> {{ $sale->branch?->name }}<br>
        <strong>Served by:</strong> {{ $sale->employee?->name }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="num">Qty</th>
                <th class="num">Unit price</th>
                <th class="num">Line total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? '—' }} <span class="muted">({{ $item->product?->sku }})</span></td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="num">{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="num">Subtotal</td><td class="num">{{ number_format((float) $sale->subtotal, 2) }}</td></tr>
            <tr><td colspan="3" class="num">Tax</td><td class="num">{{ number_format((float) $sale->tax, 2) }}</td></tr>
            <tr><td colspan="3" class="num">Total</td><td class="num">{{ number_format((float) $sale->total, 2) }}</td></tr>
        </tfoot>
    </table>
</body>
</html>
