import { useNavigate } from 'react-router-dom';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { useLogout, useSession } from '@/features/auth/api';
import { toast } from '@/stores/toast-store';
import { BranchSwitcher } from './BranchSwitcher';

export function Topbar() {
  const { data: user } = useSession();
  const logout = useLogout();
  const navigate = useNavigate();

  const onLogout = () => {
    logout.mutate(undefined, {
      onSuccess: () => {
        toast.info('Signed out.');
        navigate('/login', { replace: true });
      },
    });
  };

  return (
    <header className="flex h-14 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6">
      <BranchSwitcher />
      <div className="flex items-center gap-4">
        <div className="text-right">
          <div className="text-sm font-medium text-slate-800">
            {user?.name}
          </div>
          <div className="text-xs text-slate-500">{user?.email}</div>
        </div>
        {user?.role && (
          <Badge tone={user.role === 'admin' ? 'violet' : 'blue'}>
            {user.role}
          </Badge>
        )}
        <Button
          variant="secondary"
          size="sm"
          onClick={onLogout}
          loading={logout.isPending}
        >
          Sign out
        </Button>
      </div>
    </header>
  );
}
