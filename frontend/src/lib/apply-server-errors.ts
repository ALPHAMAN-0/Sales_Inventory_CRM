import type { FieldValues, Path, UseFormSetError } from 'react-hook-form';
import { ApiError } from './ApiError';

/**
 * Map a Laravel 422 bag onto RHF fields. Handles dot-path keys such as
 * `items.0.quantity` which RHF understands natively. Unknown keys collapse
 * onto the form root so nothing is silently lost.
 */
export function applyServerErrors<T extends FieldValues>(
  error: unknown,
  setError: UseFormSetError<T>,
): boolean {
  if (!(error instanceof ApiError) || !error.isValidation || !error.errors) {
    return false;
  }

  for (const [field, messages] of Object.entries(error.errors)) {
    setError(field as Path<T>, {
      type: 'server',
      message: messages[0],
    });
  }
  return true;
}
