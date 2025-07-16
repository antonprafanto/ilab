import React from 'react';
import { Link } from 'react-router-dom';

const VerifyEmailPage: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center">
      <div className="max-w-md w-full space-y-8">
        <div className="text-center">
          <div className="mx-auto h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
            <svg 
              className="h-6 w-6 text-green-600" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path 
                strokeLinecap="round" 
                strokeLinejoin="round" 
                strokeWidth={2} 
                d="M5 13l4 4L19 7" 
              />
            </svg>
          </div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Verifikasi Email
          </h2>
          <p className="mt-2 text-center text-sm text-gray-600">
            Kami telah mengirim link verifikasi ke email Anda
          </p>
        </div>
        
        <div className="text-center space-y-4">
          <p className="text-sm text-gray-600">
            Silakan cek email Anda dan klik link verifikasi untuk mengaktifkan akun.
          </p>
          
          <button 
            className="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-unmul-primary"
          >
            Kirim Ulang Email Verifikasi
          </button>
          
          <div className="pt-4">
            <Link
              to="/login"
              className="font-medium text-unmul-primary hover:text-blue-500"
            >
              Kembali ke halaman login
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default VerifyEmailPage;