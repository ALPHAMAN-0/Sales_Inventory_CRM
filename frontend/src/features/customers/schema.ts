import { z } from 'zod';

export const customerSchema = z.object({
  name: z.string().min(1, 'Name is required').max(255),
  email: z
    .union([z.literal(''), z.string().email('Enter a valid email')])
    .optional(),
  phone: z.union([z.literal(''), z.string().max(32)]).optional(),
});

export type CustomerValues = z.infer<typeof customerSchema>;
