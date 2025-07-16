import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import { v4 as uuidv4 } from 'uuid';
import { config } from '../config';
import { database } from '../config/database';
import { 
  AuthenticationError, 
  ValidationError, 
  NotFoundError, 
  ConflictError 
} from '../utils/errors';
import { logger } from '../utils/logger';
import { UserRole, User, LoginCredentials, RegisterData } from '@ilab-unmul/shared';

interface TokenPayload {
  userId: string;
  email: string;
  role: string;
  iat?: number;
  exp?: number;
}

interface AuthTokens {
  accessToken: string;
  refreshToken: string;
  expiresIn: number;
}

class AuthService {
  // Generate JWT tokens
  generateTokens(user: User): AuthTokens {
    const payload: TokenPayload = {
      userId: user.id,
      email: user.email,
      role: user.role
    };

    const accessToken = jwt.sign(payload, config.jwt.secret, {
      expiresIn: config.jwt.expiresIn
    });

    const refreshToken = jwt.sign(payload, config.jwt.refreshSecret, {
      expiresIn: config.jwt.refreshExpiresIn
    });

    // Calculate expiration time in seconds
    const expiresIn = this.getTokenExpirationTime(config.jwt.expiresIn);

    return {
      accessToken,
      refreshToken,
      expiresIn
    };
  }

  // Verify JWT token
  verifyToken(token: string, isRefreshToken: boolean = false): TokenPayload {
    try {
      const secret = isRefreshToken ? config.jwt.refreshSecret : config.jwt.secret;
      const decoded = jwt.verify(token, secret) as TokenPayload;
      return decoded;
    } catch (error) {
      if (error instanceof jwt.TokenExpiredError) {
        throw new AuthenticationError('Token expired');
      } else if (error instanceof jwt.JsonWebTokenError) {
        throw new AuthenticationError('Invalid token');
      }
      throw new AuthenticationError('Token verification failed');
    }
  }

  // Hash password
  async hashPassword(password: string): Promise<string> {
    return await bcrypt.hash(password, config.security.bcryptRounds);
  }

  // Verify password
  async verifyPassword(password: string, hashedPassword: string): Promise<boolean> {
    return await bcrypt.compare(password, hashedPassword);
  }

  // User login
  async login(credentials: LoginCredentials): Promise<{ user: User; tokens: AuthTokens }> {
    const { email, password } = credentials;

    // Get user with role information
    const [users] = await database.execute(`
      SELECT u.*, r.name as role_name, r.display_name as role_display_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.email = ? AND u.status = 'active'
    `, [email]);

    if (!Array.isArray(users) || users.length === 0) {
      throw new AuthenticationError('Invalid email or password');
    }

    const user = users[0] as any;

    // Verify password
    const isPasswordValid = await this.verifyPassword(password, user.password_hash);
    if (!isPasswordValid) {
      logger.warn('Failed login attempt:', { email, ip: 'unknown' });
      throw new AuthenticationError('Invalid email or password');
    }

    // Check if email is verified
    if (!user.is_email_verified) {
      throw new AuthenticationError('Please verify your email before logging in');
    }

    // Update last login timestamp
    await database.execute(
      'UPDATE users SET last_login_at = NOW() WHERE id = ?',
      [user.id]
    );

    // Convert database user to User type
    const userObj: User = {
      id: user.id,
      email: user.email,
      firstName: user.first_name,
      lastName: user.last_name,
      phoneNumber: user.phone_number,
      role: user.role_name as UserRole,
      status: user.status,
      faculty: user.faculty,
      department: user.department,
      studentId: user.student_id,
      nim: user.nim,
      institution: user.institution,
      profilePicture: user.profile_picture,
      identityDocument: user.identity_document,
      isEmailVerified: user.is_email_verified,
      isDocumentVerified: user.is_document_verified,
      createdAt: user.created_at,
      updatedAt: user.updated_at,
      lastLoginAt: user.last_login_at
    };

    // Generate tokens
    const tokens = this.generateTokens(userObj);

    // Store refresh token in database
    await this.storeRefreshToken(user.id, tokens.refreshToken);

    logger.info('User logged in successfully:', { userId: user.id, email: user.email });

    return { user: userObj, tokens };
  }

  // User registration
  async register(userData: RegisterData): Promise<{ user: User; verificationToken: string }> {
    const { email, password, firstName, lastName, role, ...otherData } = userData;

    // Check if user already exists
    const [existingUsers] = await database.execute(
      'SELECT id FROM users WHERE email = ?',
      [email]
    );

    if (Array.isArray(existingUsers) && existingUsers.length > 0) {
      throw new ConflictError('User with this email already exists');
    }

    // Get role ID
    const [roles] = await database.execute(
      'SELECT id FROM roles WHERE name = ?',
      [role]
    );

    if (!Array.isArray(roles) || roles.length === 0) {
      throw new ValidationError('Invalid role specified');
    }

    const roleId = (roles[0] as any).id;

    // Hash password
    const passwordHash = await this.hashPassword(password);

    // Generate verification token
    const verificationToken = uuidv4();

    // Create user
    const userId = uuidv4();
    
    await database.execute(`
      INSERT INTO users (
        id, email, password_hash, first_name, last_name, phone_number,
        role_id, faculty, department, student_id, nim, institution,
        email_verification_token, status
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    `, [
      userId, email, passwordHash, firstName, lastName, otherData.phoneNumber || null,
      roleId, otherData.faculty || null, otherData.department || null, 
      otherData.studentId || null, otherData.nim || null, otherData.institution || null,
      verificationToken
    ]);

    // Get created user
    const [createdUsers] = await database.execute(`
      SELECT u.*, r.name as role_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.id = ?
    `, [userId]);

    const createdUser = (createdUsers as any[])[0];

    const userObj: User = {
      id: createdUser.id,
      email: createdUser.email,
      firstName: createdUser.first_name,
      lastName: createdUser.last_name,
      phoneNumber: createdUser.phone_number,
      role: createdUser.role_name as UserRole,
      status: createdUser.status,
      faculty: createdUser.faculty,
      department: createdUser.department,
      studentId: createdUser.student_id,
      nim: createdUser.nim,
      institution: createdUser.institution,
      profilePicture: createdUser.profile_picture,
      identityDocument: createdUser.identity_document,
      isEmailVerified: createdUser.is_email_verified,
      isDocumentVerified: createdUser.is_document_verified,
      createdAt: createdUser.created_at,
      updatedAt: createdUser.updated_at,
      lastLoginAt: createdUser.last_login_at
    };

    logger.info('User registered successfully:', { userId, email });

    return { user: userObj, verificationToken };
  }

  // Verify email
  async verifyEmail(token: string): Promise<User> {
    const [users] = await database.execute(
      'SELECT * FROM users WHERE email_verification_token = ?',
      [token]
    );

    if (!Array.isArray(users) || users.length === 0) {
      throw new ValidationError('Invalid verification token');
    }

    const user = users[0] as any;

    // Update user status
    await database.execute(`
      UPDATE users 
      SET is_email_verified = true, 
          email_verification_token = NULL,
          status = 'active'
      WHERE id = ?
    `, [user.id]);

    logger.info('Email verified successfully:', { userId: user.id, email: user.email });

    return user;
  }

  // Refresh token
  async refreshToken(refreshToken: string): Promise<AuthTokens> {
    // Verify refresh token
    const payload = this.verifyToken(refreshToken, true);

    // Check if refresh token exists in database
    const [sessions] = await database.execute(
      'SELECT * FROM user_sessions WHERE refresh_token = ? AND is_active = true AND expires_at > NOW()',
      [refreshToken]
    );

    if (!Array.isArray(sessions) || sessions.length === 0) {
      throw new AuthenticationError('Invalid refresh token');
    }

    // Get user
    const [users] = await database.execute(`
      SELECT u.*, r.name as role_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.id = ? AND u.status = 'active'
    `, [payload.userId]);

    if (!Array.isArray(users) || users.length === 0) {
      throw new AuthenticationError('User not found or inactive');
    }

    const user = users[0] as any;

    const userObj: User = {
      id: user.id,
      email: user.email,
      firstName: user.first_name,
      lastName: user.last_name,
      phoneNumber: user.phone_number,
      role: user.role_name as UserRole,
      status: user.status,
      faculty: user.faculty,
      department: user.department,
      studentId: user.student_id,
      nim: user.nim,
      institution: user.institution,
      profilePicture: user.profile_picture,
      identityDocument: user.identity_document,
      isEmailVerified: user.is_email_verified,
      isDocumentVerified: user.is_document_verified,
      createdAt: user.created_at,
      updatedAt: user.updated_at,
      lastLoginAt: user.last_login_at
    };

    // Generate new tokens
    const newTokens = this.generateTokens(userObj);

    // Update refresh token in database
    await database.execute(
      'UPDATE user_sessions SET refresh_token = ?, expires_at = DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE refresh_token = ?',
      [newTokens.refreshToken, refreshToken]
    );

    return newTokens;
  }

  // Logout
  async logout(refreshToken?: string): Promise<void> {
    if (refreshToken) {
      await database.execute(
        'UPDATE user_sessions SET is_active = false WHERE refresh_token = ?',
        [refreshToken]
      );
    }
  }

  // Store refresh token
  private async storeRefreshToken(userId: string, refreshToken: string): Promise<void> {
    const sessionId = uuidv4();
    
    await database.execute(`
      INSERT INTO user_sessions (id, user_id, refresh_token, expires_at)
      VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
    `, [sessionId, userId, refreshToken]);
  }

  // Get user by ID
  async getUserById(userId: string): Promise<User | null> {
    const [users] = await database.execute(`
      SELECT u.*, r.name as role_name
      FROM users u
      JOIN roles r ON u.role_id = r.id
      WHERE u.id = ?
    `, [userId]);

    if (!Array.isArray(users) || users.length === 0) {
      return null;
    }

    const user = users[0] as any;

    return {
      id: user.id,
      email: user.email,
      firstName: user.first_name,
      lastName: user.last_name,
      phoneNumber: user.phone_number,
      role: user.role_name as UserRole,
      status: user.status,
      faculty: user.faculty,
      department: user.department,
      studentId: user.student_id,
      nim: user.nim,
      institution: user.institution,
      profilePicture: user.profile_picture,
      identityDocument: user.identity_document,
      isEmailVerified: user.is_email_verified,
      isDocumentVerified: user.is_document_verified,
      createdAt: user.created_at,
      updatedAt: user.updated_at,
      lastLoginAt: user.last_login_at
    };
  }

  // Helper function to parse time string to seconds
  private getTokenExpirationTime(timeString: string): number {
    const unit = timeString.slice(-1);
    const value = parseInt(timeString.slice(0, -1));

    switch (unit) {
      case 's': return value;
      case 'm': return value * 60;
      case 'h': return value * 60 * 60;
      case 'd': return value * 24 * 60 * 60;
      default: return 3600; // 1 hour default
    }
  }
}

export const authService = new AuthService();