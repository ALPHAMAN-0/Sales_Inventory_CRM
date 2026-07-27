import type { StockShortage } from '@/types/api';

/** Laravel 422 validation bag: field path -> messages. */
export type ValidationErrors = Record<string, string[]>;

/**
 * Normalized error thrown by the axios response interceptor. Feature hooks and
 * forms branch on `status` / `code` rather than poking at axios internals.
 */
export class ApiError extends Error {
  status: number;
  code: string | null;
  errors: ValidationErrors | null;
  shortages: StockShortage[] | null;

  constructor(
    message: string,
    status: number,
    options: {
      code?: string | null;
      errors?: ValidationErrors | null;
      shortages?: StockShortage[] | null;
    } = {},
  ) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.code = options.code ?? null;
    this.errors = options.errors ?? null;
    this.shortages = options.shortages ?? null;
  }

  get isValidation(): boolean {
    return this.status === 422 && this.errors !== null;
  }

  get isInsufficientStock(): boolean {
    return this.status === 409 && this.code === 'INSUFFICIENT_STOCK';
  }
}
