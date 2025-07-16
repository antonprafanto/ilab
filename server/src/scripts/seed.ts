import { database } from '../config/database';
import { logger } from '../utils/logger';
import fs from 'fs/promises';
import path from 'path';

async function runSeedData() {
  try {
    logger.info('🌱 Starting database seeding...');

    // Read seed SQL file
    const seedSqlPath = path.join(process.cwd(), '..', 'docs', 'database', 'seed.sql');
    const seedSql = await fs.readFile(seedSqlPath, 'utf8');

    // Split SQL statements (basic splitting by semicolon)
    const statements = seedSql
      .split(';')
      .map(stmt => stmt.trim())
      .filter(stmt => stmt.length > 0 && !stmt.startsWith('--'));

    // Execute each statement
    for (const statement of statements) {
      if (statement.trim()) {
        try {
          await database.execute(statement);
        } catch (error) {
          logger.warn(`Skipping statement (possibly already exists): ${error}`);
        }
      }
    }

    logger.info('✅ Database seeding completed successfully');
  } catch (error) {
    logger.error('❌ Database seeding failed:', error);
    throw error;
  }
}

async function main() {
  try {
    // Test database connection
    const isConnected = await database.testConnection();
    if (!isConnected) {
      throw new Error('Database connection failed');
    }

    await runSeedData();
    
    logger.info('🎉 All seeding operations completed');
    process.exit(0);
  } catch (error) {
    logger.error('💥 Seeding failed:', error);
    process.exit(1);
  }
}

// Run if called directly
if (require.main === module) {
  main();
}

export { runSeedData };