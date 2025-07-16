export const Footer = () => {
  return (
    <footer className="bg-gray-900 text-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* About */}
          <div className="col-span-1 md:col-span-2">
            <h3 className="text-lg font-semibold mb-4">ILab UNMUL</h3>
            <p className="text-gray-300 mb-4">
              Integrated Laboratory Management System Universitas Mulawarman. 
              Mendukung kegiatan penelitian dan analisis untuk kemajuan ilmu pengetahuan 
              dan pembangunan Ibu Kota Negara.
            </p>
            <div className="text-sm text-gray-400">
              <p>Universitas Mulawarman</p>
              <p>Kampus Gunung Kelua, Samarinda</p>
              <p>Kalimantan Timur 75123, Indonesia</p>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-lg font-semibold mb-4">Tautan Cepat</h3>
            <ul className="space-y-2 text-gray-300">
              <li><a href="/services" className="hover:text-white transition-colors">Layanan</a></li>
              <li><a href="/equipment" className="hover:text-white transition-colors">Peralatan</a></li>
              <li><a href="/pricing" className="hover:text-white transition-colors">Tarif</a></li>
              <li><a href="/booking-guide" className="hover:text-white transition-colors">Panduan Booking</a></li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="text-lg font-semibold mb-4">Kontak</h3>
            <ul className="space-y-2 text-gray-300">
              <li>📞 +62-541-749326</li>
              <li>📧 ilab@unmul.ac.id</li>
              <li>🕒 Senin-Jumat: 08:00-17:00</li>
              <li>🕒 Sabtu: 08:00-12:00</li>
            </ul>
          </div>
        </div>

        <div className="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
          <div className="text-sm text-gray-400">
            © 2024 ILab UNMUL. All rights reserved.
          </div>
          <div className="flex space-x-6 mt-4 md:mt-0">
            <a href="/privacy" className="text-sm text-gray-400 hover:text-white transition-colors">
              Privacy Policy
            </a>
            <a href="/terms" className="text-sm text-gray-400 hover:text-white transition-colors">
              Terms of Service
            </a>
          </div>
        </div>
      </div>
    </footer>
  )
}