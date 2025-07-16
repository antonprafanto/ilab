import { Router } from 'express';
import { equipmentCategoriesController } from '../controllers/equipment-categories';
import { authMiddleware } from '../middleware/auth';
import { validateRequest } from '../middleware/validation';
import { z } from 'zod';

const router = Router();

// All equipment category routes require authentication
router.use(authMiddleware);

// Equipment category validation schemas
const CreateCategorySchema = z.object({
  name: z.string().min(1).max(100),
  description: z.string().optional(),
  icon: z.string().max(100).optional()
});

const UpdateCategorySchema = CreateCategorySchema.partial();

// Get all equipment categories
router.get('/', equipmentCategoriesController.getCategories);

// Get category statistics
router.get('/stats', equipmentCategoriesController.getCategoryStats);

// Get category by ID
router.get('/:id', equipmentCategoriesController.getCategoryById);

// Create new category (admin/staff only)
router.post('/',
  validateRequest(CreateCategorySchema),
  equipmentCategoriesController.createCategory
);

// Update category (admin/staff only)
router.put('/:id',
  validateRequest(UpdateCategorySchema),
  equipmentCategoriesController.updateCategory
);

// Delete category (admin only)
router.delete('/:id', equipmentCategoriesController.deleteCategory);

export default router;