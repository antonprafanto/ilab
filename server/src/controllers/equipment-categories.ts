import { Request, Response } from 'express';
import { z } from 'zod';
import { database } from '../config/database';
import { ResponseHelper } from '../utils/response';
import { ValidationError, NotFoundError, ConflictError } from '../utils/errors';
import { logger } from '../utils/logger';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

// Removed local Request interface - using global Express.Request extension

// Equipment category validation schemas
const CreateCategorySchema = z.object({
  name: z.string().min(1).max(100),
  description: z.string().optional(),
  icon: z.string().max(100).optional()
});

const UpdateCategorySchema = CreateCategorySchema.partial();

class EquipmentCategoriesController {
  async getCategories(req: Request, res: Response): Promise<void> {
    try {
      const search = req.query.search as string || '';

      let whereConditions = ['1 = 1'];
      const queryParams: any[] = [];

      if (search) {
        whereConditions.push('(ec.name LIKE ? OR ec.description LIKE ?)');
        queryParams.push(`%${search}%`, `%${search}%`);
      }

      const whereClause = whereConditions.join(' AND ');

      const [categories] = await database.execute(`
        SELECT 
          ec.id,
          ec.name,
          ec.description,
          ec.icon,
          ec.created_at,
          ec.updated_at,
          COUNT(e.id) as equipment_count
        FROM equipment_categories ec
        LEFT JOIN equipment e ON ec.id = e.category_id
        WHERE ${whereClause}
        GROUP BY ec.id, ec.name, ec.description, ec.icon, ec.created_at, ec.updated_at
        ORDER BY ec.name ASC
      `, queryParams) as [RowDataPacket[], any];

      ResponseHelper.success(res, categories, 'Equipment categories retrieved successfully');

    } catch (error) {
      logger.error('Error getting equipment categories:', error);
      throw error;
    }
  }

  async getCategoryById(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid category ID format');
      }

      const [categories] = await database.execute(`
        SELECT 
          ec.id,
          ec.name,
          ec.description,
          ec.icon,
          ec.created_at,
          ec.updated_at,
          COUNT(e.id) as equipment_count
        FROM equipment_categories ec
        LEFT JOIN equipment e ON ec.id = e.category_id
        WHERE ec.id = ?
        GROUP BY ec.id, ec.name, ec.description, ec.icon, ec.created_at, ec.updated_at
      `, [id]) as [RowDataPacket[], any];

      if (categories.length === 0) {
        throw new NotFoundError('Equipment category not found');
      }

      // Get equipment in this category
      const [equipment] = await database.execute(`
        SELECT 
          id,
          name,
          type,
          status,
          location
        FROM equipment
        WHERE category_id = ?
        ORDER BY name ASC
      `, [id]) as [RowDataPacket[], any];

      const categoryData = {
        ...categories[0],
        equipment
      };

