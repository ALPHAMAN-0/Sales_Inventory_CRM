import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Button } from '@/components/ui/Button';
import { Input } from '@/components/ui/Input';
import { FormField } from '@/components/ui/FormField';
import { Modal } from '@/components/ui/Modal';
import { applyServerErrors } from '@/lib/apply-server-errors';
import { toast } from '@/stores/toast-store';
import type { Product } from '@/types/api';
import { useCreateProduct, useUpdateProduct } from './api';
import { productSchema, type ProductValues } from './schema';

interface Props {
  open: boolean;
  onClose: () => void;
  product?: Product | null;
}

export function ProductFormModal({ open, onClose, product }: Props) {
  const isEdit = !!product;
  const create = useCreateProduct();
  const update = useUpdateProduct(product?.id ?? 0);

  const {
    register,
    handleSubmit,
    reset,
    setError,
    formState: { errors, isSubmitting },
  } = useForm<ProductValues>({
    resolver: zodResolver(productSchema),
    defaultValues: { name: '', sku: '', price: 0, is_active: true },
  });

  useEffect(() => {
    if (open) {
      reset(
        product
          ? {
              name: product.name,
              sku: product.sku,
              price: product.price,
              is_active: product.is_active,
            }
          : { name: '', sku: '', price: 0, is_active: true },
      );
    }
  }, [open, product, reset]);

  const onSubmit = handleSubmit(async (values) => {
    try {
      if (isEdit) {
        await update.mutateAsync(values);
        toast.success('Product updated.');
      } else {
        await create.mutateAsync(values);
        toast.success('Product created.');
      }
      onClose();
    } catch (error) {
      if (!applyServerErrors(error, setError)) {
        toast.error('Could not save the product.');
      }
    }
  });

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={isEdit ? 'Edit product' : 'New product'}
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button type="submit" form="product-form" loading={isSubmitting}>
            {isEdit ? 'Save changes' : 'Create product'}
          </Button>
        </>
      }
    >
      <form id="product-form" onSubmit={onSubmit} className="space-y-4">
        <FormField label="Name" htmlFor="name" error={errors.name?.message}>
          <Input id="name" invalid={!!errors.name} {...register('name')} />
        </FormField>
        <FormField label="SKU" htmlFor="sku" error={errors.sku?.message}>
          <Input id="sku" invalid={!!errors.sku} {...register('sku')} />
        </FormField>
        <FormField label="Price" htmlFor="price" error={errors.price?.message}>
          <Input
            id="price"
            type="number"
            step="0.01"
            min="0"
            invalid={!!errors.price}
            {...register('price', { valueAsNumber: true })}
          />
        </FormField>
        <label className="flex items-center gap-2 text-sm text-slate-700">
          <input type="checkbox" {...register('is_active')} />
          Active (available for sale)
        </label>
      </form>
    </Modal>
  );
}
