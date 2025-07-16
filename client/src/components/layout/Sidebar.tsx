import { NavLink } from 'react-router-dom'
import { useAuthStore } from '@/stores/auth'
import { 
  HomeIcon, 
  UserIcon, 
  BeakerIcon, 
  CalendarIcon,
  DocumentIcon,
  CreditCardIcon,
  ChartBarIcon,
  CogIcon,
  UsersIcon,
  ShieldCheckIcon
} from '@heroicons/react/24/outline'

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
  { name: 'Profile', href: '/dashboard/profile', icon: UserIcon },
  { name: 'Users', href: '/dashboard/users', icon: UsersIcon, adminOnly: true },
  { name: 'Roles', href: '/dashboard/roles', icon: ShieldCheckIcon, adminOnly: true },
  { name: 'Peralatan', href: '/dashboard/equipment', icon: BeakerIcon },
  { name: 'Booking', href: '/dashboard/bookings', icon: CalendarIcon },
  { name: 'Sampel', href: '/dashboard/samples', icon: DocumentIcon },
  { name: 'Pembayaran', href: '/dashboard/payments', icon: CreditCardIcon },
  { name: 'Laporan', href: '/dashboard/reports', icon: ChartBarIcon },
  { name: 'Pengaturan', href: '/dashboard/settings', icon: CogIcon },
]

export const Sidebar = () => {
  const { user } = useAuthStore()

  // Filter navigation based on user role
  const filteredNavigation = navigation.filter(item => {
    // Admin-only items
    if ((item as any).adminOnly && !['admin', 'director'].includes(user?.role || '')) {
      return false
    }
    // Basic filtering - will be enhanced based on role permissions
    if (item.name === 'Pengaturan' && !['admin', 'director'].includes(user?.role || '')) {
      return false
    }
    if (item.name === 'Laporan' && !['admin', 'director', 'vice_director', 'lab_head'].includes(user?.role || '')) {
      return false
    }
    return true
  })

  return (
    <div className="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg lg:block hidden">
      <div className="flex h-16 items-center justify-center border-b border-gray-200">
        <div className="text-center">
          <h1 className="text-xl font-bold text-unmul-primary">ILab UNMUL</h1>
          <p className="text-xs text-gray-600">Laboratory Management</p>
        </div>
      </div>
      
      <nav className="mt-6 px-3">
        <div className="space-y-1">
          {filteredNavigation.map((item) => (
            <NavLink
              key={item.name}
              to={item.href}
              className={({ isActive }) =>
                `group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                  isActive
                    ? 'bg-unmul-primary text-white'
                    : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                }`
              }
            >
              <item.icon
                className="mr-3 h-5 w-5 flex-shrink-0"
                aria-hidden="true"
              />
              {item.name}
            </NavLink>
          ))}
        </div>
      </nav>

      {/* User info at bottom */}
      <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
        <div className="flex items-center">
          <div className="flex-shrink-0">
            <div className="h-8 w-8 rounded-full bg-unmul-primary flex items-center justify-center">
              <span className="text-sm font-medium text-white">
                {user?.firstName?.charAt(0)?.toUpperCase()}
              </span>
            </div>
          </div>
          <div className="ml-3">
            <p className="text-sm font-medium text-gray-900">
              {user?.firstName} {user?.lastName}
            </p>
            <p className="text-xs text-gray-500 capitalize">
              {user?.role?.replace('_', ' ')}
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}