import { Router } from 'express';
import { usersController } from '../controllers/users';
import { authMiddleware } from '../middleware/auth';
import { validateRequest } from '../middleware/validation';
import { UpdateUser } from '@ilab-unmul/shared';
import { z } from 'zod';

const router = Router();

// All user routes require authentication
router.use(authMiddleware);

// Get all users with pagination and filtering
router.get('/', usersController.getUsers);

// Get user statistics (admin only)
router.get('/stats', usersController.getUserStats);

// Get user by ID
router.get('/:id', usersController.getUserById);

// Update user
router.put('/:id', 
  validateRequest(UpdateUser),
  usersController.updateUser
);

// Update user status (admin/staff only)
router.patch('/:id/status',
  validateRequest(z.object({
    status: z.enum(['pending', 'active', 'inactive', 'suspended']),
    reason: z.string().optional()
  })),
  usersController.updateUserStatus
);

// Delete user (soft delete)
router.delete('/:id', usersController.deleteUser);

export default router;