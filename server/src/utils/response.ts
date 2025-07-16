import { Response } from 'express';
import { ApiResponse, PaginatedResponse, Pagination } from '@ilab-unmul/shared';
import { logger } from './logger';

export class ResponseHelper {
  static success<T>(res: Response, data?: T, message?: string, statusCode: number = 200): Response {
    const response: ApiResponse<T> = {
      success: true,
      message,
      data
    };

    return res.status(statusCode).json(response);
  }

  static paginated<T>(
    res: Response, 
    data: T[], 
    pagination: Pagination, 
    message?: string
  ): Response {
    const response: PaginatedResponse<T> = {
      success: true,
      message,
      data,
      pagination
    };

    return res.status(200).json(response);
  }

  static error(
    res: Response, 
    message: string, 
    statusCode: number = 500, 
    code: string = 'INTERNAL_ERROR',
    details?: any
  ): Response {
    const response: ApiResponse = {
      success: false,
      error: {
        code,
        message,
        details
      }
    };

    // Log error for server errors (5xx)
    if (statusCode >= 500) {
      logger.error('Server error:', {
        message,
        code,
        details,
        statusCode
      });
    }

    return res.status(statusCode).json(response);
  }

  static created<T>(res: Response, data?: T, message?: string): Response {
    return this.success(res, data, message, 201);
  }

  static noContent(res: Response): Response {
    return res.status(204).send();
  }

  static badRequest(res: Response, message: string, details?: any): Response {
    return this.error(res, message, 400, 'BAD_REQUEST', details);
  }

  static unauthorized(res: Response, message: string = 'Authentication required'): Response {
    return this.error(res, message, 401, 'AUTHENTICATION_ERROR');
  }

  static forbidden(res: Response, message: string = 'Insufficient permissions'): Response {
    return this.error(res, message, 403, 'AUTHORIZATION_ERROR');
  }

  static notFound(res: Response, message: string = 'Resource not found'): Response {
    return this.error(res, message, 404, 'NOT_FOUND');
  }

  static conflict(res: Response, message: string, details?: any): Response {
    return this.error(res, message, 409, 'CONFLICT', details);
  }

  static validation(res: Response, message: string, details?: any): Response {
    return this.error(res, message, 422, 'VALIDATION_ERROR', details);
  }

  static internalError(res: Response, message: string = 'Internal server error'): Response {
    return this.error(res, message, 500, 'INTERNAL_ERROR');
  }
}

export default ResponseHelper;