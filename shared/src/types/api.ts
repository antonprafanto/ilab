import { z } from 'zod';

export const ApiResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.any().optional(),
  error: z.object({
    code: z.string(),
    message: z.string(),
    details: z.any().optional()
  }).optional()
});

export type ApiResponse<T = any> = {
  success: boolean;
  message?: string;
  data?: T;
  error?: {
    code: string;
    message: string;
    details?: any;
  };
};

export const PaginationSchema = z.object({
  page: z.number().positive(),
  limit: z.number().positive().max(100),
  total: z.number().min(0),
  totalPages: z.number().min(0)
});

export type Pagination = z.infer<typeof PaginationSchema>;

export const PaginatedResponseSchema = z.object({
  success: z.boolean(),
  message: z.string().optional(),
  data: z.array(z.any()),
  pagination: PaginationSchema,
  error: z.object({
    code: z.string(),
    message: z.string(),
    details: z.any().optional()
  }).optional()
});

export type PaginatedResponse<T = any> = {
  success: boolean;
  message?: string;
  data: T[];
  pagination: Pagination;
  error?: {
    code: string;
    message: string;
    details?: any;
  };
};

export const QueryParamsSchema = z.object({
  page: z.number().positive().optional(),
  limit: z.number().positive().max(100).optional(),
  search: z.string().optional(),
  sortBy: z.string().optional(),
  sortOrder: z.enum(['asc', 'desc']).optional(),
  filters: z.record(z.any()).optional()
});

export type QueryParams = z.infer<typeof QueryParamsSchema>;

export enum ApiErrorCode {
  VALIDATION_ERROR = 'VALIDATION_ERROR',
  AUTHENTICATION_ERROR = 'AUTHENTICATION_ERROR',
  AUTHORIZATION_ERROR = 'AUTHORIZATION_ERROR',
  NOT_FOUND = 'NOT_FOUND',
  CONFLICT = 'CONFLICT',
  INTERNAL_ERROR = 'INTERNAL_ERROR',
  BAD_REQUEST = 'BAD_REQUEST',
  RATE_LIMIT = 'RATE_LIMIT',
  SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE'
}

export const FileUploadSchema = z.object({
  fieldname: z.string(),
  originalname: z.string(),
  encoding: z.string(),
  mimetype: z.string(),
  destination: z.string(),
  filename: z.string(),
  path: z.string(),
  size: z.number()
});

export type FileUpload = z.infer<typeof FileUploadSchema>;