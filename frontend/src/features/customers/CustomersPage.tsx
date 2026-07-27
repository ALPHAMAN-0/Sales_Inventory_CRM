import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Badge, customerStatusTone } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Input } from '@/components/ui/Input';
import { Pagination } from '@/components/ui/Pagination';
import { Select } from '@/components/ui/Select';
import { Spinner } from '@/components/ui/Spinner';
import { Table, TBody, Td, Th, THead } from '@/components/ui/Table';
import { EmptyState, ErrorState, PageHeader } from '@/components/ui/Page';
import { formatDate, formatMoney } from '@/lib/format';
import type { CustomerStatus } from '@/types/api';
import { useCustomers } from './api';
import { CustomerFormModal } from './CustomerFormModal';

export function CustomersPage() {
  const navigate = useNavigate();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState<CustomerStatus | ''>('');
  const [page, setPage] = useState(1);
  const [modalOpen, setModalOpen] = useState(false);

  const { data, isLoading, isError } = useCustomers({
    search: search || undefined,
    status: status || undefined,
    page,
  });

  return (
    <div>
      <PageHeader
        title="Customers"
        subtitle="Lifecycle is derived from purchase history — active, lost, recovered."
        actions={<Button onClick={() => setModalOpen(true)}>New customer</Button>}
      />

      <div className="mb-4 flex flex-wrap items-center gap-3">
        <Input
          placeholder="Search by name…"
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
          className="max-w-xs"
        />
        <Select
          value={status}
          onChange={(e) => {
            setStatus(e.target.value as CustomerStatus | '');
            setPage(1);
          }}
          className="w-44"
        >
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="lost">Lost</option>
          <option value="recovered">Recovered</option>
        </Select>
      </div>

      <Card>
        {isLoading ? (
          <div className="flex justify-center py-12">
            <Spinner className="h-6 w-6 text-brand-600" />
          </div>
        ) : isError ? (
          <div className="p-4">
            <ErrorState message="Could not load customers." />
          </div>
        ) : !data || data.data.length === 0 ? (
          <EmptyState message="No customers match your filters." />
        ) : (
          <>
            <Table>
              <THead>
                <tr>
                  <Th>Name</Th>
                  <Th>Email</Th>
                  <Th>Status</Th>
                  <Th align="right">Orders</Th>
                  <Th align="right">Spent</Th>
                  <Th>Last purchase</Th>
                </tr>
              </THead>
              <TBody>
                {data.data.map((c) => (
                  <tr
                    key={c.id}
                    className="cursor-pointer hover:bg-slate-50"
                    onClick={() => navigate(`/customers/${c.id}`)}
                  >
                    <Td className="font-medium text-slate-900">{c.name}</Td>
                    <Td className="text-slate-500">{c.email ?? '—'}</Td>
                    <Td>
                      {c.status && (
                        <Badge tone={customerStatusTone[c.status]}>
                          {c.status}
                        </Badge>
                      )}
                    </Td>
                    <Td align="right">{c.total_orders}</Td>
                    <Td align="right">{formatMoney(c.total_spent)}</Td>
                    <Td className="text-slate-500">
                      {formatDate(c.last_purchase_at)}
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

      <CustomerFormModal open={modalOpen} onClose={() => setModalOpen(false)} />
    </div>
  );
}
