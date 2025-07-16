import { Request, Response } from 'express';
import { z } from 'zod';
import { database } from '../config/database';
import { ResponseHelper } from '../utils/response';
import { ValidationError, NotFoundError, ConflictError } from '../utils/errors';
import { logger } from '../utils/logger';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

// Removed local Request interface - using global Express.Request extension

// Define role permissions
export const PERMISSIONS = {
  // User management
  USERS_VIEW: 'users.view',
  USERS_CREATE: 'users.create',
  USERS_UPDATE: 'users.update',
  USERS_DELETE: 'users.delete',
  USERS_MANAGE_STATUS: 'users.manage_status',
  
  // Role management
  ROLES_VIEW: 'roles.view',
  ROLES_CREATE: 'roles.create',
  ROLES_UPDATE: 'roles.update',
  ROLES_DELETE: 'roles.delete',
  
  // Equipment management
  EQUIPMENT_VIEW: 'equipment.view',
  EQUIPMENT_CREATE: 'equipment.create',
  EQUIPMENT_UPDATE: 'equipment.update',
  EQUIPMENT_DELETE: 'equipment.delete',
  EQUIPMENT_MANAGE: 'equipment.manage',
  
  // Booking management
  BOOKINGS_VIEW: 'bookings.view',
  BOOKINGS_CREATE: 'bookings.create',
  BOOKINGS_UPDATE: 'bookings.update',
  BOOKINGS_DELETE: 'bookings.delete',
  BOOKINGS_APPROVE: 'bookings.approve',
  BOOKINGS_MANAGE_ALL: 'bookings.manage_all',
  
  // Sample management
  SAMPLES_VIEW: 'samples.view',
  SAMPLES_CREATE: 'samples.create',
  SAMPLES_UPDATE: 'samples.update',
  SAMPLES_DELETE: 'samples.delete',
  SAMPLES_MANAGE: 'samples.manage',
  SAMPLES_RESULTS: 'samples.results',
  
  // Payment management
  PAYMENTS_VIEW: 'payments.view',
  PAYMENTS_CREATE: 'payments.create',
  PAYMENTS_UPDATE: 'payments.update',
  PAYMENTS_MANAGE: 'payments.manage',
  
  // System administration
  SYSTEM_ADMIN: 'system.admin',
  SYSTEM_SETTINGS: 'system.settings',
  SYSTEM_LOGS: 'system.logs',
  
  // Reports and analytics
  REPORTS_VIEW: 'reports.view',
  REPORTS_EXPORT: 'reports.export',
  ANALYTICS_VIEW: 'analytics.view'
} as const;

// Role validation schemas
const CreateRoleSchema = z.object({
  name: z.string().min(1).max(50),
  displayName: z.string().min(1).max(100),
  description: z.string().optional(),
  permissions: z.array(z.string()),
  level: z.number().int().min(1).max(10).default(1),
  isActive: z.boolean().default(true)
});

const UpdateRoleSchema = CreateRoleSchema.partial();

class RolesController {
  async getRoles(req: Request, res: Response): Promise<void> {
    try {
      const includeInactive = req.query.includeInactive === 'true';
      
      let whereClause = '';
      const queryParams: any[] = [];
      
      if (!includeInactive) {
        whereClause = 'WHERE is_active = true';
      }

      const [roles] = await database.execute(`
        SELECT 
          id,
          name,
          display_name,
          description,
          permissions,
          level,
          is_active,
          created_at,
          updated_at,
          (SELECT COUNT(*) FROM users WHERE role_id = roles.id) as user_count
        FROM roles
        ${whereClause}
        ORDER BY level DESC, name ASC
      `, queryParams) as [RowDataPacket[], any];

      // Parse permissions JSON for each role
      const rolesWithPermissions = roles.map(role => ({
        ...role,
        permissions: role.permissions ? JSON.parse(role.permissions) : []
      }));

      ResponseHelper.success(res, rolesWithPermissions, 'Roles retrieved successfully');

    } catch (error) {
      logger.error('Error getting roles:', error);
      throw error;
    }
  }

