import { Navigate, Outlet } from 'react-router-dom';
import { useSession } from '@/features/auth/api';
import type { Role } from '@/types/api';

export function RoleRoute({ role }: { role: Role }) {
  const { data: user } = useSession();

  if (user && user.role !== role) {
    return <Navigate to="/" replace />;
  }

  return <Outlet />;
}
