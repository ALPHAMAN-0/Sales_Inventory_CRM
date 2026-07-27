import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AppProviders } from '@/app/providers';
import { ProtectedRoute } from '@/app/ProtectedRoute';
import { RoleRoute } from '@/app/RoleRoute';
import { AppLayout } from '@/app/layout/AppLayout';
import { LoginPage } from '@/features/auth/LoginPage';
import { ProductsPage } from '@/features/products/ProductsPage';
import { PosPage } from '@/features/sales/PosPage';
import { SalesPage } from '@/features/sales/SalesPage';
import { SaleDetailPage } from '@/features/sales/SaleDetailPage';
import { CustomersPage } from '@/features/customers/CustomersPage';
import { CustomerDetailPage } from '@/features/customers/CustomerDetailPage';
import { LostCustomersPage } from '@/features/crm/LostCustomersPage';
import { KpiDashboardPage } from '@/features/kpi/KpiDashboardPage';
import { EmployeeKpiPage } from '@/features/kpi/EmployeeKpiPage';

export default function App() {
  return (
    <AppProviders>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route element={<ProtectedRoute />}>
            <Route element={<AppLayout />}>
              <Route index element={<Navigate to="/pos" replace />} />
              <Route path="/pos" element={<PosPage />} />
              <Route path="/products" element={<ProductsPage />} />
              <Route path="/sales" element={<SalesPage />} />
              <Route path="/sales/:id" element={<SaleDetailPage />} />
              <Route path="/customers" element={<CustomersPage />} />
              <Route path="/customers/:id" element={<CustomerDetailPage />} />
              <Route path="/kpi" element={<KpiDashboardPage />} />
              <Route path="/kpi/:id" element={<EmployeeKpiPage />} />
              <Route element={<RoleRoute role="admin" />}>
                <Route path="/crm/lost" element={<LostCustomersPage />} />
              </Route>
            </Route>
          </Route>
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </BrowserRouter>
    </AppProviders>
  );
}
