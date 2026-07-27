import { useNavigate, useParams } from 'react-router-dom';
import { Badge, saleStatusTone } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card, CardBody, Stat } from '@/components/ui/Card';
import { Spinner } from '@/components/ui/Spinner';
import { Table, TBody, Td, Th, THead } from '@/components/ui/Table';
import { ErrorState, PageHeader } from '@/components/ui/Page';
import { formatDateTime, formatMoney } from '@/lib/format';
import { invoiceUrl, useSale } from './api';

export function SaleDetailPage() {
  const { id } = useParams();
  const saleId = Number(id);
  const navigate = useNavigate();
  const { data: sale, isLoading, isError } = useSale(saleId);

  if (isLoading) {
    return (
      <div className="flex justify-center py-16">
        <Spinner className="h-6 w-6 text-brand-600" />
      </div>
    );
  }
  if (isError || !sale) {
    return <ErrorState message="Sale not found." />;
  }

  return (
    <div>
      <PageHeader
        title={sale.invoice_number}
        subtitle={`Sold ${formatDateTime(sale.sold_at)}`}
        actions={
          <div className="flex gap-2">
            <Button variant="secondary" onClick={() => navigate('/sales')}>
              Back
            </Button>
            <a href={invoiceUrl(sale.id)} target="_blank" rel="noreferrer">
              <Button>Download invoice (PDF)</Button>
            </a>
          </div>
        }
      />

      <div className="mb-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <Stat label="Customer" value={sale.customer_name ?? '—'} />
        <Stat label="Employee" value={sale.employee_name ?? '—'} />
        <Stat
          label="Status"
          value={
            sale.status ? (
              <Badge tone={saleStatusTone[sale.status]}>{sale.status}</Badge>
            ) : (
              '—'
            )
          }
        />
        <Stat label="Total" value={formatMoney(sale.total)} />
      </div>

      <Card>
        <Table>
          <THead>
            <tr>
              <Th>Product</Th>
              <Th>SKU</Th>
              <Th align="right">Unit price</Th>
              <Th align="right">Qty</Th>
              <Th align="right">Line total</Th>
            </tr>
          </THead>
          <TBody>
            {(sale.items ?? []).map((item) => (
              <tr key={item.id}>
                <Td className="font-medium text-slate-900">
                  {item.product_name}
                </Td>
                <Td className="font-mono text-xs text-slate-500">
                  {item.sku}
                </Td>
                <Td align="right">{formatMoney(item.unit_price)}</Td>
                <Td align="right">{item.quantity}</Td>
                <Td align="right">{formatMoney(item.line_total)}</Td>
              </tr>
            ))}
          </TBody>
        </Table>
        <CardBody>
          <dl className="ml-auto max-w-xs space-y-1 text-sm">
            <div className="flex justify-between text-slate-500">
              <dt>Subtotal</dt>
              <dd className="tabular">{formatMoney(sale.subtotal)}</dd>
            </div>
            <div className="flex justify-between text-slate-500">
              <dt>Tax</dt>
              <dd className="tabular">{formatMoney(sale.tax)}</dd>
            </div>
            <div className="flex justify-between text-base font-semibold text-slate-900">
              <dt>Total</dt>
              <dd className="tabular">{formatMoney(sale.total)}</dd>
            </div>
          </dl>
        </CardBody>
      </Card>
    </div>
  );
}
