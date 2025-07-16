import { z } from 'zod';

export enum SampleStatus {
  SUBMITTED = 'submitted',
  RECEIVED = 'received',
  IN_ANALYSIS = 'in_analysis',
  ANALYSIS_COMPLETE = 'analysis_complete',
  RESULTS_READY = 'results_ready',
  DELIVERED = 'delivered',
  REJECTED = 'rejected'
}

export enum SampleType {
  WATER = 'water',
  SOIL = 'soil',
  FOOD = 'food',
  PHARMACEUTICAL = 'pharmaceutical',
  CHEMICAL = 'chemical',
  BIOLOGICAL = 'biological',
  ENVIRONMENTAL = 'environmental',
  CLINICAL = 'clinical',
  INDUSTRIAL = 'industrial',
  OTHER = 'other'
}

export const SampleSchema = z.object({
  id: z.string().uuid(),
  bookingId: z.string().uuid(),
  sampleCode: z.string(),
  sampleName: z.string().min(1),
  sampleType: z.nativeEnum(SampleType),
  description: z.string().optional(),
  quantity: z.string(),
  unit: z.string(),
  storageConditions: z.string().optional(),
  preparationNotes: z.string().optional(),
  analysisRequested: z.array(z.string()),
  priority: z.enum(['low', 'normal', 'high', 'urgent']),
  status: z.nativeEnum(SampleStatus),
  submittedBy: z.string().uuid(),
  receivedBy: z.string().uuid().optional(),
  analyzedBy: z.string().uuid().optional(),
  submittedAt: z.date(),
  receivedAt: z.date().optional(),
  analysisStartedAt: z.date().optional(),
  analysisCompletedAt: z.date().optional(),
  expectedDeliveryDate: z.date().optional(),
  actualDeliveryDate: z.date().optional(),
  sampleCondition: z.object({
    temperature: z.number().optional(),
    pH: z.number().optional(),
    appearance: z.string().optional(),
    notes: z.string().optional()
  }).optional(),
  chainOfCustody: z.array(z.object({
    action: z.string(),
    performedBy: z.string().uuid(),
    timestamp: z.date(),
    notes: z.string().optional()
  })).optional(),
  testResults: z.array(z.object({
    testName: z.string(),
    method: z.string(),
    result: z.string(),
    unit: z.string().optional(),
    uncertainty: z.string().optional(),
    limitOfDetection: z.string().optional(),
    limitOfQuantification: z.string().optional(),
    notes: z.string().optional(),
    performedBy: z.string().uuid(),
    performedAt: z.date(),
    validated: z.boolean().default(false),
    validatedBy: z.string().uuid().optional(),
    validatedAt: z.date().optional()
  })).optional(),
  resultFiles: z.array(z.object({
    name: z.string(),
    url: z.string(),
    type: z.string(),
    uploadedBy: z.string().uuid(),
    uploadedAt: z.date()
  })).optional(),
  createdAt: z.date(),
  updatedAt: z.date()
});

export type Sample = z.infer<typeof SampleSchema>;

export const CreateSampleSchema = SampleSchema.omit({
  id: true,
  sampleCode: true,
  status: true,
  receivedBy: true,
  analyzedBy: true,
  receivedAt: true,
  analysisStartedAt: true,
  analysisCompletedAt: true,
  actualDeliveryDate: true,
  chainOfCustody: true,
  testResults: true,
  resultFiles: true,
  createdAt: true,
  updatedAt: true
}).extend({
  status: z.nativeEnum(SampleStatus).default(SampleStatus.SUBMITTED)
});

export type CreateSample = z.infer<typeof CreateSampleSchema>;

export const UpdateSampleSchema = SampleSchema.partial().omit({
  id: true,
  sampleCode: true,
  bookingId: true,
  submittedBy: true,
  submittedAt: true,
  createdAt: true
});

export type UpdateSample = z.infer<typeof UpdateSampleSchema>;