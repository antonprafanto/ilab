import { database } from '../config/database';
import { hashPassword } from '../services/auth';

const testEquipmentCategories = [
  {
    name: 'Analytical Instruments',
    description: 'High-precision analytical instruments for chemical analysis',
    icon: 'microscope'
  },
  {
    name: 'Sample Preparation',
    description: 'Equipment for sample preparation and pre-treatment',
    icon: 'flask'
  },
  {
    name: 'Molecular Biology',
    description: 'Equipment for molecular biology and genetics research',
    icon: 'dna'
  }
];

const testEquipment = [
  {
    name: 'GC-MS Agilent 7890A',
    type: 'gc_ms',
    description: 'Gas Chromatography-Mass Spectrometry for organic compound analysis',
    location: 'Lab Room 101',
    responsible_person: 'Dr. Ahmad Wijaya',
    contact_info: 'ahmad.wijaya@unmul.ac.id',
    pricing: {
      hourlyRate: 250000,
      setupFee: 100000,
      samplePrep: 50000
    },
    booking_rules: {
      maxDurationHours: 8,
      minAdvanceHours: 48,
      requiresApproval: true
    },
    specifications: {
      manufacturer: 'Agilent Technologies',
      model: '7890A',
      year: 2020,
      sensitivity: '1 pg',
      maxTemp: '450°C'
    }
  },
  {
    name: 'LC-MS/MS Triple Quad',
    type: 'lc_ms',
    description: 'Liquid Chromatography-Tandem Mass Spectrometry',
    location: 'Lab Room 102',
    responsible_person: 'Dr. Siti Maryam',
    contact_info: 'siti.maryam@unmul.ac.id',
    pricing: {
      hourlyRate: 300000,
      setupFee: 150000
    },
    booking_rules: {
      maxDurationHours: 6,
      minAdvanceHours: 24,
      requiresApproval: true
    },
    specifications: {
      manufacturer: 'Shimadzu',
      model: 'LCMS-8060',
      year: 2021,
      sensitivity: '0.1 pg/ml',
      flowRate: '0.001-2.0 ml/min'
    }
  },
  {
    name: 'AAS Atomic Absorption',
    type: 'aas',
    description: 'Atomic Absorption Spectrophotometer for metal analysis',
    location: 'Lab Room 103',
    responsible_person: 'Dr. Budi Santoso',
    contact_info: 'budi.santoso@unmul.ac.id',
    pricing: {
      hourlyRate: 150000,
      setupFee: 75000
    },
    booking_rules: {
      maxDurationHours: 4,
      minAdvanceHours: 12,
      requiresApproval: false
    },
    specifications: {
      manufacturer: 'PerkinElmer',
      model: 'AAnalyst 400',
      year: 2019,
      elements: 'All metals',
      detection_limit: '0.1 ppm'
    }
  },
  {
    name: 'FTIR Spectrometer',
    type: 'ftir',
    description: 'Fourier-Transform Infrared Spectroscopy',
    location: 'Lab Room 104',
    responsible_person: 'Dr. Rina Kurniawan',
    contact_info: 'rina.kurniawan@unmul.ac.id',
    pricing: {
      hourlyRate: 100000,
      setupFee: 50000
    },
    booking_rules: {
      maxDurationHours: 6,
      minAdvanceHours: 6,
      requiresApproval: false
    },
    specifications: {
      manufacturer: 'Thermo Scientific',
      model: 'Nicolet iS50',
      year: 2018,
      resolution: '0.125 cm-1',
      range: '7800-350 cm-1'
    }
  },
  {
    name: 'Real-time PCR System',
    type: 'pcr',
    description: 'Quantitative PCR for gene expression analysis',
    location: 'Lab Room 105',
    responsible_person: 'Dr. Indra Mahendra',
    contact_info: 'indra.mahendra@unmul.ac.id',
    pricing: {
      hourlyRate: 200000,
      setupFee: 100000
    },
    booking_rules: {
      maxDurationHours: 8,
      minAdvanceHours: 24,
      requiresApproval: true
    },
    specifications: {
      manufacturer: 'Applied Biosystems',
      model: 'QuantStudio 3',
      year: 2020,
      throughput: '96 wells',
      chemistry: 'TaqMan, SYBR Green'
    }
  }
];

