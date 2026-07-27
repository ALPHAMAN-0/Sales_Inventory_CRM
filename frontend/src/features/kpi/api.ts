import { useQuery } from '@tanstack/react-query';
import { api } from '@/lib/api-client';
import { queryKeys } from '@/lib/query-keys';
import type { EmployeeKpi, EmployeeKpiDetail, Wrapped } from '@/types/api';

export interface KpiDashboardParams {
  branch_id?: number;
}

export function useKpiDashboard(params: KpiDashboardParams) {
  return useQuery({
    queryKey: queryKeys.kpiDashboard(params),
    queryFn: async () => {
      const { data } = await api.get<Wrapped<EmployeeKpi[]>>('/kpi/dashboard', {
        params,
      });
      return data.data;
    },
    // Recovery KPI is credited by a queued listener a beat after the sale
    // commits; a gentle poll lets the leaderboard reflect it live while open.
    refetchInterval: 5000,
  });
}

export function useKpiEmployee(id: number) {
  return useQuery({
    queryKey: queryKeys.kpiEmployee(id),
    queryFn: async () => {
      const { data } = await api.get<Wrapped<EmployeeKpiDetail>>(
        `/kpi/employees/${id}`,
      );
      return data.data;
    },
    enabled: Number.isFinite(id) && id > 0,
  });
}
