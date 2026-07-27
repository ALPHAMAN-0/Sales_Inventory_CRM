import { Button } from './Button';

interface PaginationProps {
  page: number;
  lastPage: number;
  total: number;
  onPage: (page: number) => void;
}

export function Pagination({ page, lastPage, total, onPage }: PaginationProps) {
  if (total === 0) return null;
  return (
    <div className="flex items-center justify-between border-t border-slate-200 px-3 py-2 text-sm text-slate-500">
      <span>
        Page {page} of {lastPage} · {total} total
      </span>
      <div className="flex gap-2">
        <Button
          variant="secondary"
          size="sm"
          disabled={page <= 1}
          onClick={() => onPage(page - 1)}
        >
          Previous
        </Button>
        <Button
          variant="secondary"
          size="sm"
          disabled={page >= lastPage}
          onClick={() => onPage(page + 1)}
        >
          Next
        </Button>
      </div>
    </div>
  );
}
