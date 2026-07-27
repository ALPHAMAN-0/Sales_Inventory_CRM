import { useEffect } from 'react';
import { cn } from '@/lib/cn';
import { useToastStore, type Toast } from '@/stores/toast-store';

const tones: Record<Toast['type'], string> = {
  success: 'border-green-200 bg-green-50 text-green-800',
  error: 'border-red-200 bg-red-50 text-red-800',
  info: 'border-slate-200 bg-white text-slate-800',
};

function ToastRow({ toast }: { toast: Toast }) {
  const dismiss = useToastStore((s) => s.dismiss);

  useEffect(() => {
    const t = setTimeout(() => dismiss(toast.id), 4500);
    return () => clearTimeout(t);
  }, [toast.id, dismiss]);

  return (
    <div
      className={cn(
        'pointer-events-auto flex items-start gap-2 rounded-md border px-3 py-2 text-sm shadow-md',
        tones[toast.type],
      )}
      role="status"
    >
      <span className="flex-1">{toast.message}</span>
      <button
        onClick={() => dismiss(toast.id)}
        className="text-current opacity-50 hover:opacity-100"
        aria-label="Dismiss"
      >
        ×
      </button>
    </div>
  );
}

export function Toaster() {
  const toasts = useToastStore((s) => s.toasts);
  return (
    <div className="pointer-events-none fixed bottom-4 right-4 z-[60] flex w-80 flex-col gap-2">
      {toasts.map((t) => (
        <ToastRow key={t.id} toast={t} />
      ))}
    </div>
  );
}
