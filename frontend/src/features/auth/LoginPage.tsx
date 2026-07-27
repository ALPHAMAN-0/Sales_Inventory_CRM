import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Navigate, useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { FormField } from '@/components/ui/FormField';
import { applyServerErrors } from '@/lib/apply-server-errors';
import { ApiError } from '@/lib/ApiError';
import { useLogin, useSession } from './api';
import { loginSchema, type LoginValues } from './schema';

export function LoginPage() {
  const { data: user, isLoading } = useSession();
  const login = useLogin();
  const navigate = useNavigate();

  const {
    register,
    handleSubmit,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<LoginValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  });

  if (!isLoading && user) {
    return <Navigate to="/" replace />;
  }

  const onSubmit = handleSubmit(async (values) => {
    try {
      await login.mutateAsync(values);
      navigate('/', { replace: true });
    } catch (error) {
      if (!applyServerErrors(error, setError)) {
        setError('root', {
          message:
            error instanceof ApiError
              ? error.message
              : 'Something went wrong. Please try again.',
        });
      }
    }
  });

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-100 p-4">
      <div className="w-full max-w-sm">
        <div className="mb-6 text-center">
          <div className="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-brand-600 text-lg font-bold text-white">
            S
          </div>
          <h1 className="text-lg font-semibold text-slate-900">
            Sales, Inventory &amp; CRM
          </h1>
          <p className="text-sm text-slate-500">Sign in to continue</p>
        </div>

        <form
          onSubmit={onSubmit}
          className="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
        >
          {errors.root && (
            <div className="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
              {errors.root.message}
            </div>
          )}

          <FormField label="Email" htmlFor="email" error={errors.email?.message}>
            <Input
              id="email"
              type="email"
              autoComplete="username"
              invalid={!!errors.email}
              {...register('email')}
            />
          </FormField>

          <FormField
            label="Password"
            htmlFor="password"
            error={errors.password?.message}
          >
            <Input
              id="password"
              type="password"
              autoComplete="current-password"
              invalid={!!errors.password}
              {...register('password')}
            />
          </FormField>

          <Button type="submit" className="w-full" loading={isSubmitting}>
            Sign in
          </Button>

          <p className="text-center text-xs text-slate-400">
            Demo: admin@salescrm.test / password
          </p>
        </form>
      </div>
    </div>
  );
}
