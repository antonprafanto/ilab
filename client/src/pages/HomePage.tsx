import React from 'react';

const HomePage: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <h1 className="text-4xl font-bold text-unmul-primary mb-4">
            ILab UNMUL
          </h1>
          <p className="text-xl text-gray-600 mb-8">
            Sistem Manajemen Laboratorium Terpadu
          </p>
          <p className="text-lg text-gray-700 max-w-3xl mx-auto">
            Universitas Mulawarman
          </p>
        </div>
        
        <div className="mt-16 grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h3 className="text-xl font-semibold mb-3">Booking Fasilitas</h3>
            <p className="text-gray-600">
              Reservasi peralatan laboratorium seperti GC-MS, LC-MS/MS, AAS, FTIR, dan Real-time PCR
            </p>
          </div>
          
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h3 className="text-xl font-semibold mb-3">Manajemen Sampel</h3>
            <p className="text-gray-600">
              Tracking dan monitoring sampel penelitian dengan sistem terintegrasi
            </p>
          </div>
          
          <div className="bg-white p-6 rounded-lg shadow-md">
            <h3 className="text-xl font-semibold mb-3">Layanan Riset</h3>
            <p className="text-gray-600">
              Mendukung penelitian mahasiswa, dosen, dan industri eksternal
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default HomePage;