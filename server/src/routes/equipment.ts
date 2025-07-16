import { Router } from 'express';
import { equipmentController } from '../controllers/equipment';
import { authMiddleware } from '../middleware/auth';
import { validateRequest } from '../middleware/validation';
import { z } from 'zod';

const router = Router();

// All equipment routes require authentication
router.use(authMiddleware);

// Equipment validation schemas
const CreateEquipmentSchema = z.object({
  name: z.string().min(1).max(200),
  type: z.enum(['gc_ms', 'lc_ms', 'aas', 'ftir', 'pcr', 'freeze_dryer', 'hplc', 'spectrophotometer', 'microscope', 'centrifuge', 'incubator', 'other']),
  categoryId: z.string().uuid().optional(),
  description: z.string().optional(),
  specifications: z.record(z.any()).optional(),
  location: z.string().min(1).max(200),
  responsiblePerson: z.string().min(1).max(200),
  contactInfo: z.string().max(200).optional(),
  bookingRules: z.record(z.any()),
  pricing: z.record(z.any()),
  images: z.array(z.string()).optional(),
  documents: z.array(z.record(z.any())).optional(),
  maintenanceSchedule: z.record(z.any()).optional(),
  calibrationSchedule: z.record(z.any()).optional()
});

const UpdateEquipmentSchema = CreateEquipmentSchema.partial().extend({
  status: z.enum(['available', 'in_use', 'maintenance', 'out_of_order', 'reserved']).optional()
});

// Get all equipment with pagination and filtering
router.get('/', equipmentController.getEquipment);

// Get equipment statistics
router.get('/stats', equipmentController.getEquipmentStats);

// Get available equipment for booking
router.get('/available', equipmentController.getAvailableEquipment);

// Get equipment by ID
router.get('/:id', equipmentController.getEquipmentById);

// Create new equipment (admin/staff only)
router.post('/',
  validateRequest(CreateEquipmentSchema),
  equipmentController.createEquipment
);

// Update equipment (admin/staff only)
router.put('/:id',
  validateRequest(UpdateEquipmentSchema),
  equipmentController.updateEquipment
);

// Delete equipment (admin only)
router.delete('/:id', equipmentController.deleteEquipment);

export default router;