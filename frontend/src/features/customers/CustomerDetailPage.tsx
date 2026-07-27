import { useNavigate, useParams } from 'react-router-dom';
import { Badge, customerStatusTone, saleStatusTone } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card, Stat } from '@/components/ui/Card';
import { Spinner } from '@/components/ui/Spinner';
import { Table, TBody, Td, Th, THead } from '@/components/ui/Table';
import { EmptyState, ErrorState, PageHeader } from '@/components/ui/Page';
import { formatDate, formatDateTime, formatMoney } from '@/lib/format';
import { useCustomer } from './api';

export function CustomerDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { data: customer, isLoading, isError } = useCustomer(Number(id));

  if (isLoading) {
    return (
      <div className="flex justify-center py-16">
        <Spinner className="h-6 w-6 text-brand-600" />
      </div>
    );
  }
  if (isError || !customer) {
    return <ErrorState message="Customer not found." />;
  }

  const orders = customer.orders ?? [];

  return (
    <div>
      <PageHeader
        title={customer.name}
        subtitle={customer.email ?? 'No email on file'}
        actions={
          <div className="flex items-center gap-3">
            {customer.status && (
              <Badge tone={customerStatusTone[customer.status]}>
                {customer.status}
              </Badge>
            )}
            <Button variant="secondary" onClick={() => navigate('/customers')}>
              Back
            </Button>
          </div>
        }
      />

      <div className="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <Stat label="Total orders" value={customer.total_orders} />
        <Stat label="Total spent" value={formatMoney(customer.total_spent)} />
        <Stat
          label="First purchase"
          value={formatDate(customer.first_purchase_at)}
        />
        <Stat
          label="Last purchase"
          value={formatDate(customer.last_purchase_at)}
          hint={
            customer.purchase_frequency_days
              ? `~${customer.purchase_frequency_days}d cadence`
              : undefined
          }
        />
      </div>

      <h2 className="mb-2 text-sm font-semibold text-slate-700">
        Purchase history
      </h2>
      <Card>
        {orders.length === 0 ? (
          <EmptyState message="No purchases recorded." />
        ) : (
          <Table>
            <THead>
              <tr>
                <Th>Invoice</Th>
                <Th align="right">Total</Th>
                <Th>Status</Th>
                <Th>Date</Th>
              </tr>
            </THead>
            <TBody>
              {orders.map((s) => (
                <tr
                  key={s.id}
                  className="cursor-pointer hover:bg-slate-50"
                  onClick={() => navigate(`/sales/${s.id}`)}
                >
                  <Td className="font-mono text-xs font-medium text-brand-700">
                    {s.invoice_number}
                  </Td>
                  <Td align="right">{formatMoney(s.total)}</Td>
                  <Td>
                    {s.status && (
                      <Badge tone={saleStatusTone[s.status]}>{s.status}</Badge>
                    )}
                  </Td>
                  <Td className="text-slate-500">
                    {formatDateTime(s.sold_at)}
                  </Td>
                </tr>
              ))}
            </TBody>
          </Table>
        )}
      </Card>
    </div>
  );
}
