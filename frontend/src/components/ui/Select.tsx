import { forwardRef, type SelectHTMLAttributes } from 'react';
import { cn } from '@/lib/cn';

interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
  invalid?: boolean;
}

export const Select = forwardRef<HTMLSelectElement, SelectProps>(
  ({ className, invalid, children, ...props }, ref) => (
    <select
      ref={ref}
      className={cn(
        'block w-full rounded-md border-0 bg-white px-3 py-2 text-sm text-slate-900 ring-1 ring-inset focus:ring-2 focus:ring-inset focus:ring-brand-500 disabled:bg-slate-50',
        invalid ? 'ring-red-400' : 'ring-slate-300',
        className,
      )}
      {...props}
    >
      {children}
    </select>
  ),
);
Select.displayName = 'Select';