  async getRoleById(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid role ID format');
      }

      const [roles] = await database.execute(`
        SELECT 
          id,
          name,
          display_name,
          description,
          permissions,
          level,
          is_active,
          created_at,
          updated_at,
          (SELECT COUNT(*) FROM users WHERE role_id = roles.id) as user_count
        FROM roles
        WHERE id = ?
      `, [id]) as [RowDataPacket[], any];

      if (roles.length === 0) {
        throw new NotFoundError('Role not found');
      }

      const role = {
        ...roles[0],
        permissions: roles[0].permissions ? JSON.parse(roles[0].permissions) : []
      };

      ResponseHelper.success(res, role, 'Role retrieved successfully');

    } catch (error) {
      logger.error('Error getting role by ID:', error);
      throw error;
    }
  }

  async createRole(req: Request, res: Response): Promise<void> {
    try {
      const roleData = req.body;

      // Validate role data
      const validationResult = CreateRoleSchema.safeParse(roleData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid role data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      const { name, displayName, description, permissions, level, isActive } = validationResult.data;

      // Check if role name already exists
      const [existingRoles] = await database.execute(
        'SELECT id FROM roles WHERE name = ?',
        [name]
      ) as [RowDataPacket[], any];

      if (existingRoles.length > 0) {
        throw new ConflictError('Role name already exists');
      }

      // Validate permissions
      const validPermissions = Object.values(PERMISSIONS);
      const invalidPermissions = permissions.filter(p => !validPermissions.includes(p as any));
      
      if (invalidPermissions.length > 0) {
        throw new ValidationError(`Invalid permissions: ${invalidPermissions.join(', ')}`);
      }

      // Insert new role
      const roleId = crypto.randomUUID();
      await database.execute(`
        INSERT INTO roles (id, name, display_name, description, permissions, level, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?)
      `, [
        roleId,
        name,
        displayName,
        description,
        JSON.stringify(permissions),
        level,
        isActive
      ]);

      // Get the created role
      const [newRole] = await database.execute(`
        SELECT 
          id,
          name,
          display_name,
          description,
          permissions,
          level,
          is_active,
          created_at,
          updated_at
        FROM roles
        WHERE id = ?
      `, [roleId]) as [RowDataPacket[], any];

      const role = {
        ...newRole[0],
        permissions: JSON.parse(newRole[0].permissions)
      };

      ResponseHelper.created(res, role, 'Role created successfully');

    } catch (error) {
      logger.error('Error creating role:', error);
      throw error;
    }
  }

  async updateRole(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const updateData = req.body;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid role ID format');
      }

      // Validate update data
      const validationResult = UpdateRoleSchema.safeParse(updateData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid role data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      // Check if role exists
      const [existingRoles] = await database.execute(
        'SELECT id, name FROM roles WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingRoles.length === 0) {
        throw new NotFoundError('Role not found');
      }

      // Check for name conflicts if name is being updated
      if (updateData.name && updateData.name !== existingRoles[0].name) {
        const [nameConflict] = await database.execute(
          'SELECT id FROM roles WHERE name = ? AND id != ?',
          [updateData.name, id]
        ) as [RowDataPacket[], any];

        if (nameConflict.length > 0) {
          throw new ConflictError('Role name already exists');
        }
      }

      // Validate permissions if provided
      if (updateData.permissions) {
        const validPermissions = Object.values(PERMISSIONS);
        const invalidPermissions = updateData.permissions.filter((p: string) => !validPermissions.includes(p as any));
        
        if (invalidPermissions.length > 0) {
          throw new ValidationError(`Invalid permissions: ${invalidPermissions.join(', ')}`);
        }
      }

      // Build update query dynamically
      const updateFields: string[] = [];
      const updateValues: any[] = [];

      Object.entries(updateData).forEach(([key, value]) => {
        if (value !== undefined) {
          switch (key) {
            case 'name':
            case 'level':
              updateFields.push(`${key} = ?`);
              updateValues.push(value);
              break;
            case 'displayName':
              updateFields.push('display_name = ?');
              updateValues.push(value);
              break;
            case 'isActive':
              updateFields.push('is_active = ?');
              updateValues.push(value);
              break;
            case 'permissions':
              updateFields.push('permissions = ?');
              updateValues.push(JSON.stringify(value));
              break;
            case 'description':
              updateFields.push('description = ?');
              updateValues.push(value);
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
        UPDATE roles 
        SET ${updateFields.join(', ')}
        WHERE id = ?
      `;

      await database.execute(updateQuery, updateValues);

      // Get updated role
      const [updatedRole] = await database.execute(`
        SELECT 
          id,
          name,
          display_name,
          description,
          permissions,
          level,
          is_active,
          updated_at
        FROM roles
        WHERE id = ?
      `, [id]) as [RowDataPacket[], any];

      const role = {
        ...updatedRole[0],
        permissions: JSON.parse(updatedRole[0].permissions)
      };

      ResponseHelper.success(res, role, 'Role updated successfully');

    } catch (error) {
      logger.error('Error updating role:', error);
      throw error;
    }
  }

  async deleteRole(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid role ID format');
      }

      // Check if role exists
      const [existingRoles] = await database.execute(
        'SELECT id, name FROM roles WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingRoles.length === 0) {
        throw new NotFoundError('Role not found');
      }

      // Check if role is being used by users
      const [usersWithRole] = await database.execute(
        'SELECT COUNT(*) as count FROM users WHERE role_id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (usersWithRole[0].count > 0) {
        throw new ValidationError('Cannot delete role that is assigned to users');
      }

      // Delete role
      await database.execute('DELETE FROM roles WHERE id = ?', [id]);

      ResponseHelper.success(res, null, 'Role deleted successfully');

    } catch (error) {
      logger.error('Error deleting role:', error);
      throw error;
    }
  }

  async getPermissions(req: Request, res: Response): Promise<void> {
    try {
      // Group permissions by category
      const permissionGroups = {
        users: Object.entries(PERMISSIONS)
          .filter(([key]) => key.startsWith('USERS_'))
          .map(([key, value]) => ({ key, value, description: key.replace('USERS_', '').toLowerCase() })),
        
        roles: Object.entries(PERMISSIONS)
          .filter(([key]) => key.startsWith('ROLES_'))
          .map(([key, value]) => ({ key, value, description: key.replace('ROLES_', '').toLowerCase() })),
        
        equipment: Object.entries(PERMISSIONS)
          .filter(([key]) => key.startsWith('EQUIPMENT_'))
          .map(([key, value]) => ({ key, value, description: key.replace('EQUIPMENT_', '').toLowerCase() })),
        
        bookings: Object.entries(PERMISSIONS)
          .filter(([key]) => key.startsWith('BOOKINGS_'))
          .map(([key, value]) => ({ key, value, description: key.replace('BOOKINGS_', '').toLowerCase() })),
        
        samples: Object.entries(PERMISSIONS)
          .filter(([key]) => key.startsWith('SAMPLES_'))
          .map(([key, value]) => ({ key, value, description: key.replace('SAMPLES_', '').toLowerCase() })),
        
        payments: Object.entries(PERMISSIONS)
          .filter(([key]) => key.startsWith('PAYMENTS_'))
          .map(([key, value]) => ({ key, value, description: key.replace('PAYMENTS_', '').toLowerCase() })),
        
        system: Object.entries(PERMISSIONS)
          .filter(([key]) => key.startsWith('SYSTEM_'))
          .map(([key, value]) => ({ key, value, description: key.replace('SYSTEM_', '').toLowerCase() })),
        
        reports: Object.entries(PERMISSIONS)
          .filter(([key]) => key.startsWith('REPORTS_') || key.startsWith('ANALYTICS_'))
          .map(([key, value]) => ({ key, value, description: key.replace(/^(REPORTS_|ANALYTICS_)/, '').toLowerCase() }))
      };

      ResponseHelper.success(res, permissionGroups, 'Permissions retrieved successfully');

    } catch (error) {
      logger.error('Error getting permissions:', error);
      throw error;
    }
  }
}

export const rolesController = new RolesController();