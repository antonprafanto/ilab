import { database } from '../config/database';
import { PERMISSIONS } from '../controllers/roles';

const defaultRoles = [
  {
    name: 'admin',
    displayName: 'System Administrator',
    description: 'Full system access with all permissions',
    permissions: Object.values(PERMISSIONS),
    level: 10
  },
  {
    name: 'director',
    displayName: 'Laboratory Director',
    description: 'Laboratory director with management permissions',
    permissions: [
      PERMISSIONS.USERS_VIEW,
      PERMISSIONS.USERS_MANAGE_STATUS,
      PERMISSIONS.ROLES_VIEW,
      PERMISSIONS.EQUIPMENT_VIEW,
      PERMISSIONS.EQUIPMENT_MANAGE,
      PERMISSIONS.BOOKINGS_VIEW,
      PERMISSIONS.BOOKINGS_APPROVE,
      PERMISSIONS.BOOKINGS_MANAGE_ALL,
      PERMISSIONS.SAMPLES_VIEW,
      PERMISSIONS.SAMPLES_MANAGE,
      PERMISSIONS.PAYMENTS_VIEW,
      PERMISSIONS.PAYMENTS_MANAGE,
      PERMISSIONS.REPORTS_VIEW,
      PERMISSIONS.REPORTS_EXPORT,
      PERMISSIONS.ANALYTICS_VIEW
    ],
    level: 9
  },
  {
    name: 'vice_director',
    displayName: 'Vice Laboratory Director',
    description: 'Assistant laboratory director with operational permissions',
    permissions: [
      PERMISSIONS.USERS_VIEW,
      PERMISSIONS.EQUIPMENT_VIEW,
      PERMISSIONS.EQUIPMENT_UPDATE,
      PERMISSIONS.BOOKINGS_VIEW,
      PERMISSIONS.BOOKINGS_APPROVE,
      PERMISSIONS.BOOKINGS_MANAGE_ALL,
      PERMISSIONS.SAMPLES_VIEW,
      PERMISSIONS.SAMPLES_MANAGE,
      PERMISSIONS.PAYMENTS_VIEW,
      PERMISSIONS.REPORTS_VIEW,
      PERMISSIONS.ANALYTICS_VIEW
    ],
    level: 8
  },
  {
    name: 'lab_head',
    displayName: 'Laboratory Head',
    description: 'Laboratory section head with supervisory permissions',
    permissions: [
      PERMISSIONS.USERS_VIEW,
      PERMISSIONS.EQUIPMENT_VIEW,
      PERMISSIONS.EQUIPMENT_UPDATE,
      PERMISSIONS.BOOKINGS_VIEW,
      PERMISSIONS.BOOKINGS_APPROVE,
      PERMISSIONS.SAMPLES_VIEW,
      PERMISSIONS.SAMPLES_MANAGE,
      PERMISSIONS.SAMPLES_RESULTS,
      PERMISSIONS.PAYMENTS_VIEW,
      PERMISSIONS.REPORTS_VIEW
    ],
    level: 7
  },
  {
    name: 'laboran',
    displayName: 'Laboratory Technician',
    description: 'Laboratory technician with operational permissions',
    permissions: [
      PERMISSIONS.EQUIPMENT_VIEW,
      PERMISSIONS.BOOKINGS_VIEW,
      PERMISSIONS.BOOKINGS_UPDATE,
      PERMISSIONS.SAMPLES_VIEW,
      PERMISSIONS.SAMPLES_UPDATE,
      PERMISSIONS.SAMPLES_RESULTS,
      PERMISSIONS.PAYMENTS_VIEW
    ],
    level: 6
  },
  {
    name: 'lecturer',
    displayName: 'Lecturer/Faculty',
    description: 'University lecturer with research permissions',
    permissions: [
      PERMISSIONS.EQUIPMENT_VIEW,
      PERMISSIONS.BOOKINGS_VIEW,
      PERMISSIONS.BOOKINGS_CREATE,
      PERMISSIONS.BOOKINGS_UPDATE,
      PERMISSIONS.SAMPLES_VIEW,
      PERMISSIONS.SAMPLES_CREATE,
      PERMISSIONS.SAMPLES_UPDATE,
      PERMISSIONS.PAYMENTS_VIEW
    ],
    level: 5
  },
  {
    name: 'student',
    displayName: 'Student',
    description: 'University student with basic permissions',
    permissions: [
      PERMISSIONS.EQUIPMENT_VIEW,
      PERMISSIONS.BOOKINGS_VIEW,
      PERMISSIONS.BOOKINGS_CREATE,
      PERMISSIONS.SAMPLES_VIEW,
      PERMISSIONS.SAMPLES_CREATE,
      PERMISSIONS.PAYMENTS_VIEW
    ],
    level: 3
  },
  {
    name: 'external',
    displayName: 'External User',
    description: 'External users from industry or other institutions',
    permissions: [
      PERMISSIONS.EQUIPMENT_VIEW,
      PERMISSIONS.BOOKINGS_VIEW,
      PERMISSIONS.BOOKINGS_CREATE,
      PERMISSIONS.SAMPLES_VIEW,
      PERMISSIONS.SAMPLES_CREATE,
      PERMISSIONS.PAYMENTS_VIEW
    ],
    level: 2
  }
];

export async function seedRoles() {
  try {
    console.log('🌱 Starting roles seeding...');

    // Check if roles already exist
    const [existingRoles] = await database.execute('SELECT COUNT(*) as count FROM roles');
    const count = (existingRoles as any)[0].count;

    if (count > 0) {
      console.log('⚠️  Roles already exist, skipping seed');
      return;
    }

    // Insert default roles
    for (const role of defaultRoles) {
      const roleId = crypto.randomUUID();
      
      await database.execute(`
        INSERT INTO roles (id, name, display_name, description, permissions, level, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?)
      `, [
        roleId,
        role.name,
        role.displayName,
        role.description,
        JSON.stringify(role.permissions),
        role.level,
        true
      ]);

      console.log(`✅ Created role: ${role.displayName}`);
    }

    console.log('🎉 Roles seeding completed successfully!');

  } catch (error) {
    console.error('❌ Error seeding roles:', error);
    throw error;
  }
}

// Run seeding if called directly
if (require.main === module) {
  seedRoles()
    .then(() => {
      console.log('Seeding completed');
      process.exit(0);
    })
    .catch((error) => {
      console.error('Seeding failed:', error);
      process.exit(1);
    });
}