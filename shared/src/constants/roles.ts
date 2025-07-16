import { UserRole } from '../types/user';

export const ROLE_PERMISSIONS = {
  [UserRole.ADMIN]: [
    'users:read',
    'users:write',
    'users:delete',
    'equipment:read',
    'equipment:write',
    'equipment:delete',
    'bookings:read',
    'bookings:write',
    'bookings:delete',
    'bookings:approve',
    'samples:read',
    'samples:write',
    'samples:delete',
    'payments:read',
    'payments:write',
    'payments:delete',
    'reports:read',
    'system:manage'
  ],
  [UserRole.DIRECTOR]: [
    'users:read',
    'users:write',
    'equipment:read',
    'equipment:write',
    'bookings:read',
    'bookings:write',
    'bookings:approve',
    'samples:read',
    'samples:write',
    'payments:read',
    'payments:write',
    'reports:read'
  ],
  [UserRole.VICE_DIRECTOR]: [
    'users:read',
    'equipment:read',
    'equipment:write',
    'bookings:read',
    'bookings:write',
    'bookings:approve',
    'samples:read',
    'samples:write',
    'payments:read',
    'reports:read'
  ],
  [UserRole.LAB_HEAD]: [
    'equipment:read',
    'equipment:write',
    'bookings:read',
    'bookings:write',
    'bookings:approve',
    'samples:read',
    'samples:write',
    'payments:read',
    'reports:read'
  ],
  [UserRole.LABORAN]: [
    'equipment:read',
    'bookings:read',
    'bookings:write',
    'samples:read',
    'samples:write',
    'payments:read'
  ],
  [UserRole.LECTURER]: [
    'equipment:read',
    'bookings:read',
    'bookings:write',
    'samples:read',
    'samples:write',
    'payments:read'
  ],
  [UserRole.STUDENT]: [
    'equipment:read',
    'bookings:read',
    'bookings:write',
    'samples:read',
    'payments:read'
  ],
  [UserRole.EXTERNAL]: [
    'equipment:read',
    'bookings:read',
    'bookings:write',
    'samples:read',
    'payments:read'
  ]
};

export const ROLE_HIERARCHY = {
  [UserRole.ADMIN]: 8,
  [UserRole.DIRECTOR]: 7,
  [UserRole.VICE_DIRECTOR]: 6,
  [UserRole.LAB_HEAD]: 5,
  [UserRole.LABORAN]: 4,
  [UserRole.LECTURER]: 3,
  [UserRole.STUDENT]: 2,
  [UserRole.EXTERNAL]: 1
};

export const ROLE_DISPLAY_NAMES = {
  [UserRole.ADMIN]: 'Administrator',
  [UserRole.DIRECTOR]: 'Direktur',
  [UserRole.VICE_DIRECTOR]: 'Wakil Direktur',
  [UserRole.LAB_HEAD]: 'Kepala Laboratorium',
  [UserRole.LABORAN]: 'Laboran',
  [UserRole.LECTURER]: 'Dosen',
  [UserRole.STUDENT]: 'Mahasiswa',
  [UserRole.EXTERNAL]: 'Eksternal'
};

export const UNMUL_FACULTIES = [
  'Fakultas MIPA',
  'Fakultas Teknik',
  'Fakultas Kedokteran',
  'Fakultas Farmasi',
  'Fakultas Pertanian',
  'Fakultas Perikanan dan Ilmu Kelautan',
  'Fakultas Keguruan dan Ilmu Pendidikan',
  'Fakultas Ilmu Sosial dan Ilmu Politik',
  'Fakultas Ekonomi dan Bisnis',
  'Fakultas Kehutanan',
  'Fakultas Hukum'
];