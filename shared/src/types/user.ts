import { z } from 'zod';

export enum UserRole {
  ADMIN = 'admin',
  DIRECTOR = 'director',
  VICE_DIRECTOR = 'vice_director',
  LAB_HEAD = 'lab_head',
  LABORAN = 'laboran',
  LECTURER = 'lecturer',
  STUDENT = 'student',
  EXTERNAL = 'external'
}

export enum UserStatus {
  PENDING = 'pending',
  ACTIVE = 'active',
  INACTIVE = 'inactive',
  SUSPENDED = 'suspended'
}

export const UserSchema = z.object({
  id: z.string().uuid(),
  email: z.string().email(),
  password: z.string().min(8),
  firstName: z.string().min(1),
  lastName: z.string().min(1),
  phoneNumber: z.string().optional(),
  role: z.nativeEnum(UserRole),
  status: z.nativeEnum(UserStatus),
  faculty: z.string().optional(),
  department: z.string().optional(),
  studentId: z.string().optional(),
  nim: z.string().optional(),
  institution: z.string().optional(),
  profilePicture: z.string().optional(),
  identityDocument: z.string().optional(),
  isEmailVerified: z.boolean().default(false),
  isDocumentVerified: z.boolean().default(false),
  createdAt: z.date(),
  updatedAt: z.date(),
  lastLoginAt: z.date().optional()
});

export type User = z.infer<typeof UserSchema>;

export const CreateUserSchema = UserSchema.omit({
  id: true,
  createdAt: true,
  updatedAt: true,
  lastLoginAt: true
});

export type CreateUser = z.infer<typeof CreateUserSchema>;

export const UpdateUserSchema = UserSchema.partial().omit({
  id: true,
  createdAt: true
});

export type UpdateUser = z.infer<typeof UpdateUserSchema>;

export const LoginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(1)
});

export type LoginCredentials = z.infer<typeof LoginSchema>;

export const RegisterSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8),
  confirmPassword: z.string().min(8),
  firstName: z.string().min(1),
  lastName: z.string().min(1),
  phoneNumber: z.string().optional(),
  role: z.nativeEnum(UserRole),
  faculty: z.string().optional(),
  department: z.string().optional(),
  studentId: z.string().optional(),
  nim: z.string().optional(),
  institution: z.string().optional()
}).refine((data) => data.password === data.confirmPassword, {
  message: "Passwords don't match",
  path: ["confirmPassword"]
});

export type RegisterData = z.infer<typeof RegisterSchema>;