import { useState } from 'react';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card } from '@/components/ui/Card';
import { Input } from '@/components/ui/Input';
import { Pagination } from '@/components/ui/Pagination';
import { Spinner } from '@/components/ui/Spinner';
import { Table, TBody, Td, Th, THead } from '@/components/ui/Table';
import {
  EmptyState,
  ErrorState,
  PageHeader,
} from '@/components/ui/Page';
import { formatMoney } from '@/lib/format';
import { useSession } from '@/features/auth/api';
import { useBranchStore } from '@/stores/branch-store';
import { toast } from '@/stores/toast-store';
import type { Product } from '@/types/api';
import { useDeleteProduct, useProducts } from './api';
import { ProductFormModal } from './ProductFormModal';

function totalStock(p: Product): number {
  return (p.stock ?? []).reduce((sum, s) => sum + s.quantity, 0);
}

export function ProductsPage() {
  const { data: user } = useSession();
  const isAdmin = user?.role === 'admin';
  const branchId = useBranchStore((s) => s.selectedBranchId);

  const [search, setSearch] = useState('');
  const [activeOnly, setActiveOnly] = useState(false);
  const [page, setPage] = useState(1);
  const [editing, setEditing] = useState<Product | null>(null);
  const [modalOpen, setModalOpen] = useState(false);

  const { data, isLoading, isError, isFetching } = useProducts({
    search: search || undefined,
    active_only: activeOnly || undefined,
    page,
  });
  const del = useDeleteProduct();

  const openCreate = () => {
    setEditing(null);
    setModalOpen(true);
  };
  const openEdit = (p: Product) => {
    setEditing(p);
    setModalOpen(true);
  };

  const onDelete = (p: Product) => {
    if (!window.confirm(`Delete "${p.name}"? This cannot be undone.`)) return;
    del.mutate(p.id, {
      onSuccess: () => toast.success('Product deleted.'),
      onError: () => toast.error('Could not delete the product.'),
    });
  };

  return (
    <div>
      <PageHeader
        title="Products"
        subtitle="Catalog and per-branch stock. Prices apply to future sales only."
        actions={
          isAdmin ? <Button onClick={openCreate}>New product</Button> : undefined
        }
      />

      <div className="mb-4 flex flex-wrap items-center gap-3">
        <Input
          placeholder="Search by name…"
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
          className="max-w-xs"
        />
        <label className="flex items-center gap-2 text-sm text-slate-600">
          <input
            type="checkbox"
            checked={activeOnly}
            onChange={(e) => {
              setActiveOnly(e.target.checked);
              setPage(1);
            }}
          />
          Active only
        </label>
        {isFetching && <Spinner className="h-4 w-4 text-slate-400" />}
      </div>

      <Card>
        {isLoading ? (
          <div className="flex justify-center py-12">
            <Spinner className="h-6 w-6 text-brand-600" />
          </div>
        ) : isError ? (
          <div className="p-4">
            <ErrorState message="Could not load products." />
          </div>
        ) : !data || data.data.length === 0 ? (
          <EmptyState message="No products match your filters." />
        ) : (
          <>
            <Table>
              <THead>
                <tr>
                  <Th>Name</Th>
                  <Th>SKU</Th>
                  <Th align="right">Price</Th>
                  <Th align="right">Total stock</Th>
                  <Th align="right">This branch</Th>
                  <Th>Status</Th>
                  {isAdmin && <Th align="right">Actions</Th>}
                </tr>
              </THead>
              <TBody>
                {data.data.map((p) => {
                  const branchQty =
                    p.stock?.find((s) => s.branch_id === branchId)?.quantity ??
                    0;
                  return (
                    <tr key={p.id}>
                      <Td className="font-medium text-slate-900">{p.name}</Td>
                      <Td className="font-mono text-xs text-slate-500">
                        {p.sku}
                      </Td>
                      <Td align="right">{formatMoney(p.price)}</Td>
                      <Td align="right">{totalStock(p)}</Td>
                      <Td align="right">{branchQty}</Td>
                      <Td>
                        <Badge tone={p.is_active ? 'green' : 'slate'}>
                          {p.is_active ? 'active' : 'inactive'}
                        </Badge>
                      </Td>
                      {isAdmin && (
                        <Td align="right">
                          <div className="flex justify-end gap-1">
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => openEdit(p)}
                            >
                              Edit
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              className="text-red-600 hover:bg-red-50"
                              onClick={() => onDelete(p)}
                            >
                              Delete
                            </Button>
                          </div>
                        </Td>
                      )}
                    </tr>
                  );
                })}
              </TBody>
            </Table>
            <Pagination
              page={data.meta.current_page}
              lastPage={data.meta.last_page}
              total={data.meta.total}
              onPage={setPage}
            />
          </>
        )}
      </Card>

      <ProductFormModal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        product={editing}
      />
    </div>
  );
}
