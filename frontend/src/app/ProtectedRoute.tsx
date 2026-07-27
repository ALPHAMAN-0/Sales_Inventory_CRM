import { Navigate, Outlet } from 'react-router-dom';
import { useSession } from '@/features/auth/api';
import { Spinner } from '@/components/ui/Spinner';

export function ProtectedRoute() {
  const { data: user, isLoading } = useSession();

  if (isLoading) {
    return (
      <div className="flex h-screen items-center justify-center">
        <Spinner className="h-8 w-8 text-brand-600" />
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  return <Outlet />;
}