      ResponseHelper.success(res, categoryData, 'Equipment category retrieved successfully');

    } catch (error) {
      logger.error('Error getting equipment category by ID:', error);
      throw error;
    }
  }

  async createCategory(req: Request, res: Response): Promise<void> {
    try {
      const categoryData = req.body;

      // Check if user has permission
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head'].includes(userRole || '')) {
        throw new ValidationError('Access denied: Insufficient permissions to create categories');
      }

      // Validate category data
      const validationResult = CreateCategorySchema.safeParse(categoryData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid category data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      const { name, description, icon } = validationResult.data;

      // Check if category name already exists
      const [existingCategories] = await database.execute(
        'SELECT id FROM equipment_categories WHERE name = ?',
        [name]
      ) as [RowDataPacket[], any];

      if (existingCategories.length > 0) {
        throw new ConflictError('Category name already exists');
      }

      // Insert new category
      const categoryId = crypto.randomUUID();
      await database.execute(`
        INSERT INTO equipment_categories (id, name, description, icon)
        VALUES (?, ?, ?, ?)
      `, [categoryId, name, description, icon]);

      // Get the created category
      const [newCategory] = await database.execute(`
        SELECT 
          id,
          name,
          description,
          icon,
          created_at
        FROM equipment_categories
        WHERE id = ?
      `, [categoryId]) as [RowDataPacket[], any];

      ResponseHelper.created(res, newCategory[0], 'Equipment category created successfully');

    } catch (error) {
      logger.error('Error creating equipment category:', error);
      throw error;
    }
  }

  async updateCategory(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const updateData = req.body;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid category ID format');
      }

      // Check if user has permission
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head'].includes(userRole || '')) {
        throw new ValidationError('Access denied: Insufficient permissions to update categories');
      }

      // Validate update data
      const validationResult = UpdateCategorySchema.safeParse(updateData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid category data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      // Check if category exists
      const [existingCategories] = await database.execute(
        'SELECT id, name FROM equipment_categories WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingCategories.length === 0) {
        throw new NotFoundError('Equipment category not found');
      }

      // Check for name conflicts if name is being updated
      if (updateData.name && updateData.name !== existingCategories[0].name) {
        const [nameConflict] = await database.execute(
          'SELECT id FROM equipment_categories WHERE name = ? AND id != ?',
          [updateData.name, id]
        ) as [RowDataPacket[], any];

        if (nameConflict.length > 0) {
          throw new ConflictError('Category name already exists');
        }
      }

      // Build update query dynamically
      const updateFields: string[] = [];
      const updateValues: any[] = [];

      Object.entries(updateData).forEach(([key, value]) => {
        if (value !== undefined && ['name', 'description', 'icon'].includes(key)) {
          updateFields.push(`${key} = ?`);
          updateValues.push(value);
        }
      });

      if (updateFields.length === 0) {
        throw new ValidationError('No valid fields to update');
      }

      updateFields.push('updated_at = CURRENT_TIMESTAMP');
      updateValues.push(id);

      const updateQuery = `
        UPDATE equipment_categories 
        SET ${updateFields.join(', ')}
        WHERE id = ?
      `;

      await database.execute(updateQuery, updateValues);

      // Get updated category
      const [updatedCategory] = await database.execute(`
        SELECT 
          id,
          name,
          description,
          icon,
          updated_at
        FROM equipment_categories
        WHERE id = ?
      `, [id]) as [RowDataPacket[], any];

      ResponseHelper.success(res, updatedCategory[0], 'Equipment category updated successfully');

    } catch (error) {
      logger.error('Error updating equipment category:', error);
      throw error;
    }
  }

  async deleteCategory(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid category ID format');
      }

      // Check if user has permission
      const userRole = req.user?.role;
      if (!['admin', 'director'].includes(userRole || '')) {
        throw new ValidationError('Access denied: Insufficient permissions to delete categories');
      }

      // Check if category exists
      const [existingCategories] = await database.execute(
        'SELECT id FROM equipment_categories WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingCategories.length === 0) {
        throw new NotFoundError('Equipment category not found');
      }

      // Check if category is being used by equipment
      const [equipmentWithCategory] = await database.execute(
        'SELECT COUNT(*) as count FROM equipment WHERE category_id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (equipmentWithCategory[0].count > 0) {
        throw new ValidationError('Cannot delete category that is assigned to equipment');
      }

      // Delete category
      await database.execute('DELETE FROM equipment_categories WHERE id = ?', [id]);

      ResponseHelper.success(res, null, 'Equipment category deleted successfully');

    } catch (error) {
      logger.error('Error deleting equipment category:', error);
      throw error;
    }
  }

  async getCategoryStats(req: Request, res: Response): Promise<void> {
    try {
      // Get category statistics
      const [stats] = await database.execute(`
        SELECT 
          COUNT(*) as total_categories,
          COUNT(CASE WHEN equipment_count > 0 THEN 1 END) as categories_with_equipment,
          AVG(equipment_count) as avg_equipment_per_category
        FROM (
          SELECT 
            ec.id,
            COUNT(e.id) as equipment_count
          FROM equipment_categories ec
          LEFT JOIN equipment e ON ec.id = e.category_id
          GROUP BY ec.id
        ) as category_counts
      `) as [RowDataPacket[], any];

      // Get top categories by equipment count
      const [topCategories] = await database.execute(`
        SELECT 
          ec.name,
          ec.description,
          COUNT(e.id) as equipment_count
        FROM equipment_categories ec
        LEFT JOIN equipment e ON ec.id = e.category_id
        GROUP BY ec.id, ec.name, ec.description
        ORDER BY equipment_count DESC
        LIMIT 5
      `) as [RowDataPacket[], any];

      ResponseHelper.success(res, {
        overview: stats[0],
        topCategories
      }, 'Equipment category statistics retrieved successfully');

    } catch (error) {
      logger.error('Error getting equipment category stats:', error);
      throw error;
    }
  }
}

export const equipmentCategoriesController = new EquipmentCategoriesController();