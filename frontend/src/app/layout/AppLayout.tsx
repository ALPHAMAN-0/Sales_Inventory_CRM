import { useEffect } from 'react';
import { Outlet } from 'react-router-dom';
import { setUnauthenticatedHandler } from '@/lib/api-client';
import { queryClient } from '@/lib/query-client';
import { queryKeys } from '@/lib/query-keys';
import { Sidebar } from './Sidebar';
import { Topbar } from './Topbar';

export function AppLayout() {
  useEffect(() => {
    // A mid-session 401 drops the cached session so ProtectedRoute bounces to /login.
    setUnauthenticatedHandler(() => {
      queryClient.setQueryData(queryKeys.session, null);
    });
    return () => setUnauthenticatedHandler(() => {});
  }, []);

  return (
    <div className="flex h-screen overflow-hidden">
      <Sidebar />
      <div className="flex flex-1 flex-col overflow-hidden">
        <Topbar />
        <main className="flex-1 overflow-y-auto p-6">
          <div className="mx-auto max-w-6xl">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
}
