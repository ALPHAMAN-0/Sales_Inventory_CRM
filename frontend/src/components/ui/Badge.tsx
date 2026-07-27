import type { ReactNode } from 'react';
import { cn } from '@/lib/cn';

type Tone = 'green' | 'red' | 'amber' | 'blue' | 'slate' | 'violet';

const tones: Record<Tone, string> = {
  green: 'bg-green-100 text-green-800',
  red: 'bg-red-100 text-red-800',
  amber: 'bg-amber-100 text-amber-800',
  blue: 'bg-blue-100 text-blue-800',
  slate: 'bg-slate-100 text-slate-700',
  violet: 'bg-violet-100 text-violet-800',
};

export function Badge({
  tone = 'slate',
  children,
}: {
  tone?: Tone;
  children: ReactNode;
}) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize',
        tones[tone],
      )}
    >
      {children}
    </span>
  );
}

/** Shared status → tone mappings so colours stay consistent across screens. */
export const customerStatusTone: Record<string, Tone> = {
  active: 'green',
  lost: 'red',
  recovered: 'violet',
};

export const assignmentStatusTone: Record<string, Tone> = {
  pending: 'amber',
  contacted: 'blue',
  recovered: 'green',
  expired: 'slate',
};

export const saleStatusTone: Record<string, Tone> = {
  completed: 'green',
  pending: 'amber',
  cancelled: 'red',
};
