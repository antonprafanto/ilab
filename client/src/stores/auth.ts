import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import { User } from '@ilab-unmul/shared'
import { authService } from '@/services/auth'
import toast from 'react-hot-toast'

interface AuthState {
  user: User | null
  accessToken: string | null
  isAuthenticated: boolean
  isLoading: boolean
  
  // Actions
  login: (email: string, password: string) => Promise<void>
  register: (userData: any) => Promise<void>
  logout: () => Promise<void>
  refreshToken: () => Promise<boolean>
  setUser: (user: User | null) => void
  setAccessToken: (token: string | null) => void
  initializeAuth: () => Promise<void>
  clearAuth: () => void
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      user: null,
      accessToken: null,
      isAuthenticated: false,
      isLoading: false,

      login: async (email: string, password: string) => {
        try {
          set({ isLoading: true })
          
          const response = await authService.login({ email, password })
          
          set({
            user: response.user,
            accessToken: response.accessToken,
            isAuthenticated: true,
            isLoading: false
          })
          
          toast.success('Login berhasil!')
        } catch (error: any) {
          set({ isLoading: false })
          const message = error.response?.data?.error?.message || 'Login gagal'
          toast.error(message)
          throw error
        }
      },

      register: async (userData: any) => {
        try {
          set({ isLoading: true })
          
          await authService.register(userData)
          
          set({ isLoading: false })
          toast.success('Registrasi berhasil! Silakan cek email untuk verifikasi.')
        } catch (error: any) {
          set({ isLoading: false })
          const message = error.response?.data?.error?.message || 'Registrasi gagal'
          toast.error(message)
          throw error
        }
      },

      logout: async () => {
        try {
          await authService.logout()
        } catch (error) {
          // Even if logout fails on server, clear local state
          console.error('Logout error:', error)
        } finally {
          set({
            user: null,
            accessToken: null,
            isAuthenticated: false
          })
          toast.success('Logout berhasil')
        }
      },

      refreshToken: async () => {
        try {
          const response = await authService.refreshToken()
          
          set({
            accessToken: response.accessToken,
            isAuthenticated: true
          })
          
          return true
        } catch (error) {
          // If refresh fails, clear auth state
          get().clearAuth()
          return false
        }
      },

      setUser: (user: User | null) => {
        set({ user, isAuthenticated: !!user })
      },

      setAccessToken: (token: string | null) => {
        set({ accessToken: token, isAuthenticated: !!token })
      },

      initializeAuth: async () => {
        const { accessToken } = get()
        
        if (!accessToken) {
          return
        }

        try {
          set({ isLoading: true })
          
          // Try to get current user to validate token
          const user = await authService.getCurrentUser()
          
          set({
            user,
            isAuthenticated: true,
            isLoading: false
          })
        } catch (error) {
          // If getting current user fails, try to refresh token
          const refreshSuccess = await get().refreshToken()
          
          if (refreshSuccess) {
            try {
              const user = await authService.getCurrentUser()
              set({ user, isLoading: false })
            } catch (err) {
              get().clearAuth()
              set({ isLoading: false })
            }
          } else {
            set({ isLoading: false })
          }
        }
      },

      clearAuth: () => {
        set({
          user: null,
          accessToken: null,
          isAuthenticated: false
        })
      }
    }),
    {
      name: 'ilab-auth-storage',
      partialize: (state) => ({
        accessToken: state.accessToken,
        user: state.user
      })
    }
  )
)