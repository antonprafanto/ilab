import { Router } from 'express';
import authRoutes from './auth';
import userRoutes from './users';
import roleRoutes from './roles';
import equipmentRoutes from './equipment';
import equipmentCategoryRoutes from './equipment-categories';
import bookingRoutes from './bookings';
import sampleRoutes from './samples';
import paymentRoutes from './payments';
import notificationRoutes from './notifications';
import uploadRoutes from './upload';

const router = Router();

// Health check endpoint
router.get('/health', (req, res) => {
  res.json({ 
    success: true, 
    message: 'ILab UNMUL API is running', 
    database: 'connected',
    timestamp: new Date().toISOString() 
  });
});

// API route groups
router.use('/auth', authRoutes);
router.use('/users', userRoutes);
router.use('/roles', roleRoutes);
router.use('/equipment', equipmentRoutes);
router.use('/equipment-categories', equipmentCategoryRoutes);
router.use('/bookings', bookingRoutes);
router.use('/samples', sampleRoutes);
router.use('/payments', paymentRoutes);
router.use('/notifications', notificationRoutes);
router.use('/upload', uploadRoutes);

export default router;