import { useNavigate, useParams } from 'react-router-dom';
import { Button } from '@/components/ui/Button';
import { Card, Stat } from '@/components/ui/Card';
import { Spinner } from '@/components/ui/Spinner';
import { Table, TBody, Td, Th, THead } from '@/components/ui/Table';
import { EmptyState, ErrorState, PageHeader } from '@/components/ui/Page';
import { formatDateTime } from '@/lib/format';
import { useKpiEmployee } from './api';

export function EmployeeKpiPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { data, isLoading, isError } = useKpiEmployee(Number(id));

  if (isLoading) {
    return (
      <div className="flex justify-center py-16">
        <Spinner className="h-6 w-6 text-brand-600" />
      </div>
    );
  }
  if (isError || !data) {
    return <ErrorState message="Employee not found." />;
  }

  const { employee, events } = data;

  return (
    <div>
      <PageHeader
        title={employee.employee_name}
        subtitle="Recovery credits are append-only — the KPI score is their sum."
        actions={
          <Button variant="secondary" onClick={() => navigate('/kpi')}>
            Back to leaderboard
          </Button>
        }
      />

      <div className="mb-5 grid grid-cols-3 gap-4">
        <Stat label="KPI score" value={employee.kpi_score} />
        <Stat label="Customers recovered" value={employee.recovered_customers} />
        <Stat label="Sales handled" value={employee.sales_count} />
      </div>

      <h2 className="mb-2 text-sm font-semibold text-slate-700">
        Recovery ledger
      </h2>
      <Card>
        {events.length === 0 ? (
          <EmptyState message="No recovery credits yet." />
        ) : (
          <Table>
            <THead>
              <tr>
                <Th>Customer</Th>
                <Th>Reason</Th>
                <Th align="right">Points</Th>
                <Th>Sale</Th>
                <Th>When</Th>
              </tr>
            </THead>
            <TBody>
              {events.map((e) => (
                <tr key={e.id}>
                  <Td className="font-medium text-slate-900">
                    {e.customer_name ?? '—'}
                  </Td>
                  <Td className="capitalize text-slate-500">
                    {e.reason?.replace(/_/g, ' ') ?? '—'}
                  </Td>
                  <Td align="right" className="font-semibold text-green-700">
                    +{e.points}
                  </Td>
                  <Td>
                    {e.sale_id ? (
                      <button
                        className="text-brand-700 hover:underline"
                        onClick={() => navigate(`/sales/${e.sale_id}`)}
                      >
                        #{e.sale_id}
                      </button>
                    ) : (
                      '—'
                    )}
                  </Td>
                  <Td className="text-slate-500">
                    {formatDateTime(e.occurred_at)}
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
