import { EquipmentType } from '../types/equipment';

export const EQUIPMENT_TYPE_DISPLAY_NAMES = {
  [EquipmentType.GC_MS]: 'Gas Chromatography-Mass Spectrometry (GC-MS)',
  [EquipmentType.LC_MS]: 'Liquid Chromatography-Mass Spectrometry (LC-MS/MS)',
  [EquipmentType.AAS]: 'Atomic Absorption Spectroscopy (AAS)',
  [EquipmentType.FTIR]: 'Fourier Transform Infrared Spectroscopy (FTIR)',
  [EquipmentType.PCR]: 'Real-time Polymerase Chain Reaction (PCR)',
  [EquipmentType.FREEZE_DRYER]: 'Freeze Dryer',
  [EquipmentType.HPLC]: 'High Performance Liquid Chromatography (HPLC)',
  [EquipmentType.SPECTROPHOTOMETER]: 'Spectrophotometer',
  [EquipmentType.MICROSCOPE]: 'Microscope',
  [EquipmentType.CENTRIFUGE]: 'Centrifuge',
  [EquipmentType.INCUBATOR]: 'Incubator',
  [EquipmentType.OTHER]: 'Other Equipment'
};

export const EQUIPMENT_CATEGORIES = {
  'Analytical Chemistry': [
    EquipmentType.GC_MS,
    EquipmentType.LC_MS,
    EquipmentType.AAS,
    EquipmentType.FTIR,
    EquipmentType.HPLC,
    EquipmentType.SPECTROPHOTOMETER
  ],
  'Molecular Biology': [
    EquipmentType.PCR,
    EquipmentType.CENTRIFUGE,
    EquipmentType.INCUBATOR
  ],
  'Sample Preparation': [
    EquipmentType.FREEZE_DRYER,
    EquipmentType.CENTRIFUGE
  ],
  'Microscopy': [
    EquipmentType.MICROSCOPE
  ],
  'General': [
    EquipmentType.OTHER
  ]
};

export const DEFAULT_BOOKING_RULES = {
  maxHoursPerSession: 8,
  maxSessionsPerDay: 2,
  advanceBookingDays: 30,
  minimumNoticeHours: 24,
  allowedRoles: ['lecturer', 'student', 'external', 'laboran', 'lab_head']
};

export const DEFAULT_PRICING = {
  internal: {
    pricePerHour: 50000, // IDR
    setupFee: 25000
  },
  external: {
    pricePerHour: 100000, // IDR
    setupFee: 50000
  }
};

export const MAINTENANCE_INTERVALS = {
  [EquipmentType.GC_MS]: 90, // days
  [EquipmentType.LC_MS]: 90,
  [EquipmentType.AAS]: 60,
  [EquipmentType.FTIR]: 120,
  [EquipmentType.PCR]: 30,
  [EquipmentType.FREEZE_DRYER]: 180,
  [EquipmentType.HPLC]: 60,
  [EquipmentType.SPECTROPHOTOMETER]: 90,
  [EquipmentType.MICROSCOPE]: 180,
  [EquipmentType.CENTRIFUGE]: 90,
  [EquipmentType.INCUBATOR]: 120,
  [EquipmentType.OTHER]: 90
};

export const CALIBRATION_INTERVALS = {
  [EquipmentType.GC_MS]: 365, // days
  [EquipmentType.LC_MS]: 365,
  [EquipmentType.AAS]: 180,
  [EquipmentType.FTIR]: 365,
  [EquipmentType.PCR]: 180,
  [EquipmentType.FREEZE_DRYER]: 365,
  [EquipmentType.HPLC]: 180,
  [EquipmentType.SPECTROPHOTOMETER]: 180,
  [EquipmentType.MICROSCOPE]: 365,
  [EquipmentType.CENTRIFUGE]: 365,
  [EquipmentType.INCUBATOR]: 180,
  [EquipmentType.OTHER]: 365
};