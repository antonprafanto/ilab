import { Router } from 'express';
import { body } from 'express-validator';
import { authController } from '../controllers/auth';
import { validate, authRateLimit, authenticate } from '../middleware';

const router = Router();

// Apply auth rate limiting to all auth routes
router.use(authRateLimit);

// Login
router.post('/login',
  validate([
    body('email')
      .isEmail()
      .normalizeEmail()
      .withMessage('Valid email is required'),
    body('password')
      .isLength({ min: 1 })
      .withMessage('Password is required')
  ]),
  authController.login
);

// Register
router.post('/register',
  validate([
    body('email')
      .isEmail()
      .normalizeEmail()
      .withMessage('Valid email is required'),
    body('password')
      .isLength({ min: 8 })
      .withMessage('Password must be at least 8 characters'),
    body('confirmPassword')
      .custom((value, { req }) => {
        if (value !== req.body.password) {
          throw new Error('Passwords do not match');
        }
        return true;
      }),
    body('firstName')
      .isLength({ min: 1 })
      .trim()
      .withMessage('First name is required'),
    body('lastName')
      .isLength({ min: 1 })
      .trim()
      .withMessage('Last name is required'),
    body('role')
      .isIn(['lecturer', 'student', 'external'])
      .withMessage('Valid role is required'),
    body('phoneNumber')
      .optional()
      .isMobilePhone('id-ID')
      .withMessage('Valid Indonesian phone number required'),
    body('faculty')
      .optional()
      .isLength({ min: 1 })
      .trim(),
    body('department')
      .optional()
      .isLength({ min: 1 })
      .trim(),
    body('nim')
      .optional()
      .isLength({ min: 1 })
      .trim(),
    body('studentId')
      .optional()
      .isLength({ min: 1 })
      .trim(),
    body('institution')
      .optional()
      .isLength({ min: 1 })
      .trim()
  ]),
  authController.register
);

// Refresh token
router.post('/refresh',
  validate([
    body('refreshToken')
      .isLength({ min: 1 })
      .withMessage('Refresh token is required')
  ]),
  authController.refreshToken
);

// Logout
router.post('/logout',
  validate([
    body('refreshToken')
      .optional()
      .isLength({ min: 1 })
  ]),
  authController.logout
);

// Get current user
router.get('/me',
  authenticate,
  authController.getCurrentUser
);

// Verify email
router.post('/verify-email',
  validate([
    body('token')
      .isLength({ min: 1 })
      .withMessage('Verification token is required')
  ]),
  authController.verifyEmail
);

// Resend verification email
router.post('/resend-verification',
  validate([
    body('email')
      .isEmail()
      .normalizeEmail()
      .withMessage('Valid email is required')
  ]),
  authController.resendVerification
);

// Forgot password
router.post('/forgot-password',
  validate([
    body('email')
      .isEmail()
      .normalizeEmail()
      .withMessage('Valid email is required')
  ]),
  authController.forgotPassword
);

// Reset password
router.post('/reset-password',
  validate([
    body('token')
      .isLength({ min: 1 })
      .withMessage('Reset token is required'),
    body('password')
      .isLength({ min: 8 })
      .withMessage('Password must be at least 8 characters'),
    body('confirmPassword')
      .custom((value, { req }) => {
        if (value !== req.body.password) {
          throw new Error('Passwords do not match');
        }
        return true;
      })
  ]),
  authController.resetPassword
);

export default router;