import { Router } from 'express';
import { bookingsController } from '../controllers/bookings';
import { authMiddleware } from '../middleware/auth';
import { validateRequest } from '../middleware/validation';
import { z } from 'zod';

const router = Router();

// All booking routes require authentication
router.use(authMiddleware);

// Booking validation schemas
const CreateBookingSchema = z.object({
  equipmentId: z.string().uuid(),
  title: z.string().min(1).max(200),
  description: z.string().optional(),
  startTime: z.string().datetime(),
  endTime: z.string().datetime(),
  purpose: z.string().min(1),
  sampleType: z.string().max(100).optional(),
  numberOfSamples: z.number().int().min(1).optional(),
  specialRequirements: z.string().optional(),
  attachments: z.array(z.record(z.any())).optional()
}).refine((data) => new Date(data.endTime) > new Date(data.startTime), {
  message: "End time must be after start time",
  path: ["endTime"]
});

const UpdateBookingSchema = CreateBookingSchema.partial().extend({
  status: z.enum(['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show']).optional(),
  priority: z.enum(['low', 'normal', 'high', 'urgent']).optional(),
  notes: z.string().optional(),
  estimatedCost: z.number().optional(),
  actualCost: z.number().optional()
});

// Get all bookings with pagination and filtering
router.get('/', bookingsController.getBookings);

// Get booking statistics
router.get('/stats', bookingsController.getBookingStats);

// Get booking by ID
router.get('/:id', bookingsController.getBookingById);

// Create new booking
router.post('/',
  validateRequest(CreateBookingSchema),
  bookingsController.createBooking
);

// Update booking
router.put('/:id',
  validateRequest(UpdateBookingSchema),
  bookingsController.updateBooking
);

// Approve booking (staff only)
router.patch('/:id/approve',
  validateRequest(z.object({
    notes: z.string().optional()
  })),
  bookingsController.approveBooking
);

// Cancel booking
router.patch('/:id/cancel',
  validateRequest(z.object({
    reason: z.string().min(1)
  })),
  bookingsController.cancelBooking
);

export default router;