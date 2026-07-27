import {
  keepPreviousData,
  useMutation,
  useQuery,
  useQueryClient,
} from '@tanstack/react-query';
import { api } from '@/lib/api-client';
import { queryKeys } from '@/lib/query-keys';
import type {
  Customer,
  CustomerStatus,
  Paginated,
  Wrapped,
} from '@/types/api';

export interface CustomerListParams {
  status?: CustomerStatus | '';
  search?: string;
  page?: number;
  per_page?: number;
}

export interface CustomerInput {
  name: string;
  email?: string | null;
  phone?: string | null;
}

export function useCustomers(params: CustomerListParams) {
  return useQuery({
    queryKey: queryKeys.customerList(params),
    queryFn: async () => {
      const { data } = await api.get<Paginated<Customer>>('/customers', {
        params,
      });
      return data;
    },
    placeholderData: keepPreviousData,
  });
}

export function useCustomer(id: number) {
  return useQuery({
    queryKey: queryKeys.customer(id),
    queryFn: async () => {
      const { data } = await api.get<Wrapped<Customer>>(`/customers/${id}`);
      return data.data;
    },
    enabled: Number.isFinite(id) && id > 0,
  });
}

export function useCreateCustomer() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: CustomerInput) => {
      const { data } = await api.post<Wrapped<Customer>>('/customers', input);
      return data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: queryKeys.customers }),
  });
}
