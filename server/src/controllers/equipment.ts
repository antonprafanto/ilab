import { Request, Response } from 'express';
import { z } from 'zod';
import { database } from '../config/database';
import { ResponseHelper } from '../utils/response';
import { ValidationError, NotFoundError, ConflictError } from '../utils/errors';
import { logger } from '../utils/logger';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

// Removed local Request interface - using global Express.Request extension

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

class EquipmentController {
  async getEquipment(req: Request, res: Response): Promise<void> {
    try {
      const page = parseInt(req.query.page as string) || 1;
      const limit = parseInt(req.query.limit as string) || 10;
      const search = req.query.search as string || '';
      const type = req.query.type as string || '';
      const status = req.query.status as string || '';
      const categoryId = req.query.categoryId as string || '';
      const location = req.query.location as string || '';

      const offset = (page - 1) * limit;

      let whereConditions = ['1 = 1'];
      const queryParams: any[] = [];

      if (search) {
        whereConditions.push('(e.name LIKE ? OR e.description LIKE ? OR e.location LIKE ?)');
        queryParams.push(`%${search}%`, `%${search}%`, `%${search}%`);
      }

      if (type) {
        whereConditions.push('e.type = ?');
        queryParams.push(type);
      }

      if (status) {
        whereConditions.push('e.status = ?');
        queryParams.push(status);
      }

      if (categoryId) {
        whereConditions.push('e.category_id = ?');
        queryParams.push(categoryId);
      }

      if (location) {
        whereConditions.push('e.location LIKE ?');
        queryParams.push(`%${location}%`);
      }

      const whereClause = whereConditions.join(' AND ');

      // Get total count
      const [countResult] = await database.execute(`
        SELECT COUNT(*) as total
        FROM equipment e
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE ${whereClause}
      `, queryParams) as [RowDataPacket[], any];

      const total = countResult[0].total;

      // Get equipment with pagination
      const [equipment] = await database.execute(`
        SELECT 
          e.id,
          e.name,
          e.type,
          e.description,
          e.specifications,
          e.status,
          e.location,
          e.responsible_person,
          e.contact_info,
          e.booking_rules,
          e.pricing,
          e.images,
          e.documents,
          e.maintenance_schedule,
          e.calibration_schedule,
          e.created_at,
          e.updated_at,
          ec.name as category_name,
          ec.description as category_description
        FROM equipment e
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE ${whereClause}
        ORDER BY e.created_at DESC
        LIMIT ? OFFSET ?
      `, [...queryParams, limit, offset]) as [RowDataPacket[], any];

      // Parse JSON fields
      const equipmentWithParsedJson = equipment.map(item => ({
        ...item,
        specifications: item.specifications ? JSON.parse(item.specifications) : {},
        booking_rules: item.booking_rules ? JSON.parse(item.booking_rules) : {},
        pricing: item.pricing ? JSON.parse(item.pricing) : {},
        images: item.images ? JSON.parse(item.images) : [],
        documents: item.documents ? JSON.parse(item.documents) : [],
        maintenance_schedule: item.maintenance_schedule ? JSON.parse(item.maintenance_schedule) : {},
        calibration_schedule: item.calibration_schedule ? JSON.parse(item.calibration_schedule) : {}
      }));

      const totalPages = Math.ceil(total / limit);

      ResponseHelper.success(res, {
        equipment: equipmentWithParsedJson,
        pagination: {
          page,
          limit,
          total,
          totalPages,
          hasNext: page < totalPages,
          hasPrev: page > 1
        }
      }, 'Equipment retrieved successfully');

    } catch (error) {
      logger.error('Error getting equipment:', error);
      throw error;
    }
  }

  async getEquipmentById(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid equipment ID format');
      }

      const [equipment] = await database.execute(`
        SELECT 
          e.id,
          e.name,
          e.type,
          e.description,
          e.specifications,
          e.status,
          e.location,
          e.responsible_person,
          e.contact_info,
          e.booking_rules,
          e.pricing,
          e.images,
          e.documents,
          e.maintenance_schedule,
          e.calibration_schedule,
          e.created_at,
          e.updated_at,
          ec.id as category_id,
          ec.name as category_name,
          ec.description as category_description
        FROM equipment e
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE e.id = ?
      `, [id]) as [RowDataPacket[], any];

      if (equipment.length === 0) {
        throw new NotFoundError('Equipment not found');
      }

      const equipmentData = {
        ...equipment[0],
        specifications: equipment[0].specifications ? JSON.parse(equipment[0].specifications) : {},
        booking_rules: equipment[0].booking_rules ? JSON.parse(equipment[0].booking_rules) : {},
        pricing: equipment[0].pricing ? JSON.parse(equipment[0].pricing) : {},
        images: equipment[0].images ? JSON.parse(equipment[0].images) : [],
        documents: equipment[0].documents ? JSON.parse(equipment[0].documents) : [],
        maintenance_schedule: equipment[0].maintenance_schedule ? JSON.parse(equipment[0].maintenance_schedule) : {},
        calibration_schedule: equipment[0].calibration_schedule ? JSON.parse(equipment[0].calibration_schedule) : {}
      };