const testUsers = [
  {
    email: 'admin@unmul.ac.id',
    password: 'Admin123!',
    firstName: 'System',
    lastName: 'Administrator',
    phoneNumber: '+62-541-749444',
    role: 'admin',
    faculty: 'IT Center',
    department: 'System Administration',
    status: 'active',
    isEmailVerified: true,
    isDocumentVerified: true
  },
  {
    email: 'director@unmul.ac.id',
    password: 'Director123!',
    firstName: 'Prof. Dr. Muhammad',
    lastName: 'Rahman',
    phoneNumber: '+62-541-749445',
    role: 'director',
    faculty: 'FMIPA',
    department: 'Chemistry',
    status: 'active',
    isEmailVerified: true,
    isDocumentVerified: true
  },
  {
    email: 'labhead@unmul.ac.id',
    password: 'LabHead123!',
    firstName: 'Dr. Ahmad',
    lastName: 'Wijaya',
    phoneNumber: '+62-541-749446',
    role: 'lab_head',
    faculty: 'FMIPA',
    department: 'Chemistry',
    status: 'active',
    isEmailVerified: true,
    isDocumentVerified: true
  },
  {
    email: 'laboran@unmul.ac.id',
    password: 'Laboran123!',
    firstName: 'Siti',
    lastName: 'Aminah',
    phoneNumber: '+62-541-749447',
    role: 'laboran',
    faculty: 'FMIPA',
    department: 'Chemistry',
    status: 'active',
    isEmailVerified: true,
    isDocumentVerified: true
  },
  {
    email: 'lecturer@unmul.ac.id',
    password: 'Lecturer123!',
    firstName: 'Dr. Budi',
    lastName: 'Santoso',
    phoneNumber: '+62-541-749448',
    role: 'lecturer',
    faculty: 'FMIPA',
    department: 'Chemistry',
    status: 'active',
    isEmailVerified: true,
    isDocumentVerified: true
  },
  {
    email: 'student1@unmul.ac.id',
    password: 'Student123!',
    firstName: 'Andi',
    lastName: 'Pratama',
    phoneNumber: '+62-812-3456-7890',
    role: 'student',
    faculty: 'FMIPA',
    department: 'Chemistry',
    nim: '1908105010001',
    studentId: '1908105010001',
    status: 'active',
    isEmailVerified: true,
    isDocumentVerified: true
  },
  {
    email: 'student2@unmul.ac.id',
    password: 'Student123!',
    firstName: 'Rina',
    lastName: 'Sari',
    phoneNumber: '+62-812-3456-7891',
    role: 'student',
    faculty: 'FMIPA',
    department: 'Biology',
    nim: '1908105010002',
    studentId: '1908105010002',
    status: 'active',
    isEmailVerified: true,
    isDocumentVerified: true
  },
  {
    email: 'external@company.com',
    password: 'External123!',
    firstName: 'John',
    lastName: 'Smith',
    phoneNumber: '+62-812-3456-7892',
    role: 'external',
    institution: 'PT. Petrokimia Gresik',
    status: 'active',
    isEmailVerified: true,
    isDocumentVerified: true
  }
];

