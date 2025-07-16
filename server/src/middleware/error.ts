import { Request, Response, NextFunction } from 'express';
import { AppError, isOperationalError } from '../utils/errors';
import { ResponseHelper } from '../utils/response';
import { logger } from '../utils/logger';
import { config } from '../config';
import { ApiErrorCode } from '@ilab-unmul/shared';

export const errorHandler = (
  error: Error,
  req: Request,
  res: Response,
  next: NextFunction
): void => {
  let { message, statusCode, code } = error as AppError;

  // Handle known operational errors
  if (error instanceof AppError) {
    const appError = error as AppError;
    ResponseHelper.error(
      res,
      appError.message,
      appError.statusCode,
      appError.code,
      config.app.env === 'development' ? appError.details : undefined
    );
    return;
  }

  // Handle MySQL errors
  if (error.name === 'Error' && 'code' in error) {
    const mysqlError = error as any;
    
    switch (mysqlError.code) {
      case 'ER_DUP_ENTRY':
        statusCode = 409;
        code = ApiErrorCode.CONFLICT;
        message = 'Duplicate entry found';
        break;
      case 'ER_NO_REFERENCED_ROW_2':
        statusCode = 400;
        code = ApiErrorCode.BAD_REQUEST;
        message = 'Referenced record not found';
        break;
      case 'ER_ROW_IS_REFERENCED_2':
        statusCode = 400;
        code = ApiErrorCode.BAD_REQUEST;
        message = 'Cannot delete record as it is referenced by other records';
        break;
      case 'ECONNREFUSED':
        statusCode = 503;
        code = ApiErrorCode.SERVICE_UNAVAILABLE;
        message = 'Database connection failed';
        break;
      default:
        statusCode = 500;
        code = ApiErrorCode.INTERNAL_ERROR;
        message = 'Database error occurred';
    }
  }

  // Handle JWT errors
  if (error.name === 'JsonWebTokenError') {
    statusCode = 401;
    code = ApiErrorCode.AUTHENTICATION_ERROR;
    message = 'Invalid token';
  } else if (error.name === 'TokenExpiredError') {
    statusCode = 401;
    code = ApiErrorCode.AUTHENTICATION_ERROR;
    message = 'Token expired';
  }

  // Handle validation errors
  if (error.name === 'ValidationError') {
    statusCode = 400;
    code = ApiErrorCode.VALIDATION_ERROR;
    message = 'Validation failed';
  }

  // Handle multer errors
  if (error.name === 'MulterError') {
    statusCode = 400;
    code = 'BAD_REQUEST';
    
    switch ((error as any).code) {
      case 'LIMIT_FILE_SIZE':
        message = 'File too large';
        break;
      case 'LIMIT_FILE_COUNT':
        message = 'Too many files';
        break;
      case 'LIMIT_UNEXPECTED_FILE':
        message = 'Unexpected file field';
        break;
      default:
        message = 'File upload error';
    }
  }

  // Default to 500 server error
  if (!statusCode) {
    statusCode = 500;
    code = 'INTERNAL_ERROR';
    message = 'Something went wrong';
  }

  // Log error details
  logger.error('Error handled:', {
    message: error.message,
    stack: error.stack,
    statusCode,
    code,
    url: req.url,
    method: req.method,
    ip: req.ip,
    userAgent: req.get('User-Agent'),
    userId: (req as any).user?.id
  });

  // Send error response
  ResponseHelper.error(
    res,
    message,
    statusCode,
    code,
    config.app.env === 'development' ? {
      stack: error.stack,
      original: error.message
    } : undefined
  );
};

export const notFoundHandler = (req: Request, res: Response): void => {
  ResponseHelper.notFound(res, `Route ${req.originalUrl} not found`);
};

// Catch async errors
export const asyncHandler = (
  fn: (req: Request, res: Response, next: NextFunction) => Promise<any>
) => {
  return (req: Request, res: Response, next: NextFunction) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };
};

// Global uncaught exception handler
export const setupGlobalErrorHandling = (): void => {
  process.on('uncaughtException', (error: Error) => {
    logger.error('Uncaught Exception:', error);
    
    if (!isOperationalError(error)) {
      process.exit(1);
    }
  });

  process.on('unhandledRejection', (reason: unknown, promise: Promise<any>) => {
    logger.error('Unhandled Rejection at:', promise, 'reason:', reason);
    
    if (reason instanceof Error && !isOperationalError(reason)) {
      process.exit(1);
    }
  });

  process.on('SIGTERM', () => {
    logger.info('SIGTERM received, shutting down gracefully');
    process.exit(0);
  });

  process.on('SIGINT', () => {
    logger.info('SIGINT received, shutting down gracefully');
    process.exit(0);
  });
};