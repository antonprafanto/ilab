import { Request, Response } from 'express';
import { z } from 'zod';
import { database } from '../config/database';
import { ResponseHelper } from '../utils/response';
import { ValidationError, NotFoundError, ConflictError } from '../utils/errors';
import { logger } from '../utils/logger';
import { User, CreateUser, UpdateUser, UserStatus } from '@ilab-unmul/shared';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

// Removed local Request interface - using global Express.Request extension

class UsersController {
  async getUsers(req: Request, res: Response): Promise<void> {
    try {
      const page = parseInt(req.query.page as string) || 1;
      const limit = parseInt(req.query.limit as string) || 10;
      const search = req.query.search as string || '';
      const role = req.query.role as string || '';
      const status = req.query.status as string || '';
      const faculty = req.query.faculty as string || '';

      const offset = (page - 1) * limit;

      let whereConditions = ['1 = 1'];
      const queryParams: any[] = [];

      if (search) {
        whereConditions.push('(CONCAT(u.first_name, " ", u.last_name) LIKE ? OR u.email LIKE ? OR u.nim LIKE ? OR u.student_id LIKE ?)');
        queryParams.push(`%${search}%`, `%${search}%`, `%${search}%`, `%${search}%`);
      }

      if (role) {
        whereConditions.push('r.name = ?');
        queryParams.push(role);
      }

      if (status) {
        whereConditions.push('u.status = ?');
        queryParams.push(status);
      }

      if (faculty) {
        whereConditions.push('u.faculty LIKE ?');
        queryParams.push(`%${faculty}%`);
      }

      const whereClause = whereConditions.join(' AND ');

      // Get total count
      const [countResult] = await database.execute(`
        SELECT COUNT(*) as total
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE ${whereClause}
      `, queryParams) as [RowDataPacket[], any];

      const total = countResult[0].total;

      // Get users with pagination
      const [users] = await database.execute(`
        SELECT 
          u.id,
          u.email,
          u.first_name,
          u.last_name,
          u.phone_number,
          u.status,
          u.faculty,
          u.department,
          u.student_id,
          u.nim,
          u.institution,
          u.profile_picture,
          u.is_email_verified,
          u.is_document_verified,
          u.last_login_at,
          u.created_at,
          u.updated_at,
          r.name as role_name,
          r.display_name as role_display_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE ${whereClause}
        ORDER BY u.created_at DESC
        LIMIT ? OFFSET ?
      `, [...queryParams, limit, offset]) as [RowDataPacket[], any];

      const totalPages = Math.ceil(total / limit);

      ResponseHelper.success(res, {
        users,
        pagination: {
          page,
          limit,
          total,
          totalPages,
          hasNext: page < totalPages,
          hasPrev: page > 1
        }
      }, 'Users retrieved successfully');

    } catch (error) {
      logger.error('Error getting users:', error);
      throw error;
    }
  }

  async getUserById(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid user ID format');
      }

      const [users] = await database.execute(`
        SELECT 
          u.id,
          u.email,
          u.first_name,
          u.last_name,
          u.phone_number,
          u.status,
          u.faculty,
          u.department,
          u.student_id,
          u.nim,
          u.institution,
          u.profile_picture,
          u.identity_document,
          u.is_email_verified,
          u.is_document_verified,
          u.last_login_at,
          u.created_at,
          u.updated_at,
          r.id as role_id,
          r.name as role_name,
          r.display_name as role_display_name,
          r.permissions
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.id = ?
      `, [id]) as [RowDataPacket[], any];

      if (users.length === 0) {
        throw new NotFoundError('User not found');
      }

      const user = users[0];

