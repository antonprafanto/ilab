import { Router } from 'express';
// Will be implemented in next phase
const router = Router();

router.get('/', (req, res) => {
  res.json({ message: 'Samples routes - Coming soon' });
});

export default router;