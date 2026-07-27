import { z } from 'zod';

export const productSchema = z.object({
  name: z.string().min(1, 'Name is required').max(255),
  sku: z.string().min(1, 'SKU is required').max(64),
  price: z
    .number({ message: 'Price is required' })
    .positive('Price must be greater than 0'),
  is_active: z.boolean(),
});

export type ProductValues = z.infer<typeof productSchema>;
