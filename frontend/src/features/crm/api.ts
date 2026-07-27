import {
  keepPreviousData,
  useMutation,
  useQuery,
  useQueryClient,
} from '@tanstack/react-query';
import { api } from '@/lib/api-client';
import { queryKeys } from '@/lib/query-keys';
import type { Assignment, LostCustomer, Paginated, Wrapped } from '@/types/api';

export interface LostCustomerParams {
  page?: number;
  per_page?: number;
}

export function useLostCustomers(params: LostCustomerParams) {
  return useQuery({
    queryKey: queryKeys.lostCustomers(params),
    queryFn: async () => {
      const { data } = await api.get<Paginated<LostCustomer>>(
        '/crm/lost-customers',
        { params },
      );
      return data;
    },
    placeholderData: keepPreviousData,
  });
}

export function useAssignCustomer() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (vars: { customerId: number; employeeId: number }) => {
      const { data } = await api.post<Wrapped<Assignment>>(
        `/crm/customers/${vars.customerId}/assign`,
        { employee_id: vars.employeeId },
      );
      return data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: queryKeys.crm });
      qc.invalidateQueries({ queryKey: queryKeys.customers });
    },
  });
}

export interface CampaignResult {
  message: string;
  count: number;
}

export function useCampaign() {
  return useMutation({
    mutationFn: async (vars: { customerIds?: number[]; campaign?: string }) => {
      const { data } = await api.post<CampaignResult>('/crm/campaigns', {
        customer_ids: vars.customerIds,
        campaign: vars.campaign,
      });
      return data;
    },
  });
}
