import { Router } from 'express';
import { rolesController } from '../controllers/roles';
import { authMiddleware } from '../middleware/auth';
import { validateRequest } from '../middleware/validation';
import { z } from 'zod';

const router = Router();

// All role routes require authentication
router.use(authMiddleware);

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

// Get all available permissions
router.get('/permissions', rolesController.getPermissions);

// Get all roles
router.get('/', rolesController.getRoles);

// Get role by ID
router.get('/:id', rolesController.getRoleById);

// Create new role (admin only)
router.post('/',
  validateRequest(CreateRoleSchema),
  rolesController.createRole
);

// Update role (admin only)
router.put('/:id',
  validateRequest(UpdateRoleSchema),
  rolesController.updateRole
);

// Delete role (admin only)
router.delete('/:id', rolesController.deleteRole);

export default router;