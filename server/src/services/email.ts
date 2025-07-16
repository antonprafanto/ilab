import nodemailer from 'nodemailer';
import { config } from '../config';
import { logger } from '../utils/logger';

interface EmailOptions {
  to: string;
  subject: string;
  html: string;
  text?: string;
}

class EmailService {
  private transporter: nodemailer.Transporter;

  constructor() {
    this.transporter = nodemailer.createTransport({
      host: config.email.smtp.host,
      port: config.email.smtp.port,
      secure: config.email.smtp.secure,
      auth: config.email.smtp.auth.user ? {
        user: config.email.smtp.auth.user,
        pass: config.email.smtp.auth.pass
      } : undefined
    });
  }

  async sendEmail(options: EmailOptions): Promise<void> {
    if (!config.features.enableEmailNotifications) {
      logger.info('Email notifications disabled, skipping email send');
      return;
    }

    try {
      const mailOptions = {
        from: config.email.from,
        to: options.to,
        subject: options.subject,
        html: options.html,
        text: options.text
      };

      const result = await this.transporter.sendMail(mailOptions);
      
      logger.info('Email sent successfully:', {
        to: options.to,
        subject: options.subject,
        messageId: result.messageId
      });
    } catch (error) {
      logger.error('Failed to send email:', {
        to: options.to,
        subject: options.subject,
        error: error instanceof Error ? error.message : error
      });
      throw error;
    }
  }

  async sendVerificationEmail(email: string, firstName: string, verificationToken: string): Promise<void> {
    const verificationUrl = `${config.app.frontendUrl}/verify-email?token=${verificationToken}`;
    
    const html = `
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>Verifikasi Email - ILab UNMUL</title>
        <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
          .container { max-width: 600px; margin: 0 auto; padding: 20px; }
          .header { background: #1e40af; color: white; padding: 20px; text-align: center; }
          .content { padding: 20px; background: #f9f9f9; }
          .button { display: inline-block; background: #1e40af; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
          .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <h1>ILab UNMUL</h1>
            <h2>Verifikasi Email Anda</h2>
          </div>
          <div class="content">
            <p>Halo ${firstName},</p>
            <p>Terima kasih telah mendaftar di ILab UNMUL. Untuk menyelesaikan pendaftaran Anda, silakan verifikasi email Anda dengan mengklik tombol di bawah ini:</p>
            <p style="text-align: center;">
              <a href="${verificationUrl}" class="button">Verifikasi Email</a>
            </p>
            <p>Atau copy dan paste link berikut di browser Anda:</p>
            <p><a href="${verificationUrl}">${verificationUrl}</a></p>
            <p>Link verifikasi ini akan berlaku selama 24 jam.</p>
            <p>Jika Anda tidak mendaftar di ILab UNMUL, silakan abaikan email ini.</p>
            <p>Salam,<br>Tim ILab UNMUL</p>
          </div>
          <div class="footer">
            <p>© 2024 Integrated Laboratory UNMUL</p>
            <p>Kampus Gunung Kelua, Samarinda, Kalimantan Timur</p>
          </div>
        </div>
      </body>
      </html>
    `;

    await this.sendEmail({
      to: email,
      subject: 'Verifikasi Email - ILab UNMUL',
      html
    });
  }

  async sendPasswordResetEmail(email: string, firstName: string, resetToken: string): Promise<void> {
    const resetUrl = `${config.app.frontendUrl}/reset-password?token=${resetToken}`;
    
    const html = `
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>Reset Password - ILab UNMUL</title>
        <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
          .container { max-width: 600px; margin: 0 auto; padding: 20px; }
          .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
          .content { padding: 20px; background: #f9f9f9; }
          .button { display: inline-block; background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
          .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
          .warning { background: #fef2f2; border: 1px solid #fecaca; padding: 10px; border-radius: 4px; margin: 10px 0; }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <h1>ILab UNMUL</h1>
            <h2>Reset Password</h2>
          </div>
          <div class="content">
            <p>Halo ${firstName},</p>
            <p>Kami menerima permintaan untuk reset password akun Anda di ILab UNMUL.</p>
            <p style="text-align: center;">
              <a href="${resetUrl}" class="button">Reset Password</a>
            </p>
            <p>Atau copy dan paste link berikut di browser Anda:</p>
            <p><a href="${resetUrl}">${resetUrl}</a></p>
            <div class="warning">
              <strong>⚠️ Penting:</strong>
              <ul>
                <li>Link reset password ini akan berlaku selama 1 jam</li>
                <li>Jika Anda tidak meminta reset password, silakan abaikan email ini</li>
                <li>Untuk keamanan, segera ganti password Anda setelah reset</li>
              </ul>
            </div>
            <p>Jika Anda mengalami kesulitan, silakan hubungi administrator di ${config.email.adminEmail}</p>
            <p>Salam,<br>Tim ILab UNMUL</p>
          </div>
          <div class="footer">
            <p>© 2024 Integrated Laboratory UNMUL</p>
            <p>Kampus Gunung Kelua, Samarinda, Kalimantan Timur</p>
          </div>
        </div>
      </body>
      </html>
    `;

    await this.sendEmail({
      to: email,
      subject: 'Reset Password - ILab UNMUL',
      html
    });
  }

  async sendWelcomeEmail(email: string, firstName: string): Promise<void> {
    const loginUrl = `${config.app.frontendUrl}/login`;
    
    const html = `
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>Selamat Datang di ILab UNMUL</title>
        <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
          .container { max-width: 600px; margin: 0 auto; padding: 20px; }
          .header { background: #059669; color: white; padding: 20px; text-align: center; }
          .content { padding: 20px; background: #f9f9f9; }
          .button { display: inline-block; background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
          .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
          .features { background: white; padding: 15px; border-radius: 4px; margin: 15px 0; }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <h1>🎉 Selamat Datang di ILab UNMUL!</h1>
          </div>
          <div class="content">
            <p>Halo ${firstName},</p>
            <p>Selamat! Akun Anda telah berhasil diverifikasi dan Anda sekarang dapat mengakses semua layanan ILab UNMUL.</p>
            
            <div class="features">
              <h3>Yang dapat Anda lakukan:</h3>
              <ul>
                <li>📅 Booking peralatan laboratorium (GC-MS, LC-MS/MS, AAS, FTIR, dll)</li>
                <li>🧪 Submit sampel untuk analisis</li>
                <li>📊 Tracking status pengujian real-time</li>
                <li>💰 Kelola pembayaran dan invoice</li>
                <li>📁 Download hasil analisis</li>
                <li>📞 Akses informasi kontak dan SOP</li>
              </ul>
            </div>

            <p style="text-align: center;">
              <a href="${loginUrl}" class="button">Mulai Menggunakan ILab</a>
            </p>

            <p><strong>Informasi Penting:</strong></p>
            <ul>
              <li>Jam operasional: Senin-Jumat 08:00-17:00, Sabtu 08:00-12:00</li>
              <li>Untuk booking, mohon buat reservasi minimal 24 jam sebelumnya</li>
              <li>Pertanyaan teknis dapat dihubungi melalui platform atau email</li>
            </ul>

            <p>Jika Anda membutuhkan bantuan, jangan ragu untuk menghubungi kami di ${config.email.adminEmail}</p>
            
            <p>Selamat menggunakan layanan ILab UNMUL!</p>
            <p>Salam,<br>Tim ILab UNMUL</p>
          </div>
          <div class="footer">
            <p>© 2024 Integrated Laboratory UNMUL</p>
            <p>📧 ${config.email.adminEmail} | 📞 +62-541-749326</p>
            <p>Kampus Gunung Kelua, Samarinda, Kalimantan Timur</p>
          </div>
        </div>
      </body>
      </html>
    `;

    await this.sendEmail({
      to: email,
      subject: 'Selamat Datang di ILab UNMUL! 🎉',
      html
    });
  }

  async testConnection(): Promise<boolean> {
    try {
      await this.transporter.verify();
      return true;
    } catch (error) {
      logger.error('Email service connection test failed:', error);
      return false;
    }
  }
}

export const emailService = new EmailService();