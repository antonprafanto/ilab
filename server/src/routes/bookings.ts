import { Router } from 'express';
// Will be implemented in next phase
const router = Router();

router.get('/', (req, res) => {
  res.json({ message: 'Bookings routes - Coming soon' });
});

export default router;