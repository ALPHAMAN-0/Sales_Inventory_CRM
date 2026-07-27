import axios, {
  AxiosError,
  type AxiosInstance,
  type InternalAxiosRequestConfig,
} from 'axios';
import { ApiError } from './ApiError';

const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000';

/**
 * The Sanctum SPA client.
 *
 * - `withCredentials` sends the session + XSRF cookies cross-origin.
 * - `withXSRFToken` makes axios copy the (non-httpOnly) XSRF-TOKEN cookie into
 *   the X-XSRF-TOKEN header even for a cross-site request — required because
 *   :5173 and :8000 are different origins (same site).
 */
export const api: AxiosInstance = axios.create({
  baseURL: `${API_URL}/api/v1`,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
});

function hasXsrfCookie(): boolean {
  return document.cookie
    .split(';')
    .some((c) => c.trim().startsWith('XSRF-TOKEN='));
}

let csrfPromise: Promise<unknown> | null = null;

/** Fetch (once, de-duplicated) the CSRF cookie Laravel needs before a mutation. */
async function ensureCsrfCookie(): Promise<void> {
  if (hasXsrfCookie()) return;
  if (!csrfPromise) {
    csrfPromise = axios
      .get(`${API_URL}/sanctum/csrf-cookie`, { withCredentials: true })
      .finally(() => {
        csrfPromise = null;
      });
  }
  await csrfPromise;
}

const MUTATING = new Set(['post', 'put', 'patch', 'delete']);

api.interceptors.request.use(async (config: InternalAxiosRequestConfig) => {
  if (MUTATING.has((config.method ?? 'get').toLowerCase())) {
    await ensureCsrfCookie();
  }
  return config;
});

/** Notified on 401 so the auth store can drop the session and redirect. */
let onUnauthenticated: (() => void) | null = null;
export function setUnauthenticatedHandler(fn: () => void): void {
  onUnauthenticated = fn;
}

function normalize(error: AxiosError): ApiError {
  const response = error.response;
  if (!response) {
    return new ApiError(
      'Network error — could not reach the API.',
      0,
      { code: 'NETWORK' },
    );
  }

  const data = (response.data ?? {}) as Record<string, unknown>;
  const message =
    (typeof data.message === 'string' && data.message) ||
    error.message ||
    'Request failed.';

  return new ApiError(message, response.status, {
    code: typeof data.code === 'string' ? data.code : null,
    errors:
      response.status === 422
        ? ((data.errors as ApiError['errors']) ?? null)
        : null,
    shortages:
      response.status === 409
        ? ((data.shortages as ApiError['shortages']) ?? null)
        : null,
  });
}

api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const config = error.config as
      | (InternalAxiosRequestConfig & { _retried?: boolean })
      | undefined;

    // CSRF token expired: refresh the cookie once and replay the request.
    if (error.response?.status === 419 && config && !config._retried) {
      config._retried = true;
      csrfPromise = null;
      await ensureCsrfCookie();
      return api.request(config);
    }

    const apiError = normalize(error);

    // 401 anywhere except the session probe means the cookie died mid-session.
    if (apiError.status === 401 && !config?.url?.endsWith('/user')) {
      onUnauthenticated?.();
    }

    return Promise.reject(apiError);
  },
);
