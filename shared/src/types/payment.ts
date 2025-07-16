import { z } from 'zod';

export enum PaymentStatus {
  PENDING = 'pending',
  PAID = 'paid',
  PARTIAL = 'partial',
  OVERDUE = 'overdue',
  CANCELLED = 'cancelled',
  REFUNDED = 'refunded'
}

export enum PaymentMethod {
  CASH = 'cash',
  BANK_TRANSFER = 'bank_transfer',
  CREDIT_CARD = 'credit_card',
  DIGITAL_WALLET = 'digital_wallet',
  CHECK = 'check'
}

export const PaymentItemSchema = z.object({
  description: z.string(),
  quantity: z.number().positive(),
  unitPrice: z.number().positive(),
  totalPrice: z.number().positive(),
  category: z.enum(['equipment_usage', 'sample_analysis', 'consultation', 'training', 'other'])
});

export const PaymentSchema = z.object({
  id: z.string().uuid(),
  bookingId: z.string().uuid(),
  invoiceNumber: z.string(),
  userId: z.string().uuid(),
  items: z.array(PaymentItemSchema),
  subtotal: z.number().positive(),
  tax: z.number().min(0),
  discount: z.number().min(0),
  totalAmount: z.number().positive(),
  paidAmount: z.number().min(0),
  remainingAmount: z.number().min(0),
  status: z.nativeEnum(PaymentStatus),
  paymentMethod: z.nativeEnum(PaymentMethod).optional(),
  paymentReference: z.string().optional(),
  dueDate: z.date(),
  paidAt: z.date().optional(),
  notes: z.string().optional(),
  billTo: z.object({
    name: z.string(),
    address: z.string(),
    city: z.string(),
    postalCode: z.string(),
    country: z.string(),
    taxId: z.string().optional(),
    email: z.string().email(),
    phone: z.string().optional()
  }),
  createdBy: z.string().uuid(),
  approvedBy: z.string().uuid().optional(),
  approvedAt: z.date().optional(),
  createdAt: z.date(),
  updatedAt: z.date()
});

export type Payment = z.infer<typeof PaymentSchema>;
export type PaymentItem = z.infer<typeof PaymentItemSchema>;

export const CreatePaymentSchema = PaymentSchema.omit({
  id: true,
  invoiceNumber: true,
  remainingAmount: true,
  paidAt: true,
  approvedBy: true,
  approvedAt: true,
  createdAt: true,
  updatedAt: true
}).extend({
  status: z.nativeEnum(PaymentStatus).default(PaymentStatus.PENDING)
});

export type CreatePayment = z.infer<typeof CreatePaymentSchema>;

export const UpdatePaymentSchema = PaymentSchema.partial().omit({
  id: true,
  invoiceNumber: true,
  bookingId: true,
  userId: true,
  createdBy: true,
  createdAt: true
});

export type UpdatePayment = z.infer<typeof UpdatePaymentSchema>;

export const PaymentRecordSchema = z.object({
  id: z.string().uuid(),
  paymentId: z.string().uuid(),
  amount: z.number().positive(),
  method: z.nativeEnum(PaymentMethod),
  reference: z.string().optional(),
  notes: z.string().optional(),
  processedBy: z.string().uuid(),
  processedAt: z.date(),
  createdAt: z.date()
});

export type PaymentRecord = z.infer<typeof PaymentRecordSchema>;