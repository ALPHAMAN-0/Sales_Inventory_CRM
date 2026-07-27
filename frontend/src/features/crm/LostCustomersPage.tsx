import { useState } from 'react';
import {
  Badge,
  assignmentStatusTone,
} from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Pagination } from '@/components/ui/Pagination';
import { Spinner } from '@/components/ui/Spinner';
import { Table, TBody, Td, Th, THead } from '@/components/ui/Table';
import { EmptyState, ErrorState, PageHeader } from '@/components/ui/Page';
import { formatDate, formatMoney } from '@/lib/format';
import { toast } from '@/stores/toast-store';
import type { LostCustomer } from '@/types/api';
import { useCampaign, useLostCustomers } from './api';
import { AssignModal } from './AssignModal';

/** An open (still-active) assignment blocks re-assignment server-side. */
function hasOpenAssignment(c: LostCustomer): boolean {
  return (
    c.assignment?.status === 'pending' || c.assignment?.status === 'contacted'
  );
}

export function LostCustomersPage() {
  const [page, setPage] = useState(1);
  const [assignTarget, setAssignTarget] = useState<LostCustomer | null>(null);
  const [selected, setSelected] = useState<Set<number>>(new Set());

  const { data, isLoading, isError } = useLostCustomers({ page });
  const campaign = useCampaign();

  const toggle = (id: number) =>
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });

  const sendCampaign = () => {
    const ids = [...selected];
    campaign.mutate(
      {
        customerIds: ids.length > 0 ? ids : undefined,
        campaign: 'We miss you — here is 15% off your next order!',
      },
      {
        onSuccess: (res) => {
          toast.success(res.message);
          setSelected(new Set());
        },
        onError: () => toast.error('Could not queue the campaign.'),
      },
    );
  };

  return (
    <div>
      <PageHeader
        title="Lost Customers"
        subtitle="Assign a lost customer to an employee, then win them back."
        actions={
          <Button onClick={sendCampaign} loading={campaign.isPending}>
            {selected.size > 0
              ? `Re-engage ${selected.size} selected`
              : 'Re-engage all lost'}
          </Button>
        }
      />

      <Card>
        {isLoading ? (
          <div className="flex justify-center py-12">
            <Spinner className="h-6 w-6 text-brand-600" />
          </div>
        ) : isError ? (
          <div className="p-4">
            <ErrorState message="Could not load lost customers." />
          </div>
        ) : !data || data.data.length === 0 ? (
          <EmptyState message="No lost customers. Everyone's engaged!" />
        ) : (
          <>
            <Table>
              <THead>
                <tr>
                  <Th />
                  <Th>Name</Th>
                  <Th align="right">Days lost</Th>
                  <Th align="right">Orders</Th>
                  <Th align="right">Spent</Th>
                  <Th>Assignment</Th>
                  <Th align="right">Action</Th>
                </tr>
              </THead>
              <TBody>
                {data.data.map((c) => (
                  <tr key={c.id}>
                    <Td>
                      <input
                        type="checkbox"
                        checked={selected.has(c.id)}
                        onChange={() => toggle(c.id)}
                      />
                    </Td>
                    <Td className="font-medium text-slate-900">
                      {c.name}
                      <div className="text-xs font-normal text-slate-400">
                        last seen {formatDate(c.last_purchase_at)}
                      </div>
                    </Td>
                    <Td align="right">{c.days_since_last_purchase ?? '—'}</Td>
                    <Td align="right">{c.total_orders}</Td>
                    <Td align="right">{formatMoney(c.total_spent)}</Td>
                    <Td>
                      {c.assignment ? (
                        <div className="flex items-center gap-2">
                          <span className="text-sm text-slate-600">
                            {c.assignment.employee_name}
                          </span>
                          {c.assignment.status && (
                            <Badge
                              tone={assignmentStatusTone[c.assignment.status]}
                            >
                              {c.assignment.status}
                            </Badge>
                          )}
                        </div>
                      ) : (
                        <span className="text-sm text-slate-400">
                          Unassigned
                        </span>
                      )}
                    </Td>
                    <Td align="right">
                      <Button
                        size="sm"
                        variant="secondary"
                        disabled={hasOpenAssignment(c)}
                        onClick={() => setAssignTarget(c)}
                      >
                        {hasOpenAssignment(c) ? 'Assigned' : 'Assign'}
                      </Button>
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

      <AssignModal
        open={assignTarget !== null}
        onClose={() => setAssignTarget(null)}
        customer={assignTarget}
      />
    </div>
  );
}
