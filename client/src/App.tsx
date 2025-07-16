import { Routes, Route } from 'react-router-dom'
import { useAuthStore } from '@/stores/auth'
import { useEffect } from 'react'

// Layouts
import { AuthLayout } from '@/components/layout/AuthLayout'
import { DashboardLayout } from '@/components/layout/DashboardLayout'
import { PublicLayout } from '@/components/layout/PublicLayout'

// Pages
import HomePage from '@/pages/HomePage'
import LoginPage from '@/pages/auth/LoginPage'
import RegisterPage from '@/pages/auth/RegisterPage'
import ForgotPasswordPage from '@/pages/auth/ForgotPasswordPage'
import ResetPasswordPage from '@/pages/auth/ResetPasswordPage'
import VerifyEmailPage from '@/pages/auth/VerifyEmailPage'
import DashboardPage from '@/pages/dashboard/DashboardPage'
import ProfilePage from '@/pages/dashboard/ProfilePage'
import NotFoundPage from '@/pages/NotFoundPage'

// Protected Route Component
import { ProtectedRoute } from '@/components/ProtectedRoute'

function App() {
  const { initializeAuth } = useAuthStore()

  useEffect(() => {
    // Initialize authentication state
    initializeAuth()
  }, [initializeAuth])

  return (
    <Routes>
      {/* Public Routes */}
      <Route path="/" element={<PublicLayout />}>
        <Route index element={<HomePage />} />
        <Route path="verify-email" element={<VerifyEmailPage />} />
      </Route>

      {/* Auth Routes */}
      <Route path="/auth" element={<AuthLayout />}>
        <Route path="login" element={<LoginPage />} />
        <Route path="register" element={<RegisterPage />} />
        <Route path="forgot-password" element={<ForgotPasswordPage />} />
        <Route path="reset-password" element={<ResetPasswordPage />} />
      </Route>

      {/* Protected Dashboard Routes */}
      <Route
        path="/dashboard"
        element={
          <ProtectedRoute>
            <DashboardLayout />
          </ProtectedRoute>
        }
      >
        <Route index element={<DashboardPage />} />
        <Route path="profile" element={<ProfilePage />} />
        {/* More dashboard routes will be added in next phases */}
      </Route>

      {/* Catch all route */}
      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  )
}

export default App