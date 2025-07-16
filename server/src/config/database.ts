import mysql from 'mysql2/promise';
import { config } from './index';
import { logger } from '../utils/logger';

class Database {
  private pool: mysql.Pool;

  constructor() {
    this.pool = mysql.createPool({
      host: config.database.host,
      port: config.database.port,
      user: config.database.user,
      password: config.database.password,
      database: config.database.name,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
      idleTimeout: 60000,
      enableKeepAlive: true,
      keepAliveInitialDelay: 0,
      timezone: '+00:00',
      charset: 'utf8mb4',
      supportBigNumbers: true,
      bigNumberStrings: true,
      dateStrings: false,
      debug: config.app.env === 'development',
      multipleStatements: false
    });

    this.pool.on('connection', (connection) => {
      logger.info(`New MySQL connection established as id ${connection.threadId}`);
    });

    this.pool.on('error', (err: any) => {
      logger.error('MySQL pool error:', err);
      if (err.code === 'PROTOCOL_CONNECTION_LOST') {
        this.handleDisconnect();
      } else {
        throw err;
      }
    });
  }

  private handleDisconnect() {
    logger.warn('MySQL connection lost, attempting to reconnect...');
    // Pool will automatically create new connections when needed
  }

  async getConnection(): Promise<mysql.PoolConnection> {
    try {
      const connection = await this.pool.getConnection();
      return connection;
    } catch (error) {
      logger.error('Failed to get database connection:', error);
      throw error;
    }
  }

  async execute<T = any>(
    query: string, 
    params?: any[]
  ): Promise<[T[], mysql.FieldPacket[]]> {
    const connection = await this.getConnection();
    try {
      const result = await connection.execute(query, params);
      return result as [T[], mysql.FieldPacket[]];
    } finally {
      connection.release();
    }
  }

  async query<T = any>(
    sql: string, 
    params?: any[]
  ): Promise<[T[], mysql.FieldPacket[]]> {
    const connection = await this.getConnection();
    try {
      const result = await connection.query(sql, params);
      return result as [T[], mysql.FieldPacket[]];
    } finally {
      connection.release();
    }
  }

  async transaction<T>(
    callback: (connection: mysql.PoolConnection) => Promise<T>
  ): Promise<T> {
    const connection = await this.getConnection();
    
    try {
      await connection.beginTransaction();
      const result = await callback(connection);
      await connection.commit();
      return result;
    } catch (error) {
      await connection.rollback();
      throw error;
    } finally {
      connection.release();
    }
  }

  async testConnection(): Promise<boolean> {
    try {
      const [rows] = await this.execute('SELECT 1 as test');
      return Array.isArray(rows) && rows.length > 0;
    } catch (error) {
      logger.error('Database connection test failed:', error);
      return false;
    }
  }

  async close(): Promise<void> {
    try {
      await this.pool.end();
      logger.info('Database connection pool closed');
    } catch (error) {
      logger.error('Error closing database connection pool:', error);
      throw error;
    }
  }

  getPool(): mysql.Pool {
    return this.pool;
  }
}

export const database = new Database();
export default database;