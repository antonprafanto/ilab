import { Request, Response } from 'express';
import { z } from 'zod';
import { database } from '../config/database';
import { ResponseHelper } from '../utils/response';
import { ValidationError, NotFoundError, ConflictError } from '../utils/errors';
import { logger } from '../utils/logger';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

// Removed local Request interface - using global Express.Request extension

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

class SamplesController {
  async getSamples(req: Request, res: Response): Promise<void> {
    try {
      const page = parseInt(req.query.page as string) || 1;
      const limit = parseInt(req.query.limit as string) || 10;
      const search = req.query.search as string || '';
      const status = req.query.status as string || '';
      const sampleType = req.query.sampleType as string || '';
      const bookingId = req.query.bookingId as string || '';
      const submittedBy = req.query.submittedBy as string || '';

      const offset = (page - 1) * limit;

      let whereConditions = ['1 = 1'];
      const queryParams: any[] = [];

      // Role-based filtering
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head', 'laboran'].includes(userRole || '')) {
        // Non-admin users can only see their own samples
        whereConditions.push('s.submitted_by = ?');
        queryParams.push(req.user?.userId);
      }

      if (search) {
        whereConditions.push('(s.sample_code LIKE ? OR s.sample_name LIKE ? OR s.description LIKE ?)');
        queryParams.push(`%${search}%`, `%${search}%`, `%${search}%`);
      }

      if (status) {
        whereConditions.push('s.status = ?');
        queryParams.push(status);
      }

      if (sampleType) {
        whereConditions.push('s.sample_type = ?');
        queryParams.push(sampleType);
      }

      if (bookingId) {
        whereConditions.push('s.booking_id = ?');
        queryParams.push(bookingId);
      }

      if (submittedBy) {
        whereConditions.push('s.submitted_by = ?');
        queryParams.push(submittedBy);
      }

      const whereClause = whereConditions.join(' AND ');

      // Get total count
      const [countResult] = await database.execute(`
        SELECT COUNT(*) as total
        FROM samples s
        LEFT JOIN bookings b ON s.booking_id = b.id
        LEFT JOIN users u ON s.submitted_by = u.id
        WHERE ${whereClause}
      `, queryParams) as [RowDataPacket[], any];

      const total = countResult[0].total;

      // Get samples with pagination
      const [samples] = await database.execute(`
        SELECT 
          s.id,
          s.sample_code,
          s.sample_name,
          s.sample_type,
          s.description,
          s.quantity,
          s.unit,
          s.storage_conditions,
          s.preparation_notes,
          s.analysis_requested,
          s.priority,
          s.status,
          s.expected_delivery_date,
          s.actual_delivery_date,
          s.sample_condition,
          s.submitted_at,
          s.received_at,
          s.analysis_started_at,
          s.analysis_completed_at,
          s.created_at,
          s.updated_at,
          b.id as booking_id,
          b.title as booking_title,
          b.equipment_id as booking_equipment_id,
          e.name as equipment_name,
          e.type as equipment_type,
          submitter.id as submitted_by_id,
          CONCAT(submitter.first_name, ' ', submitter.last_name) as submitted_by_name,
          submitter.email as submitted_by_email,
          receiver.id as received_by_id,
          CONCAT(receiver.first_name, ' ', receiver.last_name) as received_by_name,
          analyzer.id as analyzed_by_id,
          CONCAT(analyzer.first_name, ' ', analyzer.last_name) as analyzed_by_name
        FROM samples s
        LEFT JOIN bookings b ON s.booking_id = b.id
        LEFT JOIN equipment e ON b.equipment_id = e.id
        LEFT JOIN users submitter ON s.submitted_by = submitter.id
        LEFT JOIN users receiver ON s.received_by = receiver.id
        LEFT JOIN users analyzer ON s.analyzed_by = analyzer.id
        WHERE ${whereClause}
        ORDER BY s.created_at DESC
        LIMIT ? OFFSET ?
      `, [...queryParams, limit, offset]) as [RowDataPacket[], any];

      // Parse JSON fields
      const samplesWithParsedJson = samples.map(sample => ({
        ...sample,
        analysis_requested: sample.analysis_requested ? JSON.parse(sample.analysis_requested) : [],
        sample_condition: sample.sample_condition ? JSON.parse(sample.sample_condition) : {}
      }));

      const totalPages = Math.ceil(total / limit);

      ResponseHelper.success(res, {
        samples: samplesWithParsedJson,
        pagination: {
          page,
          limit,
          total,
          totalPages,
          hasNext: page < totalPages,
          hasPrev: page > 1
        }
      }, 'Samples retrieved successfully');

    } catch (error) {
      logger.error('Error getting samples:', error);
      throw error;
    }
  }

  async getSampleById(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid sample ID format');
      }

      const [samples] = await database.execute(`
        SELECT 
          s.id,
          s.sample_code,
          s.sample_name,
          s.sample_type,
          s.description,
          s.quantity,
          s.unit,
          s.storage_conditions,
          s.preparation_notes,
          s.analysis_requested,
          s.priority,
          s.status,
          s.expected_delivery_date,
          s.actual_delivery_date,
          s.sample_condition,
          s.submitted_at,
          s.received_at,
          s.analysis_started_at,
          s.analysis_completed_at,
          s.created_at,
          s.updated_at,
          b.id as booking_id,
          b.title as booking_title,
          b.equipment_id as booking_equipment_id,
          e.name as equipment_name,
          e.type as equipment_type,
          e.location as equipment_location,
          submitter.id as submitted_by_id,
          CONCAT(submitter.first_name, ' ', submitter.last_name) as submitted_by_name,
          submitter.email as submitted_by_email,
          submitter.phone_number as submitted_by_phone,
          receiver.id as received_by_id,
          CONCAT(receiver.first_name, ' ', receiver.last_name) as received_by_name,
          analyzer.id as analyzed_by_id,
          CONCAT(analyzer.first_name, ' ', analyzer.last_name) as analyzed_by_name
        FROM samples s
        LEFT JOIN bookings b ON s.booking_id = b.id
        LEFT JOIN equipment e ON b.equipment_id = e.id
        LEFT JOIN users submitter ON s.submitted_by = submitter.id
        LEFT JOIN users receiver ON s.received_by = receiver.id
        LEFT JOIN users analyzer ON s.analyzed_by = analyzer.id
        WHERE s.id = ?
      `, [id]) as [RowDataPacket[], any];

      if (samples.length === 0) {
        throw new NotFoundError('Sample not found');
      }

      const sample = samples[0];

      // Check permission
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head', 'laboran'].includes(userRole || '') && 
          sample.submitted_by_id !== req.user?.userId) {
        throw new ValidationError('Access denied');
      }

      // Get test results for this sample
      const [testResults] = await database.execute(`
        SELECT 
          tr.id,
          tr.test_name,
          tr.test_method,
          tr.result,
          tr.unit,
          tr.uncertainty,
          tr.limit_of_detection,
          tr.limit_of_quantification,
          tr.notes,
          tr.performed_at,
          tr.validated,
          tr.validated_at,
          performer.id as performed_by_id,
          CONCAT(performer.first_name, ' ', performer.last_name) as performed_by_name,
          validator.id as validated_by_id,
          CONCAT(validator.first_name, ' ', validator.last_name) as validated_by_name,
          eq.name as equipment_name
        FROM test_results tr
        LEFT JOIN users performer ON tr.performed_by = performer.id
        LEFT JOIN users validator ON tr.validated_by = validator.id
        LEFT JOIN equipment eq ON tr.equipment_used = eq.id
        WHERE tr.sample_id = ?
        ORDER BY tr.performed_at DESC
      `, [id]) as [RowDataPacket[], any];

      // Get chain of custody
      const [custody] = await database.execute(`
        SELECT 
          sc.id,
          sc.action,
          sc.timestamp,
          sc.notes,
          sc.location,
          CONCAT(u.first_name, ' ', u.last_name) as performed_by_name,
          u.email as performed_by_email
        FROM sample_custody sc
        LEFT JOIN users u ON sc.performed_by = u.id
        WHERE sc.sample_id = ?
        ORDER BY sc.timestamp ASC
      `, [id]) as [RowDataPacket[], any];

      const sampleData = {
        ...sample,
        analysis_requested: sample.analysis_requested ? JSON.parse(sample.analysis_requested) : [],
        sample_condition: sample.sample_condition ? JSON.parse(sample.sample_condition) : {},
        test_results: testResults,
        chain_of_custody: custody
      };

      ResponseHelper.success(res, sampleData, 'Sample retrieved successfully');

    } catch (error) {
      logger.error('Error getting sample by ID:', error);
      throw error;
    }
  }

  async createSample(req: Request, res: Response): Promise<void> {
    try {
      const sampleData = req.body;
      const userId = req.user?.userId;

      if (!userId) {
        throw new ValidationError('User ID not found');
      }

      // Validate sample data
      const validationResult = CreateSampleSchema.safeParse(sampleData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid sample data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      const {
        bookingId,
        sampleName,
        sampleType,
        description,
        quantity,
        unit,
        storageConditions,
        preparationNotes,
        analysisRequested,
        priority,
        expectedDeliveryDate,
        sampleCondition
      } = validationResult.data;

      // Check if booking exists and belongs to user (unless admin/staff)
      const [bookings] = await database.execute(
        'SELECT id, user_id, status FROM bookings WHERE id = ?',
        [bookingId]
      ) as [RowDataPacket[], any];

      if (bookings.length === 0) {
        throw new NotFoundError('Booking not found');
      }

      const booking = bookings[0];
      const userRole = req.user?.role;
      
      if (!['admin', 'director', 'vice_director', 'lab_head', 'laboran'].includes(userRole || '') && 
          booking.user_id !== userId) {
        throw new ValidationError('Access denied: You can only create samples for your own bookings');
      }

      if (!['confirmed', 'in_progress'].includes(booking.status)) {
        throw new ValidationError('Cannot create samples for bookings that are not confirmed');
      }

      // Generate unique sample code
      const year = new Date().getFullYear();
      const month = (new Date().getMonth() + 1).toString().padStart(2, '0');
      
      const [lastSample] = await database.execute(`
        SELECT sample_code FROM samples 
        WHERE sample_code LIKE ? 
        ORDER BY sample_code DESC 
        LIMIT 1
      `, [`${year}${month}%`]) as [RowDataPacket[], any];

      let sequence = 1;
      if (lastSample.length > 0) {
        const lastCode = lastSample[0].sample_code;
        const lastSequence = parseInt(lastCode.substring(6));
        sequence = lastSequence + 1;
      }

      const sampleCode = `${year}${month}${sequence.toString().padStart(4, '0')}`;

      // Insert new sample
      const sampleId = crypto.randomUUID();
      await database.execute(`
        INSERT INTO samples (
          id, booking_id, sample_code, sample_name, sample_type, description,
          quantity, unit, storage_conditions, preparation_notes, analysis_requested,
          priority, status, submitted_by, expected_delivery_date, sample_condition
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        sampleId,
        bookingId,
        sampleCode,
        sampleName,
        sampleType,
        description,
        quantity,
        unit,
        storageConditions,
        preparationNotes,
        JSON.stringify(analysisRequested),
        priority,
        'submitted',
        userId,
        expectedDeliveryDate,
        JSON.stringify(sampleCondition || {})
      ]);

      // Create initial chain of custody entry
      await database.execute(`
        INSERT INTO sample_custody (id, sample_id, action, performed_by, timestamp, notes, location)
        VALUES (?, ?, ?, ?, ?, ?, ?)
      `, [
        crypto.randomUUID(),
        sampleId,
        'Sample submitted',
        userId,
        new Date(),
        'Initial sample submission',
        'Client location'
      ]);

      // Get the created sample
      const [newSample] = await database.execute(`
        SELECT 
          s.id,
          s.sample_code,
          s.sample_name,
          s.status,
          s.created_at,
          b.title as booking_title
        FROM samples s
        LEFT JOIN bookings b ON s.booking_id = b.id
        WHERE s.id = ?
      `, [sampleId]) as [RowDataPacket[], any];

      ResponseHelper.created(res, newSample[0], 'Sample created successfully');

    } catch (error) {
      logger.error('Error creating sample:', error);
      throw error;
    }
  }

  async updateSample(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const updateData = req.body;
      const userId = req.user?.userId;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid sample ID format');
      }

      // Validate update data
      const validationResult = UpdateSampleSchema.safeParse(updateData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid sample data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      // Check if sample exists
      const [existingSamples] = await database.execute(
        'SELECT id, submitted_by, status FROM samples WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingSamples.length === 0) {
        throw new NotFoundError('Sample not found');
      }

      const sample = existingSamples[0];

      // Check permission
      const userRole = req.user?.role;
      const canEdit = ['admin', 'director', 'vice_director', 'lab_head', 'laboran'].includes(userRole || '') || 
                     sample.submitted_by === userId;

      if (!canEdit) {
        throw new ValidationError('Access denied');
      }

      // Build update query dynamically
      const updateFields: string[] = [];
      const updateValues: any[] = [];
      const custodyActions: string[] = [];

      Object.entries(updateData).forEach(([key, value]) => {
        if (value !== undefined) {
          switch (key) {
            case 'bookingId':
              updateFields.push('booking_id = ?');
              updateValues.push(value);
              break;
            case 'sampleName':
              updateFields.push('sample_name = ?');
              updateValues.push(value);
              break;
            case 'sampleType':
              updateFields.push('sample_type = ?');
              updateValues.push(value);
              break;
            case 'storageConditions':
              updateFields.push('storage_conditions = ?');
              updateValues.push(value);
              break;
            case 'preparationNotes':
              updateFields.push('preparation_notes = ?');
              updateValues.push(value);
              break;
            case 'expectedDeliveryDate':
              updateFields.push('expected_delivery_date = ?');
              updateValues.push(value);
              break;
            case 'actualDeliveryDate':
              updateFields.push('actual_delivery_date = ?');
              updateValues.push(value);
              break;
            case 'receivedBy':
              updateFields.push('received_by = ?');
              updateValues.push(value);
              break;
            case 'analyzedBy':
              updateFields.push('analyzed_by = ?');
              updateValues.push(value);
              break;
            case 'receivedAt':
              updateFields.push('received_at = ?');
              updateValues.push(value);
              custodyActions.push('Sample received at laboratory');
              break;
            case 'analysisStartedAt':
              updateFields.push('analysis_started_at = ?');
              updateValues.push(value);
              custodyActions.push('Analysis started');
              break;
            case 'analysisCompletedAt':
              updateFields.push('analysis_completed_at = ?');
              updateValues.push(value);
              custodyActions.push('Analysis completed');
              break;
            case 'analysisRequested':
              updateFields.push('analysis_requested = ?');
              updateValues.push(JSON.stringify(value));
              break;
            case 'sampleCondition':
              updateFields.push('sample_condition = ?');
              updateValues.push(JSON.stringify(value));
              break;
            default:
              if (['description', 'quantity', 'unit', 'priority', 'status'].includes(key)) {
                updateFields.push(`${key} = ?`);
                updateValues.push(value);
                if (key === 'status' && value !== sample.status) {
                  custodyActions.push(`Status changed to ${value}`);
                }
              }
              break;
          }
        }
      });

      if (updateFields.length === 0) {
        throw new ValidationError('No valid fields to update');
      }

      updateFields.push('updated_at = CURRENT_TIMESTAMP');
      updateValues.push(id);

      const updateQuery = `
        UPDATE samples 
        SET ${updateFields.join(', ')}
        WHERE id = ?
      `;

      await database.execute(updateQuery, updateValues);

      // Add chain of custody entries for significant changes
      for (const action of custodyActions) {
        await database.execute(`
          INSERT INTO sample_custody (id, sample_id, action, performed_by, timestamp, location)
          VALUES (?, ?, ?, ?, ?, ?)
        `, [
          crypto.randomUUID(),
          id,
          action,
          userId,
          new Date(),
          'Laboratory'
        ]);
      }

      // Get updated sample
      const [updatedSample] = await database.execute(`
        SELECT 
          s.id,
          s.sample_code,
          s.sample_name,
          s.status,
          s.updated_at
        FROM samples s
        WHERE s.id = ?
      `, [id]) as [RowDataPacket[], any];

      ResponseHelper.success(res, updatedSample[0], 'Sample updated successfully');

    } catch (error) {
      logger.error('Error updating sample:', error);
      throw error;
    }
  }

  async createTestResult(req: Request, res: Response): Promise<void> {
    try {
      const testData = req.body;
      const userId = req.user?.userId;

      if (!userId) {
        throw new ValidationError('User ID not found');
      }

      // Validate test result data
      const validationResult = CreateTestResultSchema.safeParse(testData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid test result data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      const {
        sampleId,
        testName,
        testMethod,
        result,
        unit,
        uncertainty,
        limitOfDetection,
        limitOfQuantification,
        notes,
        equipmentUsed
      } = validationResult.data;

      // Check if sample exists
      const [samples] = await database.execute(
        'SELECT id, status FROM samples WHERE id = ?',
        [sampleId]
      ) as [RowDataPacket[], any];

      if (samples.length === 0) {
        throw new NotFoundError('Sample not found');
      }

      // Check if user has permission to add test results
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head', 'laboran'].includes(userRole || '')) {
        throw new ValidationError('Access denied: Insufficient permissions to add test results');
      }

      // Insert test result
      const testResultId = crypto.randomUUID();
      await database.execute(`
        INSERT INTO test_results (
          id, sample_id, test_name, test_method, result, unit,
          uncertainty, limit_of_detection, limit_of_quantification,
          notes, performed_by, equipment_used
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        testResultId,
        sampleId,
        testName,
        testMethod,
        result,
        unit,
        uncertainty,
        limitOfDetection,
        limitOfQuantification,
        notes,
        userId,
        equipmentUsed
      ]);

      // Add chain of custody entry
      await database.execute(`
        INSERT INTO sample_custody (id, sample_id, action, performed_by, timestamp, notes)
        VALUES (?, ?, ?, ?, ?, ?)
      `, [
        crypto.randomUUID(),
        sampleId,
        `Test result added: ${testName}`,
        userId,
        new Date(),
        `Test method: ${testMethod}`
      ]);

      // Get the created test result
      const [newTestResult] = await database.execute(`
        SELECT 
          tr.id,
          tr.test_name,
          tr.test_method,
          tr.result,
          tr.performed_at,
          CONCAT(u.first_name, ' ', u.last_name) as performed_by_name
        FROM test_results tr
        LEFT JOIN users u ON tr.performed_by = u.id
        WHERE tr.id = ?
      `, [testResultId]) as [RowDataPacket[], any];

      ResponseHelper.created(res, newTestResult[0], 'Test result created successfully');

    } catch (error) {
      logger.error('Error creating test result:', error);
      throw error;
    }
  }

  async getSampleStats(req: Request, res: Response): Promise<void> {
    try {
      // Get sample statistics
      const [stats] = await database.execute(`
        SELECT 
          COUNT(*) as total_samples,
          SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted_samples,
          SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) as received_samples,
          SUM(CASE WHEN status = 'in_analysis' THEN 1 ELSE 0 END) as in_analysis_samples,
          SUM(CASE WHEN status = 'analysis_complete' THEN 1 ELSE 0 END) as analysis_complete_samples,
          SUM(CASE WHEN status = 'results_ready' THEN 1 ELSE 0 END) as results_ready_samples,
          SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_samples,
          SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_samples
        FROM samples
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
      `) as [RowDataPacket[], any];

      // Get sample type distribution
      const [typeStats] = await database.execute(`
        SELECT 
          sample_type,
          COUNT(*) as sample_count
        FROM samples
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY sample_type
        ORDER BY sample_count DESC
      `) as [RowDataPacket[], any];

      // Get analysis turnaround time
      const [turnaroundStats] = await database.execute(`
        SELECT 
          AVG(TIMESTAMPDIFF(DAY, submitted_at, analysis_completed_at)) as avg_turnaround_days,
          MIN(TIMESTAMPDIFF(DAY, submitted_at, analysis_completed_at)) as min_turnaround_days,
          MAX(TIMESTAMPDIFF(DAY, submitted_at, analysis_completed_at)) as max_turnaround_days
        FROM samples
        WHERE analysis_completed_at IS NOT NULL
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
      `) as [RowDataPacket[], any];

      ResponseHelper.success(res, {
        overview: stats[0],
        typeDistribution: typeStats,
        turnaroundTime: turnaroundStats[0]
      }, 'Sample statistics retrieved successfully');

    } catch (error) {
      logger.error('Error getting sample stats:', error);
      throw error;
    }
  }
}

export const samplesController = new SamplesController();