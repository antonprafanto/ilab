import App from './app';
import { logger } from './utils/logger';
import { database } from './config/database';

async function startServer() {
  try {
    // Test database connection
    logger.info('🔌 Connecting to database...');
    const isConnected = await database.testConnection();
    
    if (!isConnected) {
      throw new Error('Failed to connect to database');
    }
    
    logger.info('✅ Database connected successfully');

    // Start the application
    const app = new App();
    app.start();
    
  } catch (error) {
    logger.error('❌ Failed to start server:', error);
    process.exit(1);
  }
}

// Handle graceful shutdown
async function gracefulShutdown(signal: string) {
  logger.info(`📡 Received ${signal}, shutting down gracefully...`);
  
  try {
    await database.close();
    logger.info('✅ Database connection closed');
    
    process.exit(0);
  } catch (error) {
    logger.error('❌ Error during shutdown:', error);
    process.exit(1);
  }
}

// Register shutdown handlers
process.on('SIGINT', () => gracefulShutdown('SIGINT'));
process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));

// Start the server
startServer();