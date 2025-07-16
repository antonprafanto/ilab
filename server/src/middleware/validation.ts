import { Request, Response, NextFunction } from 'express';
import { validationResult, ValidationChain } from 'express-validator';
import { ValidationError } from '../utils/errors';
import { ResponseHelper } from '../utils/response';

// Middleware to handle validation results
export const handleValidation = (req: Request, res: Response, next: NextFunction) => {
  const errors = validationResult(req);
  
  if (!errors.isEmpty()) {
    const errorDetails = errors.array().map(error => ({
      field: error.type === 'field' ? (error as any).path : 'unknown',
      message: error.msg,
      value: error.type === 'field' ? (error as any).value : undefined
    }));

    throw new ValidationError('Validation failed', errorDetails);
  }
  
  next();
};

// Wrapper to combine validation rules with error handling
export const validate = (validations: ValidationChain[]) => {
  return async (req: Request, res: Response, next: NextFunction) => {
    // Run all validations
    await Promise.all(validations.map(validation => validation.run(req)));
    
    // Check for validation errors
    handleValidation(req, res, next);
  };
};

// Custom validation for UUID format
export const isValidUUID = (value: string): boolean => {
  const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
  return uuidRegex.test(value);
};

// Custom validation for Indonesian phone number
export const isValidPhoneNumber = (value: string): boolean => {
  const phoneRegex = /^(\+62|62|0)([0-9]{9,12})$/;
  return phoneRegex.test(value.replace(/\s|-/g, ''));
};

// Custom validation for NIM UNMUL format
export const isValidNIM = (value: string): boolean => {
  const nimRegex = /^[0-9]{4}[A-Z]{3,8}[0-9]{3,4}$/;
  return nimRegex.test(value.replace(/\s/g, ''));
};

// Custom validation for strong password
export const isStrongPassword = (value: string): boolean => {
  const minLength = 8;
  const hasUpperCase = /[A-Z]/.test(value);
  const hasLowerCase = /[a-z]/.test(value);
  const hasNumbers = /\d/.test(value);
  const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value);
  
  return value.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar;
};

// Custom validation for file types
export const isValidFileType = (mimetype: string, allowedTypes: string[]): boolean => {
  return allowedTypes.includes(mimetype);
};

// Custom validation for date range
export const isValidDateRange = (startDate: string, endDate: string): boolean => {
  const start = new Date(startDate);
  const end = new Date(endDate);
  return start < end && !isNaN(start.getTime()) && !isNaN(end.getTime());
};

// Custom validation for business hours
export const isWithinBusinessHours = (dateTime: string): boolean => {
  const date = new Date(dateTime);
  const day = date.getDay(); // 0 = Sunday, 6 = Saturday
  const hour = date.getHours();
  
  // Monday to Friday, 8 AM to 5 PM
  return day >= 1 && day <= 5 && hour >= 8 && hour <= 17;
};

// Pagination validation middleware
export const validatePagination = (req: Request, res: Response, next: NextFunction) => {
  const page = parseInt(req.query.page as string) || 1;
  const limit = parseInt(req.query.limit as string) || 10;
  
  if (page < 1) {
    return ResponseHelper.badRequest(res, 'Page must be greater than 0');
  }
  
  if (limit < 1 || limit > 100) {
    return ResponseHelper.badRequest(res, 'Limit must be between 1 and 100');
  }
  
  req.query.page = page.toString();
  req.query.limit = limit.toString();
  
  next();
};

// Request size validation
export const validateRequestSize = (maxSizeBytes: number) => {
  return (req: Request, res: Response, next: NextFunction) => {
    const contentLength = parseInt(req.get('Content-Length') || '0');
    
    if (contentLength > maxSizeBytes) {
      return ResponseHelper.badRequest(res, 'Request body too large');
    }
    
    next();
  };
};

// Content type validation
export const validateContentType = (allowedTypes: string[]) => {
  return (req: Request, res: Response, next: NextFunction) => {
    const contentType = req.get('Content-Type')?.split(';')[0];
    
    if (!contentType || !allowedTypes.includes(contentType)) {
      return ResponseHelper.badRequest(res, 'Invalid content type');
    }
    
    next();
  };
};