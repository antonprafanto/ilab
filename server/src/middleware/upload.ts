import multer from 'multer';
import path from 'path';
import { Request } from 'express';
import { config } from '../config';
import { v4 as uuidv4 } from 'uuid';
import { BadRequestError } from '../utils/errors';
import { logger } from '../utils/logger';

// Storage configuration
const storage = multer.diskStorage({
  destination: (req: Request, file: Express.Multer.File, cb) => {
    const uploadPath = path.join(process.cwd(), config.upload.directory);
    cb(null, uploadPath);
  },
  filename: (req: Request, file: Express.Multer.File, cb) => {
    const uniqueSuffix = uuidv4();
    const extension = path.extname(file.originalname);
    const filename = `${uniqueSuffix}${extension}`;
    cb(null, filename);
  }
});

// File filter
const fileFilter = (req: Request, file: Express.Multer.File, cb: multer.FileFilterCallback) => {
  const allowedMimeTypes = [
    'image/jpeg',
    'image/jpg', 
    'image/png',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
  ];

  if (allowedMimeTypes.includes(file.mimetype)) {
    cb(null, true);
  } else {
    logger.warn('File upload rejected:', {
      filename: file.originalname,
      mimetype: file.mimetype,
      size: file.size
    });
    cb(new BadRequestError(`File type ${file.mimetype} not allowed`));
  }
};

// Base multer configuration
const upload = multer({
  storage,
  fileFilter,
  limits: {
    fileSize: config.upload.maxFileSize,
    files: 5 // Max 5 files per request
  }
});

// Single file upload
export const uploadSingle = (fieldName: string) => upload.single(fieldName);

// Multiple files upload
export const uploadMultiple = (fieldName: string, maxCount: number = 5) => 
  upload.array(fieldName, maxCount);

// Upload with specific field names
export const uploadFields = (fields: { name: string; maxCount: number }[]) => 
  upload.fields(fields);

// Profile picture upload (smaller size limit)
export const uploadProfilePicture = multer({
  storage,
  fileFilter: (req: Request, file: Express.Multer.File, cb: multer.FileFilterCallback) => {
    const allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    
    if (allowedMimeTypes.includes(file.mimetype)) {
      cb(null, true);
    } else {
      cb(new BadRequestError('Only JPEG and PNG images are allowed for profile pictures'));
    }
  },
  limits: {
    fileSize: 2 * 1024 * 1024, // 2MB for profile pictures
    files: 1
  }
}).single('profilePicture');

// Document upload (identity documents, etc.)
export const uploadDocument = multer({
  storage,
  fileFilter: (req: Request, file: Express.Multer.File, cb: multer.FileFilterCallback) => {
    const allowedMimeTypes = [
      'image/jpeg',
      'image/jpg', 
      'image/png',
      'application/pdf'
    ];
    
    if (allowedMimeTypes.includes(file.mimetype)) {
      cb(null, true);
    } else {
      cb(new BadRequestError('Only JPEG, PNG, and PDF files are allowed for documents'));
    }
  },
  limits: {
    fileSize: 5 * 1024 * 1024, // 5MB for documents
    files: 1
  }
}).single('document');

// Result files upload (lab results, analysis files)
export const uploadResultFiles = multer({
  storage,
  fileFilter,
  limits: {
    fileSize: config.upload.maxFileSize,
    files: 10 // Max 10 result files
  }
}).array('resultFiles', 10);

export default upload;