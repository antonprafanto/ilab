import { Router } from 'express';
import { samplesController } from '../controllers/samples';
import { authMiddleware } from '../middleware/auth';
import { validateRequest } from '../middleware/validation';
import { z } from 'zod';

const router = Router();

// All sample routes require authentication
router.use(authMiddleware);

// Sample validation schemas
const CreateSampleSchema = z.object({
  bookingId: z.string().uuid(),
  sampleName: z.string().min(1).max(200),
  sampleType: z.enum(['water', 'soil', 'food', 'pharmaceutical', 'chemical', 'biological', 'environmental', 'clinical', 'industrial', 'other']),
  description: z.string().optional(),
  quantity: z.string().min(1).max(100),
  unit: z.string().min(1).max(50),
  storageConditions: z.string().optional(),
  preparationNotes: z.string().optional(),
  analysisRequested: z.array(z.string()),
  priority: z.enum(['low', 'normal', 'high', 'urgent']).default('normal'),
  expectedDeliveryDate: z.string().date().optional(),
  sampleCondition: z.record(z.any()).optional()
});

const UpdateSampleSchema = CreateSampleSchema.partial().extend({
  status: z.enum(['submitted', 'received', 'in_analysis', 'analysis_complete', 'results_ready', 'delivered', 'rejected']).optional(),
  receivedBy: z.string().uuid().optional(),
  analyzedBy: z.string().uuid().optional(),
  receivedAt: z.string().datetime().optional(),
  analysisStartedAt: z.string().datetime().optional(),
  analysisCompletedAt: z.string().datetime().optional(),
  actualDeliveryDate: z.string().date().optional()
});

const CreateTestResultSchema = z.object({
  sampleId: z.string().uuid(),
  testName: z.string().min(1).max(200),
  testMethod: z.string().min(1).max(200),
  result: z.string().min(1),
  unit: z.string().max(50).optional(),
  uncertainty: z.string().max(100).optional(),
  limitOfDetection: z.string().max(100).optional(),
  limitOfQuantification: z.string().max(100).optional(),
  notes: z.string().optional(),
  equipmentUsed: z.string().uuid().optional()
});

// Get all samples with pagination and filtering
router.get('/', samplesController.getSamples);

// Get sample statistics
router.get('/stats', samplesController.getSampleStats);

// Get sample by ID
router.get('/:id', samplesController.getSampleById);

// Create new sample
router.post('/',
  validateRequest(CreateSampleSchema),
  samplesController.createSample
);

// Update sample
router.put('/:id',
  validateRequest(UpdateSampleSchema),
  samplesController.updateSample
);

// Create test result for sample (lab staff only)
router.post('/test-results',
  validateRequest(CreateTestResultSchema),
  samplesController.createTestResult
);

export default router;