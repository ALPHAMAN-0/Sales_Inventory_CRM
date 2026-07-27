import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Badge, customerStatusTone } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { Card, CardBody } from '@/components/ui/Card';
import { Input } from '@/components/ui/Input';
import { Spinner } from '@/components/ui/Spinner';
import { PageHeader } from '@/components/ui/Page';
import { ApiError } from '@/lib/ApiError';
import { formatMoney } from '@/lib/format';
import { queryClient } from '@/lib/query-client';
import { queryKeys } from '@/lib/query-keys';
import { useCustomers } from '@/features/customers/api';
import { useProducts } from '@/features/products/api';
import { useBranchStore } from '@/stores/branch-store';
import { useCartStore } from '@/stores/cart-store';
import { toast } from '@/stores/toast-store';
import type { Customer } from '@/types/api';
import { useCreateSale } from './api';

const TAX_RATE = 0.05; // preview only — the server total is authoritative.

export function PosPage() {
  const branchId = useBranchStore((s) => s.selectedBranchId);
  const navigate = useNavigate();

  const cart = useCartStore();
  const [productSearch, setProductSearch] = useState('');
  const [customerSearch, setCustomerSearch] = useState('');
  const [customer, setCustomer] = useState<Customer | null>(null);

  const products = useProducts({
    search: productSearch || undefined,
    active_only: true,
    per_page: 8,
  });
  const customers = useCustomers({
    search: customerSearch || undefined,
    per_page: 6,
  });
  const createSale = useCreateSale();

  const subtotal = cart.lines.reduce(
    (sum, l) => sum + l.unitPrice * l.quantity,
    0,
  );
  const tax = Math.round(subtotal * TAX_RATE * 100) / 100;
  const total = subtotal + tax;

  const branchStock = (branchList: { branch_id: number; quantity: number }[]) =>
    branchList.find((s) => s.branch_id === branchId)?.quantity ?? 0;

  const checkout = () => {
    if (!branchId) {
      toast.error('Select a branch first.');
      return;
    }
    if (!customer) {
      toast.error('Choose a customer.');
      return;
    }
    if (cart.lines.length === 0) {
      toast.error('Add at least one product.');
      return;
    }
    cart.clearShortages();

    createSale.mutate(
      {
        customer_id: customer.id,
        branch_id: branchId,
        items: cart.lines.map((l) => ({
          product_id: l.productId,
          quantity: l.quantity,
        })),
      },
      {
        onSuccess: (sale) => {
          toast.success(`Sale recorded — ${sale.invoice_number}`);
          cart.clear();
          setCustomer(null);
          navigate(`/sales/${sale.id}`);
        },
        onError: (error) => {
          if (error instanceof ApiError && error.isInsufficientStock) {
            cart.flagShortages(error.shortages ?? []);
            // Stock changed under us — refresh the catalog.
            queryClient.invalidateQueries({ queryKey: queryKeys.products });
            toast.error(
              'Not enough stock for one or more items. Adjust and retry.',
            );
          } else {
            toast.error(
              error instanceof ApiError ? error.message : 'Sale failed.',
            );
          }
        },
      },
    );
  };

  return (
    <div>
      <PageHeader
        title="Point of Sale"
        subtitle="Record a sale. Stock is deducted atomically — overselling is impossible."
      />

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-5">
        {/* Product picker */}
        <div className="lg:col-span-3">
          <Card>
            <div className="border-b border-slate-200 p-3">
              <Input
                placeholder="Search products…"
                value={productSearch}
                onChange={(e) => setProductSearch(e.target.value)}
              />
            </div>
            <div className="divide-y divide-slate-100">
              {products.isLoading ? (
                <div className="flex justify-center py-10">
                  <Spinner className="h-6 w-6 text-brand-600" />
                </div>
              ) : (
                products.data?.data.map((p) => {
                  const stock = branchStock(p.stock ?? []);
                  return (
                    <div
                      key={p.id}
                      className="flex items-center justify-between px-4 py-2.5"
                    >
                      <div>
                        <div className="text-sm font-medium text-slate-900">
                          {p.name}
                        </div>
                        <div className="font-mono text-xs text-slate-400">
                          {p.sku} · {formatMoney(p.price)} · {stock} in stock
                        </div>
                      </div>
                      <Button
                        size="sm"
                        variant="secondary"
                        disabled={stock <= 0}
                        onClick={() =>
                          cart.add({
                            productId: p.id,
                            name: p.name,
                            sku: p.sku,
                            unitPrice: p.price,
                            available: stock,
                          })
                        }
                      >
                        {stock <= 0 ? 'Out of stock' : 'Add'}
                      </Button>
                    </div>
                  );
                })
              )}
            </div>
          </Card>
        </div>

        {/* Cart + customer + checkout */}
        <div className="lg:col-span-2 space-y-4">
          <Card>
            <CardBody>
              <h3 className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Customer
              </h3>
              {customer ? (
                <div className="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2">
                  <div>
                    <div className="text-sm font-medium text-slate-900">
                      {customer.name}
                    </div>
                    <div className="text-xs text-slate-500">
                      {customer.email ?? 'no email'}
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    {customer.status && (
                      <Badge tone={customerStatusTone[customer.status]}>
                        {customer.status}
                      </Badge>
                    )}
                    <button
                      className="text-xs text-slate-400 hover:text-slate-600"
                      onClick={() => setCustomer(null)}
                    >
                      change
                    </button>
                  </div>
                </div>
              ) : (
                <div>
                  <Input
                    placeholder="Search customers…"
                    value={customerSearch}
                    onChange={(e) => setCustomerSearch(e.target.value)}
                  />
                  <div className="mt-2 max-h-40 divide-y divide-slate-100 overflow-y-auto">
                    {customers.data?.data.map((c) => (
                      <button
                        key={c.id}
                        onClick={() => {
                          setCustomer(c);
                          setCustomerSearch('');
                        }}
                        className="flex w-full items-center justify-between px-1 py-1.5 text-left text-sm hover:bg-slate-50"
                      >
                        <span>{c.name}</span>
                        {c.status && (
                          <Badge tone={customerStatusTone[c.status]}>
                            {c.status}
                          </Badge>
                        )}
                      </button>
                    ))}
                  </div>
                </div>
              )}
            </CardBody>
          </Card>

          <Card>
            <CardBody>
              <h3 className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Cart
              </h3>
              {cart.lines.length === 0 ? (
                <p className="py-4 text-center text-sm text-slate-400">
                  No items yet.
                </p>
              ) : (
                <div className="space-y-2">
                  {cart.lines.map((l) => {
                    const short = cart.shortages[l.productId];
                    return (
                      <div
                        key={l.productId}
                        className={`rounded-md border px-2 py-2 ${
                          short !== undefined
                            ? 'border-red-300 bg-red-50'
                            : 'border-slate-200'
                        }`}
                      >
                        <div className="flex items-center justify-between">
                          <div className="text-sm font-medium text-slate-800">
                            {l.name}
                          </div>
                          <button
                            className="text-xs text-slate-400 hover:text-red-600"
                            onClick={() => cart.remove(l.productId)}
                          >
                            remove
                          </button>
                        </div>
                        <div className="mt-1 flex items-center justify-between">
                          <div className="flex items-center gap-1">
                            <Button
                              variant="secondary"
                              size="sm"
                              onClick={() =>
                                cart.setQuantity(l.productId, l.quantity - 1)
                              }
                            >
                              −
                            </Button>
                            <span className="w-8 text-center text-sm tabular">
                              {l.quantity}
                            </span>
                            <Button
                              variant="secondary"
                              size="sm"
                              onClick={() =>
                                cart.setQuantity(l.productId, l.quantity + 1)
                              }
                            >
                              +
                            </Button>
                          </div>
                          <div className="text-sm tabular text-slate-700">
                            {formatMoney(l.unitPrice * l.quantity)}
                          </div>
                        </div>
                        {short !== undefined && (
                          <p className="mt-1 text-xs text-red-600">
                            Only {short} available at this branch.
                          </p>
                        )}
                      </div>
                    );
                  })}
                </div>
              )}

              <dl className="mt-3 space-y-1 border-t border-slate-200 pt-3 text-sm">
                <div className="flex justify-between text-slate-500">
                  <dt>Subtotal</dt>
                  <dd className="tabular">{formatMoney(subtotal)}</dd>
                </div>
                <div className="flex justify-between text-slate-500">
                  <dt>Tax (5%)</dt>
                  <dd className="tabular">{formatMoney(tax)}</dd>
                </div>
                <div className="flex justify-between text-base font-semibold text-slate-900">
                  <dt>Total</dt>
                  <dd className="tabular">{formatMoney(total)}</dd>
                </div>
              </dl>

              <Button
                className="mt-4 w-full"
                loading={createSale.isPending}
                disabled={cart.lines.length === 0 || !customer}
                onClick={checkout}
              >
                Complete sale
              </Button>
            </CardBody>
          </Card>
        </div>
      </div>
    </div>
  );
}
