import { ReactNode } from 'react'
import { Navigate, useLocation } from 'react-router-dom'
import { useAuthStore } from '@/stores/auth'

interface ProtectedRouteProps {
  children: ReactNode
  requiredRoles?: string[]
  requireEmailVerification?: boolean
}

export const ProtectedRoute = ({ 
  children, 
  requiredRoles,
  requireEmailVerification = false 
}: ProtectedRouteProps) => {
  const { isAuthenticated, user, isLoading } = useAuthStore()
  const location = useLocation()

  // Show loading state while checking auth
  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-unmul-primary"></div>
      </div>
    )
  }

  // Not authenticated - redirect to login
  if (!isAuthenticated || !user) {
    return <Navigate to="/auth/login" state={{ from: location }} replace />
  }

  // Check email verification requirement
  if (requireEmailVerification && !user.isEmailVerified) {
    return <Navigate to="/verify-email" replace />
  }

  // Check role requirements
  if (requiredRoles && !requiredRoles.includes(user.role)) {
    return <Navigate to="/dashboard" replace />
  }

  return <>{children}</>
}