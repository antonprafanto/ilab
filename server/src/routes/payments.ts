import { Router } from 'express';
import { paymentsController } from '../controllers/payments';
import { authMiddleware } from '../middleware/auth';
import { validateRequest } from '../middleware/validation';
import { z } from 'zod';

const router = Router();

// All payment routes require authentication
router.use(authMiddleware);

// Payment validation schemas
const CreatePaymentSchema = z.object({
  bookingId: z.string().uuid(),
  subtotal: z.number().min(0),
  tax: z.number().min(0).default(0),
  discount: z.number().min(0).default(0),
  totalAmount: z.number().min(0),
  paymentMethod: z.enum(['cash', 'bank_transfer', 'credit_card', 'digital_wallet', 'check']).optional(),
  paymentReference: z.string().optional(),
  dueDate: z.string().date(),
  notes: z.string().optional(),
  billTo: z.object({
    name: z.string(),
    email: z.string().email(),
    phone: z.string().optional(),
    address: z.string().optional(),
    organization: z.string().optional()
  }),
  items: z.array(z.object({
    description: z.string().min(1),
    quantity: z.number().min(0),
    unitPrice: z.number().min(0),
    totalPrice: z.number().min(0),
    category: z.enum(['equipment_usage', 'sample_analysis', 'consultation', 'training', 'other'])
  }))
});

const UpdatePaymentSchema = CreatePaymentSchema.partial().extend({
  status: z.enum(['pending', 'paid', 'partial', 'overdue', 'cancelled', 'refunded']).optional(),
  paidAmount: z.number().min(0).optional(),
  remainingAmount: z.number().min(0).optional(),
  paidAt: z.string().datetime().optional()
});

const CreatePaymentRecordSchema = z.object({
  paymentId: z.string().uuid(),
  amount: z.number().min(0),
  method: z.enum(['cash', 'bank_transfer', 'credit_card', 'digital_wallet', 'check']),
  reference: z.string().optional(),
  notes: z.string().optional()
});

// Get all payments with pagination and filtering
router.get('/', paymentsController.getPayments);

// Get payment statistics
router.get('/stats', paymentsController.getPaymentStats);

// Get payment by ID
router.get('/:id', paymentsController.getPaymentById);

// Create new payment (admin/staff only)
router.post('/',
  validateRequest(CreatePaymentSchema),
  paymentsController.createPayment
);

// Update payment (admin/staff only)
router.put('/:id',
  validateRequest(UpdatePaymentSchema),
  paymentsController.updatePayment
);

// Create payment record (admin/staff only)
router.post('/records',
  validateRequest(CreatePaymentRecordSchema),
  paymentsController.createPaymentRecord
);

export default router;