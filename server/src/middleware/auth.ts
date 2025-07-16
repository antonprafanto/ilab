import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { config } from '../config';
import { AuthenticationError, AuthorizationError } from '../utils/errors';
import { logger } from '../utils/logger';
import { authService } from '../services/auth';
import { ROLE_PERMISSIONS, ROLE_HIERARCHY } from '@ilab-unmul/shared';

// Extend Request interface to include user
declare global {
  namespace Express {
    interface Request {
      user?: {
        userId: string;
        email: string;
        role: string;
        iat?: number;
        exp?: number;
      };
    }
  }
}

// JWT authentication middleware
export const authenticate = async (req: Request, res: Response, next: NextFunction) => {
  try {
    const authHeader = req.headers.authorization;
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
      throw new AuthenticationError('Access token required');
    }

    // Verify token
    const decoded = authService.verifyToken(token);
    
    // Check if user still exists and is active
    const user = await authService.getUserById(decoded.userId);
    if (!user || user.status !== 'active') {
      throw new AuthenticationError('User account not found or inactive');
    }

    // Add user info to request
    req.user = {
      userId: decoded.userId,
      email: decoded.email,
      role: decoded.role,
      iat: decoded.iat,
      exp: decoded.exp
    };

    next();
  } catch (error) {
    next(error);
  }
};

// Role authorization middleware
export const authorize = (requiredRoles: string[]) => {
  return (req: Request, res: Response, next: NextFunction) => {
    try {
      if (!req.user) {
        throw new AuthenticationError('Authentication required');
      }

      const userRole = req.user.role;
      
      // Check if user has one of the required roles
      if (!requiredRoles.includes(userRole)) {
        logger.warn('Authorization failed:', {
          userId: req.user.userId,
          userRole,
          requiredRoles,
          url: req.url,
          method: req.method
        });
        
        throw new AuthorizationError('Insufficient permissions for this action');
      }

      next();
    } catch (error) {
      next(error);
    }
  };
};

// Permission-based authorization middleware
export const requirePermission = (permission: string) => {
  return (req: Request, res: Response, next: NextFunction) => {
    try {
      if (!req.user) {
        throw new AuthenticationError('Authentication required');
      }

      const userRole = req.user.role;
      const rolePermissions = ROLE_PERMISSIONS[userRole as keyof typeof ROLE_PERMISSIONS];
      
      if (!rolePermissions || !rolePermissions.includes(permission)) {
        logger.warn('Permission denied:', {
          userId: req.user.userId,
          userRole,
          requiredPermission: permission,
          userPermissions: rolePermissions,
          url: req.url,
          method: req.method
        });
        
        throw new AuthorizationError(`Missing permission: ${permission}`);
      }

      next();
    } catch (error) {
      next(error);
    }
  };
};

// Hierarchical role authorization (user must have equal or higher role level)
export const requireMinimumRole = (minimumRole: string) => {
  return (req: Request, res: Response, next: NextFunction) => {
    try {
      if (!req.user) {
        throw new AuthenticationError('Authentication required');
      }

      const userRole = req.user.role;
      const userLevel = ROLE_HIERARCHY[userRole as keyof typeof ROLE_HIERARCHY] || 0;
      const requiredLevel = ROLE_HIERARCHY[minimumRole as keyof typeof ROLE_HIERARCHY] || 0;
      
      if (userLevel < requiredLevel) {
        logger.warn('Insufficient role level:', {
          userId: req.user.userId,
          userRole,
          userLevel,
          minimumRole,
          requiredLevel,
          url: req.url,
          method: req.method
        });
        
        throw new AuthorizationError(`Minimum role required: ${minimumRole}`);
      }

      next();
    } catch (error) {
      next(error);
    }
  };
};

// Resource ownership authorization (user can only access their own resources)
export const requireOwnership = (getUserIdFromParams?: (req: Request) => string) => {
  return (req: Request, res: Response, next: NextFunction) => {
    try {
      if (!req.user) {
        throw new AuthenticationError('Authentication required');
      }

      const requestUserId = getUserIdFromParams 
        ? getUserIdFromParams(req)
        : req.params.userId || req.body.userId;
      
      // Admin users can access any resource
      const userRole = req.user.role;
      if (userRole === 'admin' || userRole === 'director') {
        return next();
      }

      // Check ownership
      if (req.user.userId !== requestUserId) {
        logger.warn('Ownership check failed:', {
          userId: req.user.userId,
          requestUserId,
          url: req.url,
          method: req.method
        });
        
        throw new AuthorizationError('You can only access your own resources');
      }

      next();
    } catch (error) {
      next(error);
    }
  };
};

// Optional authentication (doesn't fail if no token)
export const optionalAuth = async (req: Request, res: Response, next: NextFunction) => {
  try {
    const authHeader = req.headers.authorization;
    const token = authHeader && authHeader.split(' ')[1];

    if (token) {
      try {
        const decoded = authService.verifyToken(token);
        const user = await authService.getUserById(decoded.userId);
        
        if (user && user.status === 'active') {
          req.user = {
            userId: decoded.userId,
            email: decoded.email,
            role: decoded.role,
            iat: decoded.iat,
            exp: decoded.exp
          };
        }
      } catch (error) {
        // Ignore token errors in optional auth
        logger.debug('Optional auth token verification failed:', error);
      }
    }

    next();
  } catch (error) {
    // Don't fail on optional auth errors
    next();
  }
};

// Middleware to check if user's email is verified
export const requireEmailVerification = (req: Request, res: Response, next: NextFunction) => {
  try {
    if (!req.user) {
      throw new AuthenticationError('Authentication required');
    }

    // Get user details to check email verification
    authService.getUserById(req.user.userId)
      .then(user => {
        if (!user?.isEmailVerified) {
          throw new AuthenticationError('Email verification required');
        }
        next();
      })
      .catch(next);
  } catch (error) {
    next(error);
  }
};