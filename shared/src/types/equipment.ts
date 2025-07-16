import { z } from 'zod';

export enum EquipmentType {
  GC_MS = 'gc_ms',
  LC_MS = 'lc_ms',
  AAS = 'aas',
  FTIR = 'ftir',
  PCR = 'pcr',
  FREEZE_DRYER = 'freeze_dryer',
  HPLC = 'hplc',
  SPECTROPHOTOMETER = 'spectrophotometer',
  MICROSCOPE = 'microscope',
  CENTRIFUGE = 'centrifuge',
  INCUBATOR = 'incubator',
  OTHER = 'other'
}

export enum EquipmentStatus {
  AVAILABLE = 'available',
  IN_USE = 'in_use',
  MAINTENANCE = 'maintenance',
  OUT_OF_ORDER = 'out_of_order',
  RESERVED = 'reserved'
}

export const EquipmentSpecificationSchema = z.object({
  brand: z.string(),
  model: z.string(),
  serialNumber: z.string().optional(),
  specifications: z.record(z.string()).optional(),
  capabilities: z.array(z.string()).optional(),
  limitations: z.array(z.string()).optional()
});

export const EquipmentSchema = z.object({
  id: z.string().uuid(),
  name: z.string().min(1),
  type: z.nativeEnum(EquipmentType),
  description: z.string().optional(),
  specifications: EquipmentSpecificationSchema,
  status: z.nativeEnum(EquipmentStatus),
  location: z.string(),
  responsiblePerson: z.string(),
  contactInfo: z.string().optional(),
  bookingRules: z.object({
    maxHoursPerSession: z.number().positive(),
    maxSessionsPerDay: z.number().positive(),
    advanceBookingDays: z.number().positive(),
    minimumNoticeHours: z.number().positive(),
    allowedRoles: z.array(z.string())
  }),
  pricing: z.object({
    pricePerHour: z.number().positive(),
    pricePerSample: z.number().positive().optional(),
    setupFee: z.number().optional(),
    discounts: z.array(z.object({
      userType: z.string(),
      percentage: z.number().min(0).max(100)
    })).optional()
  }),
  images: z.array(z.string()).optional(),
  documents: z.array(z.object({
    name: z.string(),
    url: z.string(),
    type: z.enum(['sop', 'manual', 'safety', 'other'])
  })).optional(),
  maintenanceSchedule: z.object({
    lastMaintenance: z.date().optional(),
    nextMaintenance: z.date(),
    maintenanceInterval: z.number().positive()
  }).optional(),
  calibrationSchedule: z.object({
    lastCalibration: z.date().optional(),
    nextCalibration: z.date(),
    calibrationInterval: z.number().positive()
  }).optional(),
  createdAt: z.date(),
  updatedAt: z.date()
});

export type Equipment = z.infer<typeof EquipmentSchema>;
export type EquipmentSpecification = z.infer<typeof EquipmentSpecificationSchema>;

export const CreateEquipmentSchema = EquipmentSchema.omit({
  id: true,
  createdAt: true,
  updatedAt: true
});

export type CreateEquipment = z.infer<typeof CreateEquipmentSchema>;

export const UpdateEquipmentSchema = EquipmentSchema.partial().omit({
  id: true,
  createdAt: true
});

export type UpdateEquipment = z.infer<typeof UpdateEquipmentSchema>;