import { Request, Response, NextFunction } from 'express';
import rateLimit from 'express-rate-limit';
import helmet from 'helmet';
import { config } from '../config';
import { logger } from '../utils/logger';
import { RateLimitError } from '../utils/errors';

// Rate limiting middleware
export const createRateLimit = (
  windowMs?: number,
  max?: number,
  message?: string
) => {
  return rateLimit({
    windowMs: (windowMs || config.security.rateLimitWindow) * 60 * 1000, // minutes to ms
    max: max || config.security.rateLimitMax,
    message: {
      success: false,
      error: {
        code: 'RATE_LIMIT',
        message: message || 'Too many requests, please try again later'
      }
    },
    standardHeaders: true,
    legacyHeaders: false,
    handler: (req: Request, res: Response) => {
      logger.warn('Rate limit exceeded:', {
        ip: req.ip,
        userAgent: req.get('User-Agent'),
        url: req.url,
        method: req.method
      });
      
      throw new RateLimitError(message);
    }
  });
};

// General rate limiter
export const generalRateLimit = createRateLimit();

// Auth rate limiter (stricter)
export const authRateLimit = createRateLimit(
  15, // 15 minutes
  5,  // 5 attempts
  'Too many authentication attempts, please try again later'
);

// API rate limiter
export const apiRateLimit = createRateLimit(
  15, // 15 minutes
  100 // 100 requests
);

// Helmet security middleware
export const securityHeaders = helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      styleSrc: ["'self'", "'unsafe-inline'", "fonts.googleapis.com"],
      fontSrc: ["'self'", "fonts.gstatic.com"],
      imgSrc: ["'self'", "data:", "https:"],
      scriptSrc: ["'self'"],
      connectSrc: ["'self'"],
      frameSrc: ["'none'"],
      objectSrc: ["'none'"],
      baseUri: ["'self'"],
      formAction: ["'self'"],
      frameAncestors: ["'none'"],
      upgradeInsecureRequests: []
    }
  },
  crossOriginEmbedderPolicy: false,
  crossOriginResourcePolicy: { policy: "cross-origin" }
});

// CORS security check
export const corsSecurityCheck = (req: Request, res: Response, next: NextFunction) => {
  const origin = req.get('Origin');
  const allowedOrigins = [
    config.app.frontendUrl,
    config.app.url,
    'http://localhost:3000',
    'http://localhost:3001'
  ];

  // Allow requests without origin (mobile apps, Postman, etc.)
  if (!origin) {
    return next();
  }

  // Check if origin is allowed
  if (allowedOrigins.includes(origin)) {
    return next();
  }

  logger.warn('CORS violation detected:', {
    origin,
    ip: req.ip,
    userAgent: req.get('User-Agent'),
    url: req.url
  });

  res.status(403).json({
    success: false,
    error: {
      code: 'AUTHORIZATION_ERROR',
      message: 'Origin not allowed'
    }
  });
};

// Request sanitization
export const sanitizeRequest = (req: Request, res: Response, next: NextFunction) => {
  // Remove potential XSS payloads
  const sanitizeString = (str: string): string => {
    return str
      .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
      .replace(/javascript:/gi, '')
      .replace(/on\w+\s*=/gi, '')
      .trim();
  };

  const sanitizeObject = (obj: any): any => {
    if (typeof obj === 'string') {
      return sanitizeString(obj);
    }
    
    if (typeof obj === 'object' && obj !== null) {
      if (Array.isArray(obj)) {
        return obj.map(sanitizeObject);
      }
      
      const sanitized: any = {};
      for (const key in obj) {
        if (obj.hasOwnProperty(key)) {
          sanitized[key] = sanitizeObject(obj[key]);
        }
      }
      return sanitized;
    }
    
    return obj;
  };

  // Sanitize request body
  if (req.body) {
    req.body = sanitizeObject(req.body);
  }

  // Sanitize query parameters
  if (req.query) {
    req.query = sanitizeObject(req.query);
  }

  next();
};

// IP whitelist for admin routes
export const ipWhitelist = (allowedIPs: string[]) => {
  return (req: Request, res: Response, next: NextFunction) => {
    const clientIP = req.ip || req.connection.remoteAddress;
    
    if (config.app.env === 'development') {
      return next(); // Skip in development
    }

    if (allowedIPs.includes(clientIP || '')) {
      return next();
    }

    logger.warn('IP not whitelisted:', {
      ip: clientIP,
      url: req.url,
      userAgent: req.get('User-Agent')
    });

    res.status(403).json({
      success: false,
      error: {
        code: 'AUTHORIZATION_ERROR',
        message: 'Access denied from this IP address'
      }
    });
  };
};

// Security headers for file uploads
export const uploadSecurityHeaders = (req: Request, res: Response, next: NextFunction) => {
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('X-Frame-Options', 'DENY');
  res.setHeader('X-XSS-Protection', '1; mode=block');
  next();
};