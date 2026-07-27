import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Card } from '@/components/ui/Card';
import { Select } from '@/components/ui/Select';
import { Spinner } from '@/components/ui/Spinner';
import { Table, TBody, Td, Th, THead } from '@/components/ui/Table';
import { EmptyState, ErrorState, PageHeader } from '@/components/ui/Page';
import { useBranches } from '@/features/branches/api';
import { useKpiDashboard } from './api';

const medal = ['🥇', '🥈', '🥉'];

export function KpiDashboardPage() {
  const navigate = useNavigate();
  const [branchId, setBranchId] = useState<number | ''>('');
  const { data: branches } = useBranches();
  const { data, isLoading, isError } = useKpiDashboard({
    branch_id: branchId === '' ? undefined : Number(branchId),
  });

  return (
    <div>
      <PageHeader
        title="KPI Leaderboard"
        subtitle="Recovery points update live — each won-back customer is worth points."
        actions={
          <Select
            value={branchId}
            onChange={(e) =>
              setBranchId(e.target.value ? Number(e.target.value) : '')
            }
            className="w-44"
          >
            <option value="">All branches</option>
            {branches?.map((b) => (
              <option key={b.id} value={b.id}>
                {b.name}
              </option>
            ))}
          </Select>
        }
      />

      <Card>
        {isLoading ? (
          <div className="flex justify-center py-12">
            <Spinner className="h-6 w-6 text-brand-600" />
          </div>
        ) : isError ? (
          <div className="p-4">
            <ErrorState message="Could not load the leaderboard." />
          </div>
        ) : !data || data.length === 0 ? (
          <EmptyState message="No employees to rank yet." />
        ) : (
          <Table>
            <THead>
              <tr>
                <Th align="center">Rank</Th>
                <Th>Employee</Th>
                <Th>Branch</Th>
                <Th align="right">Recovered</Th>
                <Th align="right">Sales</Th>
                <Th align="right">KPI score</Th>
              </tr>
            </THead>
            <TBody>
              {data.map((emp, i) => (
                <tr
                  key={emp.employee_id}
                  className="cursor-pointer hover:bg-slate-50"
                  onClick={() => navigate(`/kpi/${emp.employee_id}`)}
                >
                  <Td align="center" className="text-lg">
                    {medal[i] ?? emp.rank ?? i + 1}
                  </Td>
                  <Td className="font-medium text-slate-900">
                    {emp.employee_name}
                  </Td>
                  <Td className="text-slate-500">{emp.branch_name ?? '—'}</Td>
                  <Td align="right">{emp.recovered_customers}</Td>
                  <Td align="right">{emp.sales_count}</Td>
                  <Td align="right" className="font-semibold text-brand-700">
                    {emp.kpi_score}
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
