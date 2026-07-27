import { QueryClient } from '@tanstack/react-query';
import { ApiError } from './ApiError';

/** Don't retry deterministic client errors — only transient/5xx failures. */
const NO_RETRY = new Set([400, 401, 403, 404, 409, 419, 422]);

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 30_000,
      refetchOnWindowFocus: false,
      retry: (failureCount, error) => {
        if (error instanceof ApiError && NO_RETRY.has(error.status)) {
          return false;
        }
        return failureCount < 2;
      },
    },
    mutations: {
      retry: false,
    },
  },
});
