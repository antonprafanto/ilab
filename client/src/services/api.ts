import axios, { 
  AxiosInstance, 
  AxiosResponse, 
  AxiosError, 
  InternalAxiosRequestConfig 
} from 'axios'
import { ApiResponse, PaginatedResponse } from '@ilab-unmul/shared'
import toast from 'react-hot-toast'

class ApiService {
  private instance: AxiosInstance

  constructor() {
    this.instance = axios.create({
      baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:3001/api/v1',
      timeout: parseInt(import.meta.env.VITE_API_TIMEOUT || '10000'),
      withCredentials: true, // Important for cookies
      headers: {
        'Content-Type': 'application/json',
      }
    })

    this.setupInterceptors()
  }

  private setupInterceptors() {
    // Request interceptor to add auth token
    this.instance.interceptors.request.use(
      (config: InternalAxiosRequestConfig) => {
        const token = this.getAccessToken()
        if (token) {
          config.headers.Authorization = `Bearer ${token}`
        }
        return config
      },
      (error) => {
        return Promise.reject(error)
      }
    )

    // Response interceptor to handle token refresh
    this.instance.interceptors.response.use(
      (response: AxiosResponse) => {
        return response
      },
      async (error: AxiosError) => {
        const originalRequest = error.config as InternalAxiosRequestConfig & {
          _retry?: boolean
        }

        // If error is 401 and we haven't already tried to refresh
        if (error.response?.status === 401 && !originalRequest._retry) {
          originalRequest._retry = true

          try {
            // Try to refresh token
            await this.refreshToken()
            
            // Retry original request with new token
            const token = this.getAccessToken()
            if (token) {
              originalRequest.headers.Authorization = `Bearer ${token}`
            }
            
            return this.instance(originalRequest)
          } catch (refreshError) {
            // Refresh failed, redirect to login
            this.handleAuthError()
            return Promise.reject(refreshError)
          }
        }

        // Handle other errors
        this.handleError(error)
        return Promise.reject(error)
      }
    )
  }

  private getAccessToken(): string | null {
    try {
      const authStorage = localStorage.getItem('ilab-auth-storage')
      if (authStorage) {
        const parsed = JSON.parse(authStorage)
        return parsed.state?.accessToken || null
      }
    } catch (error) {
      console.error('Error getting access token:', error)
    }
    return null
  }

  private async refreshToken(): Promise<void> {
    try {
      const response = await axios.post(
        `${import.meta.env.VITE_API_BASE_URL}/auth/refresh`,
        {},
        { withCredentials: true }
      )

      if (response.data.success) {
        // Update token in store
        const authStorage = localStorage.getItem('ilab-auth-storage')
        if (authStorage) {
          const parsed = JSON.parse(authStorage)
          parsed.state.accessToken = response.data.data.accessToken
          localStorage.setItem('ilab-auth-storage', JSON.stringify(parsed))
        }
      }
    } catch (error) {
      throw error
    }
  }

  private handleAuthError() {
    // Clear auth storage
    localStorage.removeItem('ilab-auth-storage')
    
    // Redirect to login (if not already there)
    if (!window.location.pathname.includes('/auth/login')) {
      window.location.href = '/auth/login'
    }
  }

  private handleError(error: AxiosError) {
    const response = error.response?.data as ApiResponse
    
    if (response?.error) {
      const message = response.error.message
      
      // Don't show toast for certain error types
      if (error.response?.status !== 401) {
        toast.error(message)
      }
    } else if (error.code === 'NETWORK_ERROR') {
      toast.error('Koneksi jaringan bermasalah')
    } else if (error.code === 'ECONNABORTED') {
      toast.error('Request timeout')
    } else {
      toast.error('Terjadi kesalahan yang tidak diketahui')
    }
  }

  // Generic request methods
  async get<T>(url: string, config?: any): Promise<T> {
    const response = await this.instance.get<ApiResponse<T>>(url, config)
    return response.data.data as T
  }

  async post<T>(url: string, data?: any, config?: any): Promise<T> {
    const response = await this.instance.post<ApiResponse<T>>(url, data, config)
    return response.data.data as T
  }

  async put<T>(url: string, data?: any, config?: any): Promise<T> {
    const response = await this.instance.put<ApiResponse<T>>(url, data, config)
    return response.data.data as T
  }

  async patch<T>(url: string, data?: any, config?: any): Promise<T> {
    const response = await this.instance.patch<ApiResponse<T>>(url, data, config)
    return response.data.data as T
  }

  async delete<T>(url: string, config?: any): Promise<T> {
    const response = await this.instance.delete<ApiResponse<T>>(url, config)
    return response.data.data as T
  }

  // Paginated requests
  async getPaginated<T>(url: string, config?: any): Promise<PaginatedResponse<T>> {
    const response = await this.instance.get<PaginatedResponse<T>>(url, config)
    return response.data
  }

  // File upload
  async uploadFile<T>(url: string, file: File, onProgress?: (progress: number) => void): Promise<T> {
    const formData = new FormData()
    formData.append('file', file)

    const response = await this.instance.post<ApiResponse<T>>(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: (progressEvent) => {
        if (onProgress && progressEvent.total) {
          const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
          onProgress(progress)
        }
      },
    })

    return response.data.data as T
  }

  // Download file
  async downloadFile(url: string, filename?: string): Promise<void> {
    const response = await this.instance.get(url, {
      responseType: 'blob',
    })

    const blob = new Blob([response.data])
    const downloadUrl = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = downloadUrl
    link.download = filename || 'download'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(downloadUrl)
  }

  // Get raw axios instance for special cases
  getInstance(): AxiosInstance {
    return this.instance
  }
}

export const apiService = new ApiService()
export default apiService