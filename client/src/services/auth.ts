import { User, LoginCredentials, RegisterData } from '@ilab-unmul/shared'
import { apiService } from './api'

interface LoginResponse {
  user: User
  accessToken: string
  expiresIn: number
}

interface RefreshTokenResponse {
  accessToken: string
  expiresIn: number
}

class AuthService {
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    return await apiService.post<LoginResponse>('/auth/login', credentials)
  }

  async register(userData: RegisterData): Promise<{ user: User; message: string }> {
    return await apiService.post('/auth/register', userData)
  }

  async logout(): Promise<void> {
    return await apiService.post('/auth/logout')
  }

  async refreshToken(): Promise<RefreshTokenResponse> {
    return await apiService.post<RefreshTokenResponse>('/auth/refresh')
  }

  async getCurrentUser(): Promise<User> {
    return await apiService.get<User>('/auth/me')
  }

  async verifyEmail(token: string): Promise<void> {
    return await apiService.post('/auth/verify-email', { token })
  }

  async resendVerification(email: string): Promise<void> {
    return await apiService.post('/auth/resend-verification', { email })
  }

  async forgotPassword(email: string): Promise<void> {
    return await apiService.post('/auth/forgot-password', { email })
  }

  async resetPassword(token: string, password: string, confirmPassword: string): Promise<void> {
    return await apiService.post('/auth/reset-password', {
      token,
      password,
      confirmPassword
    })
  }
}

export const authService = new AuthService()
export default authService