import { Request, Response } from 'express';
import { v4 as uuidv4 } from 'uuid';
import { ResponseHelper } from '../utils/response';
import { authService } from '../services/auth';
import { emailService } from '../services/email';
import { database } from '../config/database';
import { ValidationError, NotFoundError } from '../utils/errors';
import { logger } from '../utils/logger';
import { LoginCredentials, RegisterData } from '@ilab-unmul/shared';

class AuthController {
  async login(req: Request, res: Response): Promise<void> {
    const credentials: LoginCredentials = req.body;

    const result = await authService.login(credentials);
    
    // Set refresh token as httpOnly cookie
    res.cookie('refreshToken', result.tokens.refreshToken, {
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'strict',
      maxAge: 7 * 24 * 60 * 60 * 1000 // 7 days
    });

    ResponseHelper.success(res, {
      user: result.user,
      accessToken: result.tokens.accessToken,
      expiresIn: result.tokens.expiresIn
    }, 'Login successful');
  }

  async register(req: Request, res: Response): Promise<void> {
    const userData: RegisterData = req.body;

    const result = await authService.register(userData);
    
    // Send verification email
    try {
      await emailService.sendVerificationEmail(
        result.user.email,
        result.user.firstName,
        result.verificationToken
      );
    } catch (emailError) {
      logger.error('Failed to send verification email:', emailError);
      // Don't fail registration if email fails
    }

    ResponseHelper.created(res, {
      user: result.user,
      message: 'Registration successful. Please check your email to verify your account.'
    }, 'User registered successfully');
  }

  async refreshToken(req: Request, res: Response): Promise<void> {
    const { refreshToken } = req.body;
    const cookieRefreshToken = req.cookies.refreshToken;
    
    const tokenToUse = refreshToken || cookieRefreshToken;
    
    if (!tokenToUse) {
      throw new ValidationError('Refresh token required');
    }

    const tokens = await authService.refreshToken(tokenToUse);
    
    // Update refresh token cookie
    res.cookie('refreshToken', tokens.refreshToken, {
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'strict',
      maxAge: 7 * 24 * 60 * 60 * 1000 // 7 days
    });

    ResponseHelper.success(res, {
      accessToken: tokens.accessToken,
      expiresIn: tokens.expiresIn
    }, 'Token refreshed successfully');
  }

  async logout(req: Request, res: Response): Promise<void> {
    const { refreshToken } = req.body;
    const cookieRefreshToken = req.cookies.refreshToken;
    
    const tokenToUse = refreshToken || cookieRefreshToken;
    
    if (tokenToUse) {
      await authService.logout(tokenToUse);
    }
    
    // Clear refresh token cookie
    res.clearCookie('refreshToken');
    
    ResponseHelper.success(res, null, 'Logout successful');
  }

  async getCurrentUser(req: Request, res: Response): Promise<void> {
    const userId = req.user?.userId;
    
    if (!userId) {
      throw new ValidationError('User ID not found in token');
    }

    const user = await authService.getUserById(userId);
    
    if (!user) {
      throw new NotFoundError('User not found');
    }

    ResponseHelper.success(res, user, 'User retrieved successfully');
  }

  async verifyEmail(req: Request, res: Response): Promise<void> {
    const { token } = req.body;

    const user = await authService.verifyEmail(token);
    
    // Send welcome email
    try {
      await emailService.sendWelcomeEmail(
        user.email,
        user.first_name
      );
    } catch (emailError) {
      logger.error('Failed to send welcome email:', emailError);
      // Don't fail verification if email fails
    }

    ResponseHelper.success(res, null, 'Email verified successfully');
  }

  async resendVerification(req: Request, res: Response): Promise<void> {
    const { email } = req.body;

    // Check if user exists and is not verified
    const [users] = await database.execute(
      'SELECT id, first_name, is_email_verified FROM users WHERE email = ?',
      [email]
    );

    if (!Array.isArray(users) || users.length === 0) {
      throw new NotFoundError('User with this email not found');
    }

    const user = users[0] as any;

    if (user.is_email_verified) {
      throw new ValidationError('Email is already verified');
    }

    // Generate new verification token
    const verificationToken = uuidv4();
    
    await database.execute(
      'UPDATE users SET email_verification_token = ? WHERE id = ?',
      [verificationToken, user.id]
    );

    // Send verification email
    await emailService.sendVerificationEmail(
      email,
      user.first_name,
      verificationToken
    );

    ResponseHelper.success(res, null, 'Verification email sent successfully');
  }

  async forgotPassword(req: Request, res: Response): Promise<void> {
    const { email } = req.body;

    // Check if user exists
    const [users] = await database.execute(
      'SELECT id, first_name FROM users WHERE email = ? AND status = "active"',
      [email]
    );

    if (!Array.isArray(users) || users.length === 0) {
      // Don't reveal if email exists or not for security
      ResponseHelper.success(res, null, 'If the email exists, a reset link has been sent');
      return;
    }

    const user = users[0] as any;

    // Generate reset token
    const resetToken = uuidv4();
    const expiresAt = new Date(Date.now() + 60 * 60 * 1000); // 1 hour

    await database.execute(
      'UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?',
      [resetToken, expiresAt, user.id]
    );

    // Send reset email
    try {
      await emailService.sendPasswordResetEmail(
        email,
        user.first_name,
        resetToken
      );
    } catch (emailError) {
      logger.error('Failed to send password reset email:', emailError);
      // Still return success to not reveal if email exists
    }

    ResponseHelper.success(res, null, 'If the email exists, a reset link has been sent');
  }

  async resetPassword(req: Request, res: Response): Promise<void> {
    const { token, password } = req.body;

    // Find user with valid reset token
    const [users] = await database.execute(
      'SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()',
      [token]
    );

    if (!Array.isArray(users) || users.length === 0) {
      throw new ValidationError('Invalid or expired reset token');
    }

    const user = users[0] as any;

    // Hash new password
    const passwordHash = await authService.hashPassword(password);

    // Update password and clear reset token
    await database.execute(`
      UPDATE users 
      SET password_hash = ?, 
          password_reset_token = NULL, 
          password_reset_expires = NULL 
      WHERE id = ?
    `, [passwordHash, user.id]);

    // Invalidate all user sessions
    await database.execute(
      'UPDATE user_sessions SET is_active = false WHERE user_id = ?',
      [user.id]
    );

    ResponseHelper.success(res, null, 'Password reset successfully');
  }
}

export const authController = new AuthController();