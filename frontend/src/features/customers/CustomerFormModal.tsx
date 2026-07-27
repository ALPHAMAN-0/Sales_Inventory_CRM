import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { FormField } from '@/components/ui/FormField';
import { Modal } from '@/components/ui/Modal';
import { applyServerErrors } from '@/lib/apply-server-errors';
import { toast } from '@/stores/toast-store';
import { useCreateCustomer } from './api';
import { customerSchema, type CustomerValues } from './schema';

interface Props {
  open: boolean;
  onClose: () => void;
}

export function CustomerFormModal({ open, onClose }: Props) {
  const create = useCreateCustomer();

  const {
    register,
    handleSubmit,
    reset,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<CustomerValues>({
    resolver: zodResolver(customerSchema),
    defaultValues: { name: '', email: '', phone: '' },
  });

  useEffect(() => {
    if (open) reset({ name: '', email: '', phone: '' });
  }, [open, reset]);

  const onSubmit = handleSubmit(async (values) => {
    try {
      await create.mutateAsync({
        name: values.name,
        email: values.email || null,
        phone: values.phone || null,
      });
      toast.success('Customer created.');
      onClose();
    } catch (error) {
      if (!applyServerErrors(error, setError)) {
        toast.error('Could not create the customer.');
      }
    }
  });

  return (
    <Modal
      open={open}
      onClose={onClose}
      title="New customer"
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button type="submit" form="customer-form" loading={isSubmitting}>
            Create customer
          </Button>
        </>
      }
    >
      <form id="customer-form" onSubmit={onSubmit} className="space-y-4">
        <FormField label="Name" htmlFor="c-name" error={errors.name?.message}>
          <Input id="c-name" invalid={!!errors.name} {...register('name')} />
        </FormField>
        <FormField
          label="Email"
          htmlFor="c-email"
          error={errors.email?.message}
          hint="Optional — used for invoices and re-engagement."
        >
          <Input
            id="c-email"
            type="email"
            invalid={!!errors.email}
            {...register('email')}
          />
        </FormField>
        <FormField label="Phone" htmlFor="c-phone" error={errors.phone?.message}>
          <Input id="c-phone" invalid={!!errors.phone} {...register('phone')} />
        </FormField>
      </form>
    </Modal>
  );
}
