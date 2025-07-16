import express from 'express';
import cors from 'cors';
import compression from 'compression';
import morgan from 'morgan';
import cookieParser from 'cookie-parser';
import 'express-async-errors';

import { config } from './config';
import { logger, morganStream } from './utils/logger';
import { 
  errorHandler, 
  notFoundHandler, 
  setupGlobalErrorHandling,
  securityHeaders,
  corsSecurityCheck,
  sanitizeRequest,
  generalRateLimit
} from './middleware';

// Import routes (will be created next)
import apiRoutes from './routes';

class App {
  public app: express.Application;

  constructor() {
    this.app = express();
    this.setupGlobalErrorHandling();
    this.setupMiddleware();
    this.setupRoutes();
    this.setupErrorHandling();
  }

  private setupGlobalErrorHandling(): void {
    setupGlobalErrorHandling();
  }

  private setupMiddleware(): void {
    // Security middleware
    this.app.use(securityHeaders);
    
    // CORS configuration
    this.app.use(cors({
      origin: (origin, callback) => {
        const allowedOrigins = [
          config.app.frontendUrl,
          'http://localhost:3000',
          'http://localhost:3001'
        ];
        
        // Allow requests with no origin (mobile apps, etc.)
        if (!origin) return callback(null, true);
        
        if (allowedOrigins.indexOf(origin) !== -1) {
          callback(null, true);
        } else {
          callback(new Error('Not allowed by CORS'));
        }
      },
      credentials: true,
      optionsSuccessStatus: 200,
      methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
      allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
    }));

    // Additional CORS security check
    this.app.use(corsSecurityCheck);

    // Rate limiting
    this.app.use(generalRateLimit);

    // Request parsing middleware
    this.app.use(express.json({ limit: '10mb' }));
    this.app.use(express.urlencoded({ extended: true, limit: '10mb' }));
    this.app.use(cookieParser());

    // Compression middleware
    this.app.use(compression());

    // Request sanitization
    this.app.use(sanitizeRequest);

    // Logging middleware
    const morganFormat = config.app.env === 'development' 
      ? 'dev' 
      : 'combined';
    
    this.app.use(morgan(morganFormat, { stream: morganStream }));

    // Trust proxy for accurate IP addresses
    this.app.set('trust proxy', 1);

    // Static file serving for uploads
    this.app.use('/uploads', express.static('uploads'));
  }

  private setupRoutes(): void {
    // Health check endpoint
    this.app.get('/health', (req, res) => {
      res.status(200).json({
        success: true,
        message: 'ILab UNMUL API is running',
        data: {
          service: config.app.name,
          version: '1.0.0',
          environment: config.app.env,
          timestamp: new Date().toISOString()
        }
      });
    });

    // API routes
    this.app.use(config.app.apiBasePath, apiRoutes);

    // Catch-all for undefined routes
    this.app.use('*', notFoundHandler);
  }

  private setupErrorHandling(): void {
    // Global error handler (must be last)
    this.app.use(errorHandler);
  }

  public start(): void {
    const port = config.app.port;
    
    this.app.listen(port, () => {
      logger.info(`🚀 ${config.app.name} server started`, {
        port,
        environment: config.app.env,
        apiBasePath: config.app.apiBasePath
      });
    });
  }
}

export default App;