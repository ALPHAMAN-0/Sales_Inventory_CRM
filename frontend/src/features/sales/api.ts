import {
  keepPreviousData,
  useMutation,
  useQuery,
  useQueryClient,
} from '@tanstack/react-query';
import { api } from '@/lib/api-client';
import { queryKeys } from '@/lib/query-keys';
import type {
  CreateSaleInput,
  Paginated,
  Sale,
  Wrapped,
} from '@/types/api';

export interface SaleListParams {
  customer_id?: number;
  branch_id?: number;
  page?: number;
  per_page?: number;
}

export function useSales(params: SaleListParams) {
  return useQuery({
    queryKey: queryKeys.saleList(params),
    queryFn: async () => {
      const { data } = await api.get<Paginated<Sale>>('/sales', { params });
      return data;
    },
    placeholderData: keepPreviousData,
  });
}

export function useSale(id: number) {
  return useQuery({
    queryKey: queryKeys.sale(id),
    queryFn: async () => {
      const { data } = await api.get<Wrapped<Sale>>(`/sales/${id}`);
      return data.data;
    },
    enabled: Number.isFinite(id) && id > 0,
  });
}

/**
 * The keystone mutation. On success we invalidate every domain the sale
 * touches — stock counts, the sales list, customer lifecycle, and the KPI
 * leaderboard all re-fetch, so a recovery shows up live without any event bus.
 */
export function useCreateSale() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: CreateSaleInput) => {
      const { data } = await api.post<Wrapped<Sale>>('/sales', input);
      return data.data;
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: queryKeys.products });
      qc.invalidateQueries({ queryKey: queryKeys.sales });
      qc.invalidateQueries({ queryKey: queryKeys.customers });
      qc.invalidateQueries({ queryKey: queryKeys.crm });
      qc.invalidateQueries({ queryKey: queryKeys.kpi });
    },
  });
}

export function invoiceUrl(saleId: number): string {
  const base = import.meta.env.VITE_API_URL ?? 'http://localhost:8000';
  return `${base}/api/v1/sales/${saleId}/invoice`;
}
