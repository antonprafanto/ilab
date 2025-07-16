import { Link } from 'react-router-dom'
import { Button } from '@/components/ui/Button'

export const PublicHeader = () => {
  return (
    <header className="bg-white shadow-sm border-b">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo */}
          <Link to="/" className="flex items-center space-x-2">
            <div className="text-2xl font-bold text-unmul-primary">
              ILab UNMUL
            </div>
          </Link>

          {/* Navigation */}
          <nav className="hidden md:flex items-center space-x-8">
            <Link 
              to="/services" 
              className="text-gray-600 hover:text-unmul-primary transition-colors"
            >
              Layanan
            </Link>
            <Link 
              to="/equipment" 
              className="text-gray-600 hover:text-unmul-primary transition-colors"
            >
              Peralatan
            </Link>
            <Link 
              to="/pricing" 
              className="text-gray-600 hover:text-unmul-primary transition-colors"
            >
              Tarif
            </Link>
            <Link 
              to="/contact" 
              className="text-gray-600 hover:text-unmul-primary transition-colors"
            >
              Kontak
            </Link>
          </nav>

          {/* Auth buttons */}
          <div className="flex items-center space-x-4">
            <Link to="/auth/login">
              <Button variant="ghost">
                Masuk
              </Button>
            </Link>
            <Link to="/auth/register">
              <Button variant="unmul">
                Daftar
              </Button>
            </Link>
          </div>
        </div>
      </div>
    </header>
  )
}