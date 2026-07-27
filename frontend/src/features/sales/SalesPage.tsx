import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Badge, saleStatusTone } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Pagination } from '@/components/ui/Pagination';
import { Spinner } from '@/components/ui/Spinner';
import { Table, TBody, Td, Th, THead } from '@/components/ui/Table';
import { EmptyState, ErrorState, PageHeader } from '@/components/ui/Page';
import { formatDateTime, formatMoney } from '@/lib/format';
import { useSales } from './api';

export function SalesPage() {
  const navigate = useNavigate();
  const [page, setPage] = useState(1);
  const { data, isLoading, isError } = useSales({ page });

  return (
    <div>
      <PageHeader
        title="Sales"
        subtitle="Every completed transaction, newest first."
        actions={<Button onClick={() => navigate('/pos')}>New sale</Button>}
      />

      <Card>
        {isLoading ? (
          <div className="flex justify-center py-12">
            <Spinner className="h-6 w-6 text-brand-600" />
          </div>
        ) : isError ? (
          <div className="p-4">
            <ErrorState message="Could not load sales." />
          </div>
        ) : !data || data.data.length === 0 ? (
          <EmptyState message="No sales recorded yet." />
        ) : (
          <>
            <Table>
              <THead>
                <tr>
                  <Th>Invoice</Th>
                  <Th>Customer</Th>
                  <Th>Employee</Th>
                  <Th align="right">Total</Th>
                  <Th>Status</Th>
                  <Th>Sold at</Th>
                </tr>
              </THead>
              <TBody>
                {data.data.map((s) => (
                  <tr
                    key={s.id}
                    className="cursor-pointer hover:bg-slate-50"
                    onClick={() => navigate(`/sales/${s.id}`)}
                  >
                    <Td className="font-mono text-xs font-medium text-brand-700">
                      {s.invoice_number}
                    </Td>
                    <Td>{s.customer_name ?? '—'}</Td>
                    <Td>{s.employee_name ?? '—'}</Td>
                    <Td align="right">{formatMoney(s.total)}</Td>
                    <Td>
                      {s.status && (
                        <Badge tone={saleStatusTone[s.status]}>
                          {s.status}
                        </Badge>
                      )}
                    </Td>
                    <Td className="text-slate-500">
                      {formatDateTime(s.sold_at)}
                    </Td>
                  </tr>
                ))}
              </TBody>
            </Table>
            <Pagination
              page={data.meta.current_page}
              lastPage={data.meta.last_page}
              total={data.meta.total}
              onPage={setPage}
            />
          </>
        )}
      </Card>
    </div>
  );
}