      ResponseHelper.success(res, equipmentData, 'Equipment retrieved successfully');

    } catch (error) {
      logger.error('Error getting equipment by ID:', error);
      throw error;
    }
  }

  async createEquipment(req: Request, res: Response): Promise<void> {
    try {
      const equipmentData = req.body;

      // Validate equipment data
      const validationResult = CreateEquipmentSchema.safeParse(equipmentData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid equipment data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      const {
        name,
        type,
        categoryId,
        description,
        specifications,
        location,
        responsiblePerson,
        contactInfo,
        bookingRules,
        pricing,
        images,
        documents,
        maintenanceSchedule,
        calibrationSchedule
      } = validationResult.data;

      // Check if equipment name already exists
      const [existingEquipment] = await database.execute(
        'SELECT id FROM equipment WHERE name = ?',
        [name]
      ) as [RowDataPacket[], any];

      if (existingEquipment.length > 0) {
        throw new ConflictError('Equipment name already exists');
      }

      // Validate category if provided
      if (categoryId) {
        const [categories] = await database.execute(
          'SELECT id FROM equipment_categories WHERE id = ?',
          [categoryId]
        ) as [RowDataPacket[], any];

        if (categories.length === 0) {
          throw new ValidationError('Invalid category ID');
        }
      }

      // Insert new equipment
      const equipmentId = crypto.randomUUID();
      await database.execute(`
        INSERT INTO equipment (
          id, name, type, category_id, description, specifications, status, location,
          responsible_person, contact_info, booking_rules, pricing, images, documents,
          maintenance_schedule, calibration_schedule
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        equipmentId,
        name,
        type,
        categoryId || null,
        description,
        JSON.stringify(specifications || {}),
        'available',
        location,
        responsiblePerson,
        contactInfo,
        JSON.stringify(bookingRules),
        JSON.stringify(pricing),
        JSON.stringify(images || []),
        JSON.stringify(documents || []),
        JSON.stringify(maintenanceSchedule || {}),
        JSON.stringify(calibrationSchedule || {})
      ]);

      // Get the created equipment
      const [newEquipment] = await database.execute(`
        SELECT 
          e.id,
          e.name,
          e.type,
          e.description,
          e.status,
          e.location,
          e.responsible_person,
          e.contact_info,
          e.created_at,
          ec.name as category_name
        FROM equipment e
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE e.id = ?
      `, [equipmentId]) as [RowDataPacket[], any];

      ResponseHelper.created(res, newEquipment[0], 'Equipment created successfully');

    } catch (error) {
      logger.error('Error creating equipment:', error);
      throw error;
    }
  }

  async updateEquipment(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const updateData = req.body;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid equipment ID format');
      }

      // Validate update data
      const validationResult = UpdateEquipmentSchema.safeParse(updateData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid equipment data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      // Check if equipment exists
      const [existingEquipment] = await database.execute(
        'SELECT id, name FROM equipment WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingEquipment.length === 0) {
        throw new NotFoundError('Equipment not found');
      }

      // Check for name conflicts if name is being updated
      if (updateData.name && updateData.name !== existingEquipment[0].name) {
        const [nameConflict] = await database.execute(
          'SELECT id FROM equipment WHERE name = ? AND id != ?',
          [updateData.name, id]
        ) as [RowDataPacket[], any];

        if (nameConflict.length > 0) {
          throw new ConflictError('Equipment name already exists');
        }
      }

      // Validate category if provided
      if (updateData.categoryId) {
        const [categories] = await database.execute(
          'SELECT id FROM equipment_categories WHERE id = ?',
          [updateData.categoryId]
        ) as [RowDataPacket[], any];

        if (categories.length === 0) {
          throw new ValidationError('Invalid category ID');
        }
      }

      // Build update query dynamically
      const updateFields: string[] = [];
      const updateValues: any[] = [];

      Object.entries(updateData).forEach(([key, value]) => {
        if (value !== undefined) {
          switch (key) {
            case 'name':
            case 'type':
            case 'description':
            case 'status':
            case 'location':
              updateFields.push(`${key} = ?`);
              updateValues.push(value);
              break;
            case 'categoryId':
              updateFields.push('category_id = ?');
              updateValues.push(value);
              break;
            case 'responsiblePerson':
              updateFields.push('responsible_person = ?');
              updateValues.push(value);
              break;
            case 'contactInfo':
              updateFields.push('contact_info = ?');
              updateValues.push(value);
              break;
            case 'specifications':
            case 'bookingRules':
            case 'pricing':
            case 'images':
            case 'documents':
            case 'maintenanceSchedule':
            case 'calibrationSchedule':
              const dbField = key.replace(/([A-Z])/g, '_$1').toLowerCase();
              updateFields.push(`${dbField} = ?`);
              updateValues.push(JSON.stringify(value));
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
        UPDATE equipment 
        SET ${updateFields.join(', ')}
        WHERE id = ?
      `;

      await database.execute(updateQuery, updateValues);

      // Get updated equipment
      const [updatedEquipment] = await database.execute(`
        SELECT 
          e.id,
          e.name,
          e.type,
          e.status,
          e.location,
          e.responsible_person,
          e.updated_at,
          ec.name as category_name
        FROM equipment e
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE e.id = ?
      `, [id]) as [RowDataPacket[], any];

      ResponseHelper.success(res, updatedEquipment[0], 'Equipment updated successfully');

    } catch (error) {
      logger.error('Error updating equipment:', error);
      throw error;
    }
  }

  async deleteEquipment(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid equipment ID format');
      }

      // Check if equipment exists
      const [existingEquipment] = await database.execute(
        'SELECT id FROM equipment WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingEquipment.length === 0) {
        throw new NotFoundError('Equipment not found');
      }

      // Check if equipment has any active bookings
      const [activeBookings] = await database.execute(
        'SELECT COUNT(*) as count FROM bookings WHERE equipment_id = ? AND status IN ("pending", "confirmed", "in_progress")',
        [id]
      ) as [RowDataPacket[], any];

      if (activeBookings[0].count > 0) {
        throw new ValidationError('Cannot delete equipment with active bookings');
      }

      // Delete equipment
      await database.execute('DELETE FROM equipment WHERE id = ?', [id]);

      ResponseHelper.success(res, null, 'Equipment deleted successfully');

    } catch (error) {
      logger.error('Error deleting equipment:', error);
      throw error;
    }
  }

  async getEquipmentStats(req: Request, res: Response): Promise<void> {
    try {
      // Get equipment statistics
      const [stats] = await database.execute(`
        SELECT 
          COUNT(*) as total_equipment,
          SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_equipment,
          SUM(CASE WHEN status = 'in_use' THEN 1 ELSE 0 END) as in_use_equipment,
          SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_equipment,
          SUM(CASE WHEN status = 'out_of_order' THEN 1 ELSE 0 END) as out_of_order_equipment,
          SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved_equipment
        FROM equipment
      `) as [RowDataPacket[], any];

      // Get type distribution
      const [typeStats] = await database.execute(`
        SELECT 
          type,
          COUNT(*) as equipment_count
        FROM equipment
        GROUP BY type
        ORDER BY equipment_count DESC
      `) as [RowDataPacket[], any];

      // Get location distribution
      const [locationStats] = await database.execute(`
        SELECT 
          location,
          COUNT(*) as equipment_count
        FROM equipment
        GROUP BY location
        ORDER BY equipment_count DESC
        LIMIT 10
      `) as [RowDataPacket[], any];

      ResponseHelper.success(res, {
        overview: stats[0],
        typeDistribution: typeStats,
        locationDistribution: locationStats
      }, 'Equipment statistics retrieved successfully');

    } catch (error) {
      logger.error('Error getting equipment stats:', error);
      throw error;
    }
  }

  async getAvailableEquipment(req: Request, res: Response): Promise<void> {
    try {
      const { startTime, endTime, type } = req.query;

      let whereConditions = ['e.status = "available"'];
      const queryParams: any[] = [];

      if (type) {
        whereConditions.push('e.type = ?');
        queryParams.push(type);
      }

      // Check for booking conflicts if time range is provided
      if (startTime && endTime) {
        whereConditions.push(`
          e.id NOT IN (
            SELECT equipment_id FROM bookings 
            WHERE status IN ('confirmed', 'in_progress') 
            AND NOT (end_time <= ? OR start_time >= ?)
          )
        `);
        queryParams.push(startTime, endTime);
      }

      const whereClause = whereConditions.join(' AND ');

      const [equipment] = await database.execute(`
        SELECT 
          e.id,
          e.name,
          e.type,
          e.description,
          e.location,
          e.responsible_person,
          e.pricing,
          e.booking_rules,
          ec.name as category_name
        FROM equipment e
        LEFT JOIN equipment_categories ec ON e.category_id = ec.id
        WHERE ${whereClause}
        ORDER BY e.name ASC
      `, queryParams) as [RowDataPacket[], any];

      // Parse JSON fields
      const equipmentWithParsedJson = equipment.map(item => ({
        ...item,
        pricing: item.pricing ? JSON.parse(item.pricing) : {},
        booking_rules: item.booking_rules ? JSON.parse(item.booking_rules) : {}
      }));

      ResponseHelper.success(res, equipmentWithParsedJson, 'Available equipment retrieved successfully');

    } catch (error) {
      logger.error('Error getting available equipment:', error);
      throw error;
    }
  }
}

export const equipmentController = new EquipmentController();