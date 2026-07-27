import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '@/lib/api-client';
import { queryKeys } from '@/lib/query-keys';
import type { AuthUser, Wrapped } from '@/types/api';

export interface Credentials {
  email: string;
  password: string;
}

/** The session probe. `retry: false` so a 401 resolves quickly to "guest". */
export function useSession() {
  return useQuery({
    queryKey: queryKeys.session,
    queryFn: async () => {
      const { data } = await api.get<Wrapped<AuthUser>>('/user');
      return data.data;
    },
    retry: false,
    staleTime: 5 * 60_000,
  });
}

export function useLogin() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async (creds: Credentials) => {
      const { data } = await api.post<Wrapped<AuthUser>>('/login', creds);
      return data.data;
    },
    onSuccess: (user) => {
      qc.setQueryData(queryKeys.session, user);
    },
  });
}

export function useLogout() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: async () => {
      await api.post('/logout');
    },
    onSuccess: () => {
      qc.clear();
    },
  });
}
