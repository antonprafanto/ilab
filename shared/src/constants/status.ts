import { BookingStatus, SampleStatus, PaymentStatus } from '../types/index';

export const BOOKING_STATUS_DISPLAY = {
  [BookingStatus.PENDING]: 'Menunggu Konfirmasi',
  [BookingStatus.CONFIRMED]: 'Terkonfirmasi',
  [BookingStatus.IN_PROGRESS]: 'Sedang Berlangsung',
  [BookingStatus.COMPLETED]: 'Selesai',
  [BookingStatus.CANCELLED]: 'Dibatalkan',
  [BookingStatus.NO_SHOW]: 'Tidak Hadir'
};

export const BOOKING_STATUS_COLORS = {
  [BookingStatus.PENDING]: 'bg-yellow-100 text-yellow-800',
  [BookingStatus.CONFIRMED]: 'bg-blue-100 text-blue-800',
  [BookingStatus.IN_PROGRESS]: 'bg-green-100 text-green-800',
  [BookingStatus.COMPLETED]: 'bg-gray-100 text-gray-800',
  [BookingStatus.CANCELLED]: 'bg-red-100 text-red-800',
  [BookingStatus.NO_SHOW]: 'bg-orange-100 text-orange-800'
};

export const SAMPLE_STATUS_DISPLAY = {
  [SampleStatus.SUBMITTED]: 'Dikirim',
  [SampleStatus.RECEIVED]: 'Diterima',
  [SampleStatus.IN_ANALYSIS]: 'Sedang Dianalisis',
  [SampleStatus.ANALYSIS_COMPLETE]: 'Analisis Selesai',
  [SampleStatus.RESULTS_READY]: 'Hasil Siap',
  [SampleStatus.DELIVERED]: 'Diserahkan',
  [SampleStatus.REJECTED]: 'Ditolak'
};

export const SAMPLE_STATUS_COLORS = {
  [SampleStatus.SUBMITTED]: 'bg-blue-100 text-blue-800',
  [SampleStatus.RECEIVED]: 'bg-indigo-100 text-indigo-800',
  [SampleStatus.IN_ANALYSIS]: 'bg-yellow-100 text-yellow-800',
  [SampleStatus.ANALYSIS_COMPLETE]: 'bg-purple-100 text-purple-800',
  [SampleStatus.RESULTS_READY]: 'bg-green-100 text-green-800',
  [SampleStatus.DELIVERED]: 'bg-gray-100 text-gray-800',
  [SampleStatus.REJECTED]: 'bg-red-100 text-red-800'
};

export const PAYMENT_STATUS_DISPLAY = {
  [PaymentStatus.PENDING]: 'Menunggu Pembayaran',
  [PaymentStatus.PAID]: 'Lunas',
  [PaymentStatus.PARTIAL]: 'Pembayaran Sebagian',
  [PaymentStatus.OVERDUE]: 'Terlambat',
  [PaymentStatus.CANCELLED]: 'Dibatalkan',
  [PaymentStatus.REFUNDED]: 'Dikembalikan'
};

export const PAYMENT_STATUS_COLORS = {
  [PaymentStatus.PENDING]: 'bg-yellow-100 text-yellow-800',
  [PaymentStatus.PAID]: 'bg-green-100 text-green-800',
  [PaymentStatus.PARTIAL]: 'bg-blue-100 text-blue-800',
  [PaymentStatus.OVERDUE]: 'bg-red-100 text-red-800',
  [PaymentStatus.CANCELLED]: 'bg-gray-100 text-gray-800',
  [PaymentStatus.REFUNDED]: 'bg-purple-100 text-purple-800'
};