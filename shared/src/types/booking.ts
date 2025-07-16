import { z } from 'zod';

export enum BookingStatus {
  PENDING = 'pending',
  CONFIRMED = 'confirmed',
  IN_PROGRESS = 'in_progress',
  COMPLETED = 'completed',
  CANCELLED = 'cancelled',
  NO_SHOW = 'no_show'
}

export enum BookingPriority {
  LOW = 'low',
  NORMAL = 'normal',
  HIGH = 'high',
  URGENT = 'urgent'
}

const BaseBookingSchema = z.object({
  id: z.string().uuid(),
  userId: z.string().uuid(),
  equipmentId: z.string().uuid(),
  title: z.string().min(1),
  description: z.string().optional(),
  startTime: z.date(),
  endTime: z.date(),
  status: z.nativeEnum(BookingStatus),
  priority: z.nativeEnum(BookingPriority),
  purpose: z.string(),
  sampleType: z.string().optional(),
  numberOfSamples: z.number().positive().optional(),
  specialRequirements: z.string().optional(),
  estimatedCost: z.number().positive().optional(),
  actualCost: z.number().positive().optional(),
  notes: z.string().optional(),
  approvedBy: z.string().uuid().optional(),
  approvedAt: z.date().optional(),
  cancelledBy: z.string().uuid().optional(),
  cancelledAt: z.date().optional(),
  cancellationReason: z.string().optional(),
  attachments: z.array(z.object({
    name: z.string(),
    url: z.string(),
    type: z.string()
  })).optional(),
  createdAt: z.date(),
  updatedAt: z.date()
});

export const BookingSchema = BaseBookingSchema.refine((data) => data.endTime > data.startTime, {
  message: "End time must be after start time",
  path: ["endTime"]
});

export type Booking = z.infer<typeof BaseBookingSchema>;

export const CreateBookingSchema = BaseBookingSchema.omit({
  id: true,
  status: true,
  estimatedCost: true,
  actualCost: true,
  approvedBy: true,
  approvedAt: true,
  cancelledBy: true,
  cancelledAt: true,
  createdAt: true,
  updatedAt: true
}).extend({
  status: z.nativeEnum(BookingStatus).default(BookingStatus.PENDING)
}).refine((data) => data.endTime > data.startTime, {
  message: "End time must be after start time",
  path: ["endTime"]
});

export type CreateBooking = z.infer<typeof CreateBookingSchema>;

export const UpdateBookingSchema = BaseBookingSchema.partial().omit({
  id: true,
  userId: true,
  createdAt: true
});

export type UpdateBooking = z.infer<typeof UpdateBookingSchema>;

export const BookingQuerySchema = z.object({
  userId: z.string().uuid().optional(),
  equipmentId: z.string().uuid().optional(),
  status: z.nativeEnum(BookingStatus).optional(),
  startDate: z.date().optional(),
  endDate: z.date().optional(),
  page: z.number().positive().default(1),
  limit: z.number().positive().max(100).default(10)
});

export type BookingQuery = z.infer<typeof BookingQuerySchema>;