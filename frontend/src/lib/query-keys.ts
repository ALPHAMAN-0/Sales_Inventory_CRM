// Hierarchical query keys. Invalidating a prefix (e.g. ['products']) cascades to
// every derived query — this is the app's cache-coherence mechanism after a sale.

export const queryKeys = {
  session: ['session'] as const,
  branches: ['branches'] as const,

  products: ['products'] as const,
  productList: (params: object) =>
    ['products', 'list', params] as const,
  product: (id: number) => ['products', 'detail', id] as const,

  sales: ['sales'] as const,
  saleList: (params: object) =>
    ['sales', 'list', params] as const,
  sale: (id: number) => ['sales', 'detail', id] as const,

  customers: ['customers'] as const,
  customerList: (params: object) =>
    ['customers', 'list', params] as const,
  customer: (id: number) => ['customers', 'detail', id] as const,

  crm: ['crm'] as const,
  lostCustomers: (params: object) =>
    ['crm', 'lost', params] as const,

  kpi: ['kpi'] as const,
  kpiDashboard: (params: object) =>
    ['kpi', 'dashboard', params] as const,
  kpiEmployee: (id: number) => ['kpi', 'employee', id] as const,
};