      ResponseHelper.success(res, user, 'User retrieved successfully');

    } catch (error) {
      logger.error('Error getting user by ID:', error);
      throw error;
    }
  }

  async updateUser(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const updateData = req.body;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid user ID format');
      }

      // Validate update data
      const validationResult = UpdateUser.safeParse(updateData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid user data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      // Check if user exists
      const [existingUsers] = await database.execute(
        'SELECT id, email FROM users WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingUsers.length === 0) {
        throw new NotFoundError('User not found');
      }

      // Check for email conflicts if email is being updated
      if (updateData.email && updateData.email !== existingUsers[0].email) {
        const [emailConflict] = await database.execute(
          'SELECT id FROM users WHERE email = ? AND id != ?',
          [updateData.email, id]
        ) as [RowDataPacket[], any];

        if (emailConflict.length > 0) {
          throw new ConflictError('Email already exists');
        }
      }

      // Check for role_id if role is being updated
      let roleId = null;
      if (updateData.role) {
        const [roles] = await database.execute(
          'SELECT id FROM roles WHERE name = ?',
          [updateData.role]
        ) as [RowDataPacket[], any];

        if (roles.length === 0) {
          throw new ValidationError('Invalid role specified');
        }
        roleId = roles[0].id;
      }

      // Build update query dynamically
      const updateFields: string[] = [];
      const updateValues: any[] = [];

      Object.entries(updateData).forEach(([key, value]) => {
        if (value !== undefined) {
          switch (key) {
            case 'role':
              updateFields.push('role_id = ?');
              updateValues.push(roleId);
              break;
            case 'firstName':
              updateFields.push('first_name = ?');
              updateValues.push(value);
              break;
            case 'lastName':
              updateFields.push('last_name = ?');
              updateValues.push(value);
              break;
            case 'phoneNumber':
              updateFields.push('phone_number = ?');
              updateValues.push(value);
              break;
            case 'studentId':
              updateFields.push('student_id = ?');
              updateValues.push(value);
              break;
            case 'profilePicture':
              updateFields.push('profile_picture = ?');
              updateValues.push(value);
              break;
            case 'identityDocument':
              updateFields.push('identity_document = ?');
              updateValues.push(value);
              break;
            case 'isEmailVerified':
              updateFields.push('is_email_verified = ?');
              updateValues.push(value);
              break;
            case 'isDocumentVerified':
              updateFields.push('is_document_verified = ?');
              updateValues.push(value);
              break;
            default:
              if (['email', 'status', 'faculty', 'department', 'nim', 'institution'].includes(key)) {
                updateFields.push(`${key} = ?`);
                updateValues.push(value);
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
        UPDATE users 
        SET ${updateFields.join(', ')}
        WHERE id = ?
      `;

      await database.execute(updateQuery, updateValues);

      // Get updated user
      const [updatedUsers] = await database.execute(`
        SELECT 
          u.id,
          u.email,
          u.first_name,
          u.last_name,
          u.phone_number,
          u.status,
          u.faculty,
          u.department,
          u.student_id,
          u.nim,
          u.institution,
          u.profile_picture,
          u.is_email_verified,
          u.is_document_verified,
          u.updated_at,
          r.name as role_name,
          r.display_name as role_display_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.id = ?
      `, [id]) as [RowDataPacket[], any];

      ResponseHelper.success(res, updatedUsers[0], 'User updated successfully');

    } catch (error) {
      logger.error('Error updating user:', error);
      throw error;
    }
  }

  async updateUserStatus(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const { status, reason } = req.body;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid user ID format');
      }

      if (!Object.values(UserStatus).includes(status)) {
        throw new ValidationError('Invalid status value');
      }

      // Check if user exists
      const [existingUsers] = await database.execute(
        'SELECT id, status FROM users WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingUsers.length === 0) {
        throw new NotFoundError('User not found');
      }

      if (existingUsers[0].status === status) {
        throw new ValidationError('User already has this status');
      }

      // Update user status
      await database.execute(
        'UPDATE users SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?',
        [status, id]
      );

      // Log the status change
      await database.execute(`
        INSERT INTO audit_logs (table_name, record_id, action, old_values, new_values, user_id)
        VALUES (?, ?, ?, ?, ?, ?)
      `, [
        'users',
        id,
        'UPDATE',
        JSON.stringify({ status: existingUsers[0].status }),
        JSON.stringify({ status, reason }),
        req.user?.userId
      ]);

      ResponseHelper.success(res, null, `User status updated to ${status}`);

    } catch (error) {
      logger.error('Error updating user status:', error);
      throw error;
    }
  }

  async deleteUser(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid user ID format');
      }

      // Check if user exists
      const [existingUsers] = await database.execute(
        'SELECT id FROM users WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingUsers.length === 0) {
        throw new NotFoundError('User not found');
      }

      // Check if user has any active bookings
      const [activeBookings] = await database.execute(
        'SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status IN ("pending", "confirmed", "in_progress")',
        [id]
      ) as [RowDataPacket[], any];

      if (activeBookings[0].count > 0) {
        throw new ValidationError('Cannot delete user with active bookings');
      }

      // Soft delete - update status to inactive instead of hard delete
      await database.execute(
        'UPDATE users SET status = "inactive", updated_at = CURRENT_TIMESTAMP WHERE id = ?',
        [id]
      );

      // Log the deletion
      await database.execute(`
        INSERT INTO audit_logs (table_name, record_id, action, new_values, user_id)
        VALUES (?, ?, ?, ?, ?)
      `, [
        'users',
        id,
        'DELETE',
        JSON.stringify({ action: 'soft_delete', reason: 'deleted_by_admin' }),
        req.user?.userId
      ]);

      ResponseHelper.success(res, null, 'User deleted successfully');

    } catch (error) {
      logger.error('Error deleting user:', error);
      throw error;
    }
  }

  async getUserStats(req: Request, res: Response): Promise<void> {
    try {
      // Get user statistics
      const [stats] = await database.execute(`
        SELECT 
          COUNT(*) as total_users,
          SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
          SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_users,
          SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_users,
          SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended_users,
          SUM(CASE WHEN is_email_verified = 1 THEN 1 ELSE 0 END) as verified_users,
          SUM(CASE WHEN is_document_verified = 1 THEN 1 ELSE 0 END) as document_verified_users
        FROM users
      `) as [RowDataPacket[], any];

      // Get role distribution
      const [roleStats] = await database.execute(`
        SELECT 
          r.display_name as role_name,
          COUNT(u.id) as user_count
        FROM roles r
        LEFT JOIN users u ON r.id = u.role_id
        GROUP BY r.id, r.display_name
        ORDER BY user_count DESC
      `) as [RowDataPacket[], any];

      // Get faculty distribution
      const [facultyStats] = await database.execute(`
        SELECT 
          faculty,
          COUNT(*) as user_count
        FROM users
        WHERE faculty IS NOT NULL AND faculty != ''
        GROUP BY faculty
        ORDER BY user_count DESC
        LIMIT 10
      `) as [RowDataPacket[], any];

      ResponseHelper.success(res, {
        overview: stats[0],
        roleDistribution: roleStats,
        facultyDistribution: facultyStats
      }, 'User statistics retrieved successfully');

    } catch (error) {
      logger.error('Error getting user stats:', error);
      throw error;
    }
  }
}

export const usersController = new UsersController();