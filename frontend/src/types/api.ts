// TypeScript mirror of the Laravel API Resources. Money fields are JSON
// numbers (Resources cast decimals to float); timestamps are ISO strings.

export type Role = 'admin' | 'employee';

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: Role | null;
  branch_id: number | null;
  employee_id: number | null;
}

export interface Branch {
  id: number;
  name: string;
  code: string;
}

export interface ProductStock {
  branch_id: number;
  branch_name: string | null;
  quantity: number;
}

export interface Product {
  id: number;
  name: string;
  sku: string;
  price: number;
  is_active: boolean;
  stock?: ProductStock[];
  created_at: string | null;
  updated_at: string | null;
}

export interface SaleItem {
  id: number;
  product_id: number;
  product_name: string | null;
  sku: string | null;
  quantity: number;
  unit_price: number;
  line_total: number;
}

export type SaleStatus = 'pending' | 'completed' | 'cancelled';

export interface Sale {
  id: number;
  invoice_number: string;
  customer_id: number | null;
  customer_name: string | null;
  employee_id: number | null;
  employee_name: string | null;
  branch_id: number | null;
  items?: SaleItem[];
  subtotal: number;
  tax: number;
  total: number;
  status: SaleStatus | null;
  sold_at: string | null;
  created_at: string | null;
}

export type CustomerStatus = 'active' | 'lost' | 'recovered';

export interface Customer {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
  status: CustomerStatus | null;
  total_orders: number;
  total_spent: number;
  first_purchase_at: string | null;
  last_purchase_at: string | null;
  purchase_frequency_days: number | null;
  orders?: Sale[];
}

export type AssignmentStatus = 'pending' | 'contacted' | 'recovered' | 'expired';

export interface LostCustomerAssignment {
  id: number;
  employee_id: number;
  employee_name: string | null;
  status: AssignmentStatus | null;
  assigned_at: string | null;
}

export interface LostCustomer {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
  status: CustomerStatus | null;
  last_purchase_at: string | null;
  days_since_last_purchase: number | null;
  total_orders: number;
  total_spent: number;
  assignment: LostCustomerAssignment | null;
}

export interface Assignment {
  id: number;
  customer_id: number;
  customer_name: string | null;
  employee_id: number;
  employee_name: string | null;
  status: AssignmentStatus | null;
  assigned_at: string | null;
  recovered_at: string | null;
  sale_id: number | null;
}

export interface EmployeeKpi {
  employee_id: number;
  employee_name: string;
  branch_id: number | null;
  branch_name: string | null;
  kpi_score: number;
  rank: number | null;
  recovered_customers: number;
  sales_count: number;
}

export interface KpiEventEntry {
  id: number;
  points: number;
  reason: string | null;
  customer_name: string | null;
  sale_id: number | null;
  occurred_at: string | null;
}

export interface EmployeeKpiDetail {
  employee: {
    employee_id: number;
    employee_name: string;
    branch_id: number | null;
    kpi_score: number;
    recovered_customers: number;
    sales_count: number;
  };
  events: KpiEventEntry[];
}

// ---- Envelopes ----

export interface Wrapped<T> {
  data: T;
}

export interface Paginated<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
  };
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
}

// ---- Request payloads ----

export interface SaleLineInput {
  product_id: number;
  quantity: number;
}

export interface CreateSaleInput {
  customer_id: number;
  employee_id?: number;
  branch_id: number;
  items: SaleLineInput[];
}

export interface StockShortage {
  product_id: number;
  product_name: string;
  requested: number;
  available: number;
}
