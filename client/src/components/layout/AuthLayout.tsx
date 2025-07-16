import { Outlet, Navigate } from 'react-router-dom'
import { useAuthStore } from '@/stores/auth'

export const AuthLayout = () => {
  const { isAuthenticated } = useAuthStore()

  // Redirect to dashboard if already authenticated
  if (isAuthenticated) {
    return <Navigate to="/dashboard" replace />
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-unmul-primary to-unmul-secondary">
      <div className="flex min-h-screen">
        {/* Left side - Branding */}
        <div className="hidden lg:flex lg:w-1/2 flex-col justify-center px-12 text-white">
          <div className="max-w-md">
            <div className="mb-8">
              <h1 className="text-4xl font-bold mb-2">ILab UNMUL</h1>
              <p className="text-xl text-blue-100">
                Integrated Laboratory Management System
              </p>
            </div>
            
            <div className="space-y-6">
              <div className="flex items-start space-x-3">
                <div className="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center mt-0.5">
                  <span className="text-sm">🔬</span>
                </div>
                <div>
                  <h3 className="font-semibold">Booking Peralatan Modern</h3>
                  <p className="text-blue-100 text-sm">
                    GC-MS, LC-MS/MS, AAS, FTIR, Real-time PCR, dan lainnya
                  </p>
                </div>
              </div>
              
              <div className="flex items-start space-x-3">
                <div className="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center mt-0.5">
                  <span className="text-sm">📊</span>
                </div>
                <div>
                  <h3 className="font-semibold">Tracking Real-time</h3>
                  <p className="text-blue-100 text-sm">
                    Monitor status sampel dan hasil analisis secara langsung
                  </p>
                </div>
              </div>
              
              <div className="flex items-start space-x-3">
                <div className="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center mt-0.5">
                  <span className="text-sm">🎓</span>
                </div>
                <div>
                  <h3 className="font-semibold">Mendukung Riset UNMUL</h3>
                  <p className="text-blue-100 text-sm">
                    Untuk mahasiswa, dosen, dan mitra industri
                  </p>
                </div>
              </div>
            </div>

            <div className="mt-12 pt-8 border-t border-white/20">
              <p className="text-sm text-blue-100">
                Universitas Mulawarman<br />
                Kampus Gunung Kelua, Samarinda<br />
                Kalimantan Timur, Indonesia
              </p>
            </div>
          </div>
        </div>

        {/* Right side - Auth Form */}
        <div className="flex-1 flex items-center justify-center px-6 py-12 lg:px-8">
          <div className="w-full max-w-md">
            <div className="bg-white rounded-2xl shadow-xl p-8">
              <div className="text-center mb-8 lg:hidden">
                <h1 className="text-2xl font-bold text-unmul-primary mb-2">
                  ILab UNMUL
                </h1>
                <p className="text-gray-600">
                  Integrated Laboratory Management System
                </p>
              </div>
              
              <Outlet />
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}