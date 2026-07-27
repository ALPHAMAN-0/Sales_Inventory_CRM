import {
  keepPreviousData,
  useMutation,
  useQuery,
  useQueryClient,
} from '@tanstack/react-query';
import { api } from '@/lib/api-client';
import { queryKeys } from '@/lib/query-keys';
import type { Paginated, Product, Wrapped } from '@/types/api';

export interface ProductListParams {
  search?: string;
  active_only?: boolean;
  page?: number;
  per_page?: number;
}

export interface ProductInput {
  name: string;
  sku: string;
  price: number;
  is_active: boolean;
}

export function useProducts(params: ProductListParams) {
  return useQuery({
    queryKey: queryKeys.productList(params),
    queryFn: async () => {
      const { data } = await api.get<Paginated<Product>>('/products', {
        params,
      });
      return data;
    },
    placeholderData: keepPreviousData,
  });
}

export function useCreateProduct() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: ProductInput) => {
      const { data } = await api.post<Wrapped<Product>>('/products', input);
      return data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: queryKeys.products }),
  });
}

export function useUpdateProduct(id: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (input: ProductInput) => {
      const { data } = await api.put<Wrapped<Product>>(`/products/${id}`, input);
      return data.data;
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: queryKeys.products }),
  });
}

export function useDeleteProduct() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (id: number) => {
      await api.delete(`/products/${id}`);
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: queryKeys.products }),
  });
}