export async function seedTestData() {
  try {
    console.log('🌱 Starting test data seeding...');

    // Clear existing test data
    await database.execute('DELETE FROM payment_records WHERE id LIKE "test-%"');
    await database.execute('DELETE FROM payment_items WHERE id LIKE "test-%"');
    await database.execute('DELETE FROM payments WHERE invoice_number LIKE "TEST-%"');
    await database.execute('DELETE FROM test_results WHERE id LIKE "test-%"');
    await database.execute('DELETE FROM sample_custody WHERE id LIKE "test-%"');
    await database.execute('DELETE FROM samples WHERE sample_code LIKE "TEST%"');
    await database.execute('DELETE FROM booking_history WHERE id LIKE "test-%"');
    await database.execute('DELETE FROM bookings WHERE id LIKE "test-%"');
    await database.execute('DELETE FROM equipment WHERE name LIKE "%Test%" OR id LIKE "test-%"');
    await database.execute('DELETE FROM equipment_categories WHERE name LIKE "%Test%" OR id LIKE "test-%"');
    await database.execute('DELETE FROM users WHERE email LIKE "%@unmul.ac.id" OR email LIKE "%@company.com"');

    console.log('🧹 Cleared existing test data');

    // 1. Seed Equipment Categories
    console.log('📁 Seeding equipment categories...');
    const categoryIds: { [key: string]: string } = {};
    
    for (const category of testEquipmentCategories) {
      const categoryId = `test-cat-${crypto.randomUUID()}`;
      categoryIds[category.name] = categoryId;
      
      await database.execute(`
        INSERT INTO equipment_categories (id, name, description, icon)
        VALUES (?, ?, ?, ?)
      `, [categoryId, category.name, category.description, category.icon]);
      
      console.log(`✅ Created category: ${category.name}`);
    }

    // 2. Seed Equipment
    console.log('🔬 Seeding equipment...');
    const equipmentIds: string[] = [];
    
    for (let i = 0; i < testEquipment.length; i++) {
      const equipment = testEquipment[i];
      const equipmentId = `test-eq-${crypto.randomUUID()}`;
      equipmentIds.push(equipmentId);
      
      // Assign category based on type
      let categoryId = null;
      if (['gc_ms', 'lc_ms', 'aas', 'ftir'].includes(equipment.type)) {
        categoryId = categoryIds['Analytical Instruments'];
      } else if (equipment.type === 'pcr') {
        categoryId = categoryIds['Molecular Biology'];
      }
      
      await database.execute(`
        INSERT INTO equipment (
          id, name, type, category_id, description, status, location,
          responsible_person, contact_info, booking_rules, pricing, specifications
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        equipmentId,
        equipment.name,
        equipment.type,
        categoryId,
        equipment.description,
        'available',
        equipment.location,
        equipment.responsible_person,
        equipment.contact_info,
        JSON.stringify(equipment.booking_rules),
        JSON.stringify(equipment.pricing),
        JSON.stringify(equipment.specifications)
      ]);
      
      console.log(`✅ Created equipment: ${equipment.name}`);
    }

    // 3. Seed Users
    console.log('👥 Seeding users...');
    const userIds: { [key: string]: string } = {};
    
    for (const user of testUsers) {
      const userId = `test-user-${crypto.randomUUID()}`;
      const roleKey = user.role;
      userIds[user.email] = userId;
      
      // Get role ID
      const [roles] = await database.execute(
        'SELECT id FROM roles WHERE name = ?',
        [user.role]
      ) as any;
      
      if (roles.length === 0) {
        console.log(`❌ Role not found: ${user.role}`);
        continue;
      }
      
      const roleId = roles[0].id;
      const passwordHash = await hashPassword(user.password);
      
      await database.execute(`
        INSERT INTO users (
          id, email, password_hash, first_name, last_name, phone_number,
          role_id, status, faculty, department, student_id, nim, institution,
          is_email_verified, is_document_verified
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        userId,
        user.email,
        passwordHash,
        user.firstName,
        user.lastName,
        user.phoneNumber,
        roleId,
        user.status,
        user.faculty,
        user.department,
        user.studentId,
        user.nim,
        user.institution,
        user.isEmailVerified,
        user.isDocumentVerified
      ]);
      
      console.log(`✅ Created user: ${user.email} (${user.role})`);
    }

    // 4. Seed Sample Bookings
    console.log('📅 Seeding sample bookings...');
    const bookingIds: string[] = [];
    
    const sampleBookings = [
      {
        userId: userIds['student1@unmul.ac.id'],
        equipmentId: equipmentIds[0], // GC-MS
        title: 'Analisis Senyawa Organik dalam Air',
        description: 'Penelitian untuk tugas akhir tentang kontaminan organik',
        startTime: new Date(Date.now() + 24 * 60 * 60 * 1000), // Tomorrow
        duration: 4,
        purpose: 'Penelitian tugas akhir mahasiswa',
        sampleType: 'water',
        numberOfSamples: 5
      },
      {
        userId: userIds['lecturer@unmul.ac.id'],
        equipmentId: equipmentIds[1], // LC-MS
        title: 'Analisis Metabolit Tanaman',
        description: 'Riset identifikasi metabolit sekunder',
        startTime: new Date(Date.now() + 48 * 60 * 60 * 1000), // Day after tomorrow
        duration: 6,
        purpose: 'Penelitian dosen untuk publikasi jurnal',
        sampleType: 'biological',
        numberOfSamples: 10
      },
      {
        userId: userIds['external@company.com'],
        equipmentId: equipmentIds[2], // AAS
        title: 'Quality Control Produk',
        description: 'Analisis kandungan logam berat dalam produk',
        startTime: new Date(Date.now() + 72 * 60 * 60 * 1000), // 3 days from now
        duration: 3,
        purpose: 'Quality control produk industri',
        sampleType: 'industrial',
        numberOfSamples: 8
      }
    ];

    for (let i = 0; i < sampleBookings.length; i++) {
      const booking = sampleBookings[i];
      const bookingId = `test-booking-${crypto.randomUUID()}`;
      bookingIds.push(bookingId);
      
      const endTime = new Date(booking.startTime.getTime() + booking.duration * 60 * 60 * 1000);
      
      await database.execute(`
        INSERT INTO bookings (
          id, user_id, equipment_id, title, description, start_time, end_time,
          status, purpose, sample_type, number_of_samples, estimated_cost
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        bookingId,
        booking.userId,
        booking.equipmentId,
        booking.title,
        booking.description,
        booking.startTime,
        endTime,
        'confirmed',
        booking.purpose,
        booking.sampleType,
        booking.numberOfSamples,
        250000 * booking.duration // Sample cost calculation
      ]);
      
      console.log(`✅ Created booking: ${booking.title}`);
    }

    // 5. Seed Sample Data
    console.log('🧪 Seeding samples...');
    
    for (let i = 0; i < bookingIds.length; i++) {
      const bookingId = bookingIds[i];
      const booking = sampleBookings[i];
      
      const sampleId = `test-sample-${crypto.randomUUID()}`;
      const sampleCode = `TEST${new Date().getFullYear()}${(new Date().getMonth() + 1).toString().padStart(2, '0')}${(i + 1).toString().padStart(4, '0')}`;
      
      await database.execute(`
        INSERT INTO samples (
          id, booking_id, sample_code, sample_name, sample_type,
          description, quantity, unit, analysis_requested, priority,
          status, submitted_by, expected_delivery_date
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        sampleId,
        bookingId,
        sampleCode,
        `Sample ${i + 1} - ${booking.title}`,
        booking.sampleType,
        `Test sample for ${booking.title}`,
        `${booking.numberOfSamples}`,
        'pieces',
        JSON.stringify(['chemical_composition', 'purity_analysis']),
        'normal',
        'submitted',
        booking.userId,
        new Date(Date.now() + 7 * 24 * 60 * 60 * 1000) // 1 week from now
      ]);
      
      console.log(`✅ Created sample: ${sampleCode}`);
    }

    console.log('🎉 Test data seeding completed successfully!');
    console.log('\n📋 Test Data Summary:');
    console.log(`- Equipment Categories: ${testEquipmentCategories.length}`);
    console.log(`- Equipment: ${testEquipment.length}`);
    console.log(`- Users: ${testUsers.length}`);
    console.log(`- Bookings: ${sampleBookings.length}`);
    console.log(`- Samples: ${sampleBookings.length}`);
    
    console.log('\n🔑 Test Login Credentials:');
    testUsers.forEach(user => {
      console.log(`- ${user.role}: ${user.email} / ${user.password}`);
    });

  } catch (error) {
    console.error('❌ Error seeding test data:', error);
    throw error;
  }
}

// Run seeding if called directly
if (require.main === module) {
  seedTestData()
    .then(() => {
      console.log('Test data seeding completed');
      process.exit(0);
    })
    .catch((error) => {
      console.error('Test data seeding failed:', error);
      process.exit(1);
    });
}