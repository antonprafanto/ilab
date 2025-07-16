import { Request, Response } from 'express';
import { z } from 'zod';
import { database } from '../config/database';
import { ResponseHelper } from '../utils/response';
import { ValidationError, NotFoundError, ConflictError } from '../utils/errors';
import { logger } from '../utils/logger';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

// Removed local Request interface - using global Express.Request extension

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

const UpdateBookingSchema = z.object({
  equipmentId: z.string().uuid().optional(),
  title: z.string().min(1).max(200).optional(),
  description: z.string().optional(),
  startTime: z.string().datetime().optional(),
  endTime: z.string().datetime().optional(),
  purpose: z.string().min(1).max(500).optional(),
  sampleType: z.enum(['water', 'soil', 'biological', 'chemical', 'industrial', 'other']).optional(),
  numberOfSamples: z.number().int().positive().optional(),
  specialRequirements: z.string().optional(),
  attachments: z.array(z.string()).optional(),
  status: z.enum(['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show']).optional(),
  priority: z.enum(['low', 'normal', 'high', 'urgent']).optional(),
  notes: z.string().optional(),
  estimatedCost: z.number().optional(),
  actualCost: z.number().optional()
});

class BookingsController {
  async getBookings(req: Request, res: Response): Promise<void> {
    try {
      const page = parseInt(req.query.page as string) || 1;
      const limit = parseInt(req.query.limit as string) || 10;
      const search = req.query.search as string || '';
      const status = req.query.status as string || '';
      const equipmentId = req.query.equipmentId as string || '';
      const userId = req.query.userId as string || '';
      const startDate = req.query.startDate as string || '';
      const endDate = req.query.endDate as string || '';

      const offset = (page - 1) * limit;

      let whereConditions = ['1 = 1'];
      const queryParams: any[] = [];

      // Role-based filtering
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head'].includes(userRole || '')) {
        // Non-admin users can only see their own bookings
        whereConditions.push('b.user_id = ?');
        queryParams.push(req.user?.userId);
      }

      if (search) {
        whereConditions.push('(b.title LIKE ? OR b.description LIKE ? OR b.purpose LIKE ?)');
        queryParams.push(`%${search}%`, `%${search}%`, `%${search}%`);
      }

      if (status) {
        whereConditions.push('b.status = ?');
        queryParams.push(status);
      }

      if (equipmentId) {
        whereConditions.push('b.equipment_id = ?');
        queryParams.push(equipmentId);
      }

      if (userId) {
        whereConditions.push('b.user_id = ?');
        queryParams.push(userId);
      }

      if (startDate) {
        whereConditions.push('DATE(b.start_time) >= ?');
        queryParams.push(startDate);
      }

      if (endDate) {
        whereConditions.push('DATE(b.end_time) <= ?');
        queryParams.push(endDate);
      }

      const whereClause = whereConditions.join(' AND ');

      // Get total count
      const [countResult] = await database.execute(`
        SELECT COUNT(*) as total
        FROM bookings b
        LEFT JOIN equipment e ON b.equipment_id = e.id
        LEFT JOIN users u ON b.user_id = u.id
        WHERE ${whereClause}
      `, queryParams) as [RowDataPacket[], any];

      const total = countResult[0].total;

      // Get bookings with pagination
      const [bookings] = await database.execute(`
        SELECT 
          b.id,
          b.title,
          b.description,
          b.start_time,
          b.end_time,
          b.status,
          b.priority,
          b.purpose,
          b.sample_type,
          b.number_of_samples,
          b.special_requirements,
          b.estimated_cost,
          b.actual_cost,
          b.notes,
          b.attachments,
          b.created_at,
          b.updated_at,
          e.id as equipment_id,
          e.name as equipment_name,
          e.type as equipment_type,
          e.location as equipment_location,
          u.id as user_id,
          CONCAT(u.first_name, ' ', u.last_name) as user_name,
          u.email as user_email,
          u.faculty as user_faculty,
          approver.id as approved_by_id,
          CONCAT(approver.first_name, ' ', approver.last_name) as approved_by_name,
          b.approved_at
        FROM bookings b
        LEFT JOIN equipment e ON b.equipment_id = e.id
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN users approver ON b.approved_by = approver.id
        WHERE ${whereClause}
        ORDER BY b.start_time DESC
        LIMIT ? OFFSET ?
      `, [...queryParams, limit, offset]) as [RowDataPacket[], any];

      // Parse JSON fields
      const bookingsWithParsedJson = bookings.map(booking => ({
        ...booking,
        attachments: booking.attachments ? JSON.parse(booking.attachments) : []
      }));

      const totalPages = Math.ceil(total / limit);

      ResponseHelper.success(res, {
        bookings: bookingsWithParsedJson,
        pagination: {
          page,
          limit,
          total,
          totalPages,
          hasNext: page < totalPages,
          hasPrev: page > 1
        }
      }, 'Bookings retrieved successfully');

    } catch (error) {
      logger.error('Error getting bookings:', error);
      throw error;
    }
  }

  async getBookingById(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid booking ID format');
      }

      const [bookings] = await database.execute(`
        SELECT 
          b.id,
          b.title,
          b.description,
          b.start_time,
          b.end_time,
          b.status,
          b.priority,
          b.purpose,
          b.sample_type,
          b.number_of_samples,
          b.special_requirements,
          b.estimated_cost,
          b.actual_cost,
          b.notes,
          b.attachments,
          b.reminder_sent,
          b.created_at,
          b.updated_at,
          e.id as equipment_id,
          e.name as equipment_name,
          e.type as equipment_type,
          e.location as equipment_location,
          e.responsible_person as equipment_responsible,
          e.pricing as equipment_pricing,
          u.id as user_id,
          CONCAT(u.first_name, ' ', u.last_name) as user_name,
          u.email as user_email,
          u.phone_number as user_phone,
          u.faculty as user_faculty,
          u.department as user_department,
          approver.id as approved_by_id,
          CONCAT(approver.first_name, ' ', approver.last_name) as approved_by_name,
          b.approved_at,
          canceller.id as cancelled_by_id,
          CONCAT(canceller.first_name, ' ', canceller.last_name) as cancelled_by_name,
          b.cancelled_at,
          b.cancellation_reason
        FROM bookings b
        LEFT JOIN equipment e ON b.equipment_id = e.id
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN users approver ON b.approved_by = approver.id
        LEFT JOIN users canceller ON b.cancelled_by = canceller.id
        WHERE b.id = ?
      `, [id]) as [RowDataPacket[], any];

      if (bookings.length === 0) {
        throw new NotFoundError('Booking not found');
      }

      const booking = bookings[0];

      // Check permission - users can only view their own bookings unless they're admin/staff
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head', 'laboran'].includes(userRole || '') && 
          booking.user_id !== req.user?.userId) {
        throw new ValidationError('Access denied');
      }

      const bookingData = {
        ...booking,
        attachments: booking.attachments ? JSON.parse(booking.attachments) : [],
        equipment_pricing: booking.equipment_pricing ? JSON.parse(booking.equipment_pricing) : {}
      };

      ResponseHelper.success(res, bookingData, 'Booking retrieved successfully');

    } catch (error) {
      logger.error('Error getting booking by ID:', error);
      throw error;
    }
  }

  async createBooking(req: Request, res: Response): Promise<void> {
    try {
      const bookingData = req.body;
      const userId = req.user?.userId;

      if (!userId) {
        throw new ValidationError('User ID not found');
      }

      // Validate booking data
      const validationResult = CreateBookingSchema.safeParse(bookingData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid booking data: ' + validationResult.error.errors.map((e: any) => e.message).join(', ')
        );
      }

      const {
        equipmentId,
        title,
        description,
        startTime,
        endTime,
        purpose,
        sampleType,
        numberOfSamples,
        specialRequirements,
        attachments
      } = validationResult.data;

      // Check if equipment exists and is available
      const [equipment] = await database.execute(
        'SELECT id, name, status, booking_rules, pricing FROM equipment WHERE id = ?',
        [equipmentId]
      ) as [RowDataPacket[], any];

      if (equipment.length === 0) {
        throw new NotFoundError('Equipment not found');
      }

      if (equipment[0].status !== 'available') {
        throw new ValidationError('Equipment is not available for booking');
      }

      // Check for booking conflicts
      const [conflicts] = await database.execute(`
        SELECT id FROM bookings 
        WHERE equipment_id = ? 
        AND status IN ('confirmed', 'in_progress')
        AND NOT (end_time <= ? OR start_time >= ?)
      `, [equipmentId, startTime, endTime]) as [RowDataPacket[], any];

      if (conflicts.length > 0) {
        throw new ConflictError('Equipment is already booked for the specified time slot');
      }

      // Validate booking rules (basic validation)
      const bookingRules = equipment[0].booking_rules ? JSON.parse(equipment[0].booking_rules) : {};
      const start = new Date(startTime);
      const end = new Date(endTime);
      const durationHours = (end.getTime() - start.getTime()) / (1000 * 60 * 60);

      if (bookingRules.maxDurationHours && durationHours > bookingRules.maxDurationHours) {
        throw new ValidationError(`Booking duration exceeds maximum allowed (${bookingRules.maxDurationHours} hours)`);
      }

      if (bookingRules.minAdvanceHours) {
        const advanceHours = (start.getTime() - Date.now()) / (1000 * 60 * 60);
        if (advanceHours < bookingRules.minAdvanceHours) {
          throw new ValidationError(`Booking must be made at least ${bookingRules.minAdvanceHours} hours in advance`);
        }
      }

      // Calculate estimated cost
      const pricing = equipment[0].pricing ? JSON.parse(equipment[0].pricing) : {};
      let estimatedCost = 0;
      if (pricing.hourlyRate) {
        estimatedCost = pricing.hourlyRate * durationHours;
      }
      if (pricing.setupFee) {
        estimatedCost += pricing.setupFee;
      }

      // Insert new booking
      const bookingId = crypto.randomUUID();
      await database.execute(`
        INSERT INTO bookings (
          id, user_id, equipment_id, title, description, start_time, end_time,
          status, purpose, sample_type, number_of_samples, special_requirements,
          estimated_cost, attachments
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        bookingId,
        userId,
        equipmentId,
        title,
        description,
        startTime,
        endTime,
        'pending', // Default status
        purpose,
        sampleType,
        numberOfSamples,
        specialRequirements,
        estimatedCost,
        JSON.stringify(attachments || [])
      ]);

      // Log booking creation in history
      await database.execute(`
        INSERT INTO booking_history (id, booking_id, action, new_data, changed_by)
        VALUES (?, ?, ?, ?, ?)
      `, [
        crypto.randomUUID(),
        bookingId,
        'created',
        JSON.stringify({ title, equipment: equipment[0].name, start_time: startTime, end_time: endTime }),
        userId
      ]);

      // Get the created booking
      const [newBooking] = await database.execute(`
        SELECT 
          b.id,
          b.title,
          b.start_time,
          b.end_time,
          b.status,
          b.estimated_cost,
          b.created_at,
          e.name as equipment_name
        FROM bookings b
        LEFT JOIN equipment e ON b.equipment_id = e.id
        WHERE b.id = ?
      `, [bookingId]) as [RowDataPacket[], any];

      ResponseHelper.created(res, newBooking[0], 'Booking created successfully');

    } catch (error) {
      logger.error('Error creating booking:', error);
      throw error;
    }
  }

  async updateBooking(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const updateData = req.body;
      const userId = req.user?.userId;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid booking ID format');
      }

      // Validate update data
      const validationResult = UpdateBookingSchema.safeParse(updateData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid booking data: ' + validationResult.error.errors.map((e: any) => e.message).join(', ')
        );
      }

      // Check if booking exists
      const [existingBookings] = await database.execute(
        'SELECT id, user_id, status, start_time, end_time, equipment_id FROM bookings WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingBookings.length === 0) {
        throw new NotFoundError('Booking not found');
      }

      const booking = existingBookings[0];

      // Check permission
      const userRole = req.user?.role;
      const canEdit = ['admin', 'director', 'vice_director', 'lab_head', 'laboran'].includes(userRole || '') || 
                     booking.user_id === userId;

      if (!canEdit) {
        throw new ValidationError('Access denied');
      }

      // Check if booking can be modified
      if (['completed', 'cancelled'].includes(booking.status)) {
        throw new ValidationError('Cannot modify completed or cancelled booking');
      }

      // If updating time, check for conflicts
      if (updateData.startTime || updateData.endTime) {
        const newStartTime = updateData.startTime || booking.start_time;
        const newEndTime = updateData.endTime || booking.end_time;

        const [conflicts] = await database.execute(`
          SELECT id FROM bookings 
          WHERE equipment_id = ? 
          AND id != ?
          AND status IN ('confirmed', 'in_progress')
          AND NOT (end_time <= ? OR start_time >= ?)
        `, [booking.equipment_id, id, newStartTime, newEndTime]) as [RowDataPacket[], any];

        if (conflicts.length > 0) {
          throw new ConflictError('Equipment is already booked for the new time slot');
        }
      }

      // Build update query dynamically
      const updateFields: string[] = [];
      const updateValues: any[] = [];
      const oldData: any = {};
      const newData: any = {};

      Object.entries(updateData).forEach(([key, value]) => {
        if (value !== undefined) {
          switch (key) {
            case 'equipmentId':
              updateFields.push('equipment_id = ?');
              updateValues.push(value);
              oldData[key] = booking.equipment_id;
              newData[key] = value;
              break;
            case 'startTime':
              updateFields.push('start_time = ?');
              updateValues.push(value);
              oldData[key] = booking.start_time;
              newData[key] = value;
              break;
            case 'endTime':
              updateFields.push('end_time = ?');
              updateValues.push(value);
              oldData[key] = booking.end_time;
              newData[key] = value;
              break;
            case 'sampleType':
              updateFields.push('sample_type = ?');
              updateValues.push(value);
              break;
            case 'numberOfSamples':
              updateFields.push('number_of_samples = ?');
              updateValues.push(value);
              break;
            case 'specialRequirements':
              updateFields.push('special_requirements = ?');
              updateValues.push(value);
              break;
            case 'estimatedCost':
              updateFields.push('estimated_cost = ?');
              updateValues.push(value);
              break;
            case 'actualCost':
              updateFields.push('actual_cost = ?');
              updateValues.push(value);
              break;
            case 'attachments':
              updateFields.push('attachments = ?');
              updateValues.push(JSON.stringify(value));
              break;
            default:
              if (['title', 'description', 'status', 'priority', 'purpose', 'notes'].includes(key)) {
                updateFields.push(`${key} = ?`);
                updateValues.push(value);
                newData[key] = value;
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
        UPDATE bookings 
        SET ${updateFields.join(', ')}
        WHERE id = ?
      `;

      await database.execute(updateQuery, updateValues);

      // Log booking update in history
      await database.execute(`
        INSERT INTO booking_history (id, booking_id, action, old_data, new_data, changed_by)
        VALUES (?, ?, ?, ?, ?, ?)
      `, [
        crypto.randomUUID(),
        id,
        'updated',
        JSON.stringify(oldData),
        JSON.stringify(newData),
        userId
      ]);

      // Get updated booking
      const [updatedBooking] = await database.execute(`
        SELECT 
          b.id,
          b.title,
          b.status,
          b.start_time,
          b.end_time,
          b.updated_at,
          e.name as equipment_name
        FROM bookings b
        LEFT JOIN equipment e ON b.equipment_id = e.id
        WHERE b.id = ?
      `, [id]) as [RowDataPacket[], any];

      ResponseHelper.success(res, updatedBooking[0], 'Booking updated successfully');

    } catch (error) {
      logger.error('Error updating booking:', error);
      throw error;
    }
  }

  async approveBooking(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const { notes } = req.body;
      const userId = req.user?.userId;

      // Check if user has permission to approve
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head', 'laboran'].includes(userRole || '')) {
        throw new ValidationError('Access denied: Insufficient permissions to approve bookings');
      }

      // Get booking details
      const [bookings] = await database.execute(
        'SELECT id, status, user_id FROM bookings WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (bookings.length === 0) {
        throw new NotFoundError('Booking not found');
      }

      const booking = bookings[0];

      if (booking.status !== 'pending') {
        throw new ValidationError('Only pending bookings can be approved');
      }

      // Update booking status
      await database.execute(`
        UPDATE bookings 
        SET status = 'confirmed', 
            approved_by = ?, 
            approved_at = CURRENT_TIMESTAMP,
            notes = ?
        WHERE id = ?
      `, [userId, notes, id]);

      // Log approval in history
      await database.execute(`
        INSERT INTO booking_history (id, booking_id, action, new_data, changed_by, reason)
        VALUES (?, ?, ?, ?, ?, ?)
      `, [
        crypto.randomUUID(),
        id,
        'approved',
        JSON.stringify({ status: 'confirmed', approved_by: userId }),
        userId,
        notes
      ]);

      ResponseHelper.success(res, null, 'Booking approved successfully');

    } catch (error) {
      logger.error('Error approving booking:', error);
      throw error;
    }
  }

  async cancelBooking(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const { reason } = req.body;
      const userId = req.user?.userId;

      // Get booking details
      const [bookings] = await database.execute(
        'SELECT id, status, user_id, start_time FROM bookings WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (bookings.length === 0) {
        throw new NotFoundError('Booking not found');
      }

      const booking = bookings[0];

      // Check permission
      const userRole = req.user?.role;
      const canCancel = ['admin', 'director', 'vice_director', 'lab_head', 'laboran'].includes(userRole || '') || 
                       booking.user_id === userId;

      if (!canCancel) {
        throw new ValidationError('Access denied');
      }

      if (['completed', 'cancelled'].includes(booking.status)) {
        throw new ValidationError('Cannot cancel completed or already cancelled booking');
      }

      // Update booking status
      await database.execute(`
        UPDATE bookings 
        SET status = 'cancelled', 
            cancelled_by = ?, 
            cancelled_at = CURRENT_TIMESTAMP,
            cancellation_reason = ?
        WHERE id = ?
      `, [userId, reason, id]);

      // Log cancellation in history
      await database.execute(`
        INSERT INTO booking_history (id, booking_id, action, new_data, changed_by, reason)
        VALUES (?, ?, ?, ?, ?, ?)
      `, [
        crypto.randomUUID(),
        id,
        'cancelled',
        JSON.stringify({ status: 'cancelled', cancelled_by: userId }),
        userId,
        reason
      ]);

      ResponseHelper.success(res, null, 'Booking cancelled successfully');

    } catch (error) {
      logger.error('Error cancelling booking:', error);
      throw error;
    }
  }

  async getBookingStats(req: Request, res: Response): Promise<void> {
    try {
      // Get booking statistics
      const [stats] = await database.execute(`
        SELECT 
          COUNT(*) as total_bookings,
          SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
          SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
          SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_bookings,
          SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
          SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
          AVG(TIMESTAMPDIFF(HOUR, start_time, end_time)) as avg_duration_hours,
          SUM(estimated_cost) as total_estimated_revenue,
          SUM(actual_cost) as total_actual_revenue
        FROM bookings
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
      `) as [RowDataPacket[], any];

      // Get equipment usage statistics
      const [equipmentStats] = await database.execute(`
        SELECT 
          e.name as equipment_name,
          e.type as equipment_type,
          COUNT(b.id) as booking_count,
          SUM(TIMESTAMPDIFF(HOUR, b.start_time, b.end_time)) as total_hours_used
        FROM equipment e
        LEFT JOIN bookings b ON e.id = b.equipment_id 
          AND b.status IN ('confirmed', 'in_progress', 'completed')
          AND b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY e.id, e.name, e.type
        ORDER BY booking_count DESC
        LIMIT 10
      `) as [RowDataPacket[], any];

      ResponseHelper.success(res, {
        overview: stats[0],
        equipmentUsage: equipmentStats
      }, 'Booking statistics retrieved successfully');

    } catch (error) {
      logger.error('Error getting booking stats:', error);
      throw error;
    }
  }
}

export const bookingsController = new BookingsController();