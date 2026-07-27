import { useQuery } from '@tanstack/react-query';
import { api } from '@/lib/api-client';
import { queryKeys } from '@/lib/query-keys';
import type { Branch, Wrapped } from '@/types/api';

export function useBranches() {
  return useQuery({
    queryKey: queryKeys.branches,
    queryFn: async () => {
      const { data } = await api.get<Wrapped<Branch[]>>('/branches');
      return data.data;
    },
    staleTime: 10 * 60_000,
  });
}
