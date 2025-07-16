import { Request, Response } from 'express';
import { z } from 'zod';
import { database } from '../config/database';
import { ResponseHelper } from '../utils/response';
import { ValidationError, NotFoundError, ConflictError } from '../utils/errors';
import { logger } from '../utils/logger';
import { RowDataPacket, ResultSetHeader } from 'mysql2';

// Removed local Request interface - using global Express.Request extension

// Payment validation schemas
const CreatePaymentSchema = z.object({
  bookingId: z.string().uuid(),
  subtotal: z.number().min(0),
  tax: z.number().min(0).default(0),
  discount: z.number().min(0).default(0),
  totalAmount: z.number().min(0),
  paymentMethod: z.enum(['cash', 'bank_transfer', 'credit_card', 'digital_wallet', 'check']).optional(),
  paymentReference: z.string().optional(),
  dueDate: z.string().date(),
  notes: z.string().optional(),
  billTo: z.object({
    name: z.string(),
    email: z.string().email(),
    phone: z.string().optional(),
    address: z.string().optional(),
    organization: z.string().optional()
  }),
  items: z.array(z.object({
    description: z.string().min(1),
    quantity: z.number().min(0),
    unitPrice: z.number().min(0),
    totalPrice: z.number().min(0),
    category: z.enum(['equipment_usage', 'sample_analysis', 'consultation', 'training', 'other'])
  }))
});

const UpdatePaymentSchema = CreatePaymentSchema.partial().extend({
  status: z.enum(['pending', 'paid', 'partial', 'overdue', 'cancelled', 'refunded']).optional(),
  paidAmount: z.number().min(0).optional(),
  remainingAmount: z.number().min(0).optional(),
  paidAt: z.string().datetime().optional()
});

const CreatePaymentRecordSchema = z.object({
  paymentId: z.string().uuid(),
  amount: z.number().min(0),
  method: z.enum(['cash', 'bank_transfer', 'credit_card', 'digital_wallet', 'check']),
  reference: z.string().optional(),
  notes: z.string().optional()
});

class PaymentsController {
  async getPayments(req: Request, res: Response): Promise<void> {
    try {
      const page = parseInt(req.query.page as string) || 1;
      const limit = parseInt(req.query.limit as string) || 10;
      const search = req.query.search as string || '';
      const status = req.query.status as string || '';
      const paymentMethod = req.query.paymentMethod as string || '';
      const bookingId = req.query.bookingId as string || '';
      const userId = req.query.userId as string || '';
      const startDate = req.query.startDate as string || '';
      const endDate = req.query.endDate as string || '';

      const offset = (page - 1) * limit;

      let whereConditions = ['1 = 1'];
      const queryParams: any[] = [];

      // Role-based filtering
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head'].includes(userRole || '')) {
        // Non-admin users can only see their own payments
        whereConditions.push('p.user_id = ?');
        queryParams.push(req.user?.userId);
      }

      if (search) {
        whereConditions.push('(p.invoice_number LIKE ? OR JSON_EXTRACT(p.bill_to, "$.name") LIKE ?)');
        queryParams.push(`%${search}%`, `%${search}%`);
      }

      if (status) {
        whereConditions.push('p.status = ?');
        queryParams.push(status);
      }

      if (paymentMethod) {
        whereConditions.push('p.payment_method = ?');
        queryParams.push(paymentMethod);
      }

      if (bookingId) {
        whereConditions.push('p.booking_id = ?');
        queryParams.push(bookingId);
      }

      if (userId) {
        whereConditions.push('p.user_id = ?');
        queryParams.push(userId);
      }

      if (startDate) {
        whereConditions.push('DATE(p.created_at) >= ?');
        queryParams.push(startDate);
      }

      if (endDate) {
        whereConditions.push('DATE(p.created_at) <= ?');
        queryParams.push(endDate);
      }

      const whereClause = whereConditions.join(' AND ');

      // Get total count
      const [countResult] = await database.execute(`
        SELECT COUNT(*) as total
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.id
        LEFT JOIN users u ON p.user_id = u.id
        WHERE ${whereClause}
      `, queryParams) as [RowDataPacket[], any];

      const total = countResult[0].total;

      // Get payments with pagination
      const [payments] = await database.execute(`
        SELECT 
          p.id,
          p.invoice_number,
          p.subtotal,
          p.tax,
          p.discount,
          p.total_amount,
          p.paid_amount,
          p.remaining_amount,
          p.status,
          p.payment_method,
          p.payment_reference,
          p.due_date,
          p.paid_at,
          p.notes,
          p.bill_to,
          p.created_at,
          p.updated_at,
          b.id as booking_id,
          b.title as booking_title,
          b.start_time as booking_start_time,
          e.name as equipment_name,
          e.type as equipment_type,
          u.id as user_id,
          CONCAT(u.first_name, ' ', u.last_name) as user_name,
          u.email as user_email,
          creator.id as created_by_id,
          CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name,
          approver.id as approved_by_id,
          CONCAT(approver.first_name, ' ', approver.last_name) as approved_by_name,
          p.approved_at
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.id
        LEFT JOIN equipment e ON b.equipment_id = e.id
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN users creator ON p.created_by = creator.id
        LEFT JOIN users approver ON p.approved_by = approver.id
        WHERE ${whereClause}
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
      `, [...queryParams, limit, offset]) as [RowDataPacket[], any];

      // Parse JSON fields
      const paymentsWithParsedJson = payments.map(payment => ({
        ...payment,
        bill_to: payment.bill_to ? JSON.parse(payment.bill_to) : {}
      }));

      const totalPages = Math.ceil(total / limit);

      ResponseHelper.success(res, {
        payments: paymentsWithParsedJson,
        pagination: {
          page,
          limit,
          total,
          totalPages,
          hasNext: page < totalPages,
          hasPrev: page > 1
        }
      }, 'Payments retrieved successfully');

    } catch (error) {
      logger.error('Error getting payments:', error);
      throw error;
    }
  }

  async getPaymentById(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid payment ID format');
      }

      const [payments] = await database.execute(`
        SELECT 
          p.id,
          p.invoice_number,
          p.subtotal,
          p.tax,
          p.discount,
          p.total_amount,
          p.paid_amount,
          p.remaining_amount,
          p.status,
          p.payment_method,
          p.payment_reference,
          p.due_date,
          p.paid_at,
          p.notes,
          p.bill_to,
          p.created_at,
          p.updated_at,
          b.id as booking_id,
          b.title as booking_title,
          b.start_time as booking_start_time,
          b.end_time as booking_end_time,
          e.name as equipment_name,
          e.type as equipment_type,
          e.location as equipment_location,
          u.id as user_id,
          CONCAT(u.first_name, ' ', u.last_name) as user_name,
          u.email as user_email,
          u.phone_number as user_phone,
          creator.id as created_by_id,
          CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name,
          approver.id as approved_by_id,
          CONCAT(approver.first_name, ' ', approver.last_name) as approved_by_name,
          p.approved_at
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.id
        LEFT JOIN equipment e ON b.equipment_id = e.id
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN users creator ON p.created_by = creator.id
        LEFT JOIN users approver ON p.approved_by = approver.id
        WHERE p.id = ?
      `, [id]) as [RowDataPacket[], any];

      if (payments.length === 0) {
        throw new NotFoundError('Payment not found');
      }

      const payment = payments[0];

      // Check permission
      const userRole = req.user?.role;
      if (!['admin', 'director', 'vice_director', 'lab_head'].includes(userRole || '') && 
          payment.user_id !== req.user?.userId) {
        throw new ValidationError('Access denied');
      }

      // Get payment items
      const [items] = await database.execute(`
        SELECT 
          id,
          description,
          quantity,
          unit_price,
          total_price,
          category
        FROM payment_items
        WHERE payment_id = ?
        ORDER BY id ASC
      `, [id]) as [RowDataPacket[], any];

      // Get payment records
      const [records] = await database.execute(`
        SELECT 
          pr.id,
          pr.amount,
          pr.method,
          pr.reference,
          pr.notes,
          pr.processed_at,
          CONCAT(u.first_name, ' ', u.last_name) as processed_by_name
        FROM payment_records pr
        LEFT JOIN users u ON pr.processed_by = u.id
        WHERE pr.payment_id = ?
        ORDER BY pr.processed_at DESC
      `, [id]) as [RowDataPacket[], any];

      const paymentData = {
        ...payment,
        bill_to: payment.bill_to ? JSON.parse(payment.bill_to) : {},
        items,
        payment_records: records
      };

      ResponseHelper.success(res, paymentData, 'Payment retrieved successfully');

    } catch (error) {
      logger.error('Error getting payment by ID:', error);
      throw error;
    }
  }

  async createPayment(req: Request, res: Response): Promise<void> {
    try {
      const paymentData = req.body;
      const userId = req.user?.userId;

      if (!userId) {
        throw new ValidationError('User ID not found');
      }

      // Validate payment data
      const validationResult = CreatePaymentSchema.safeParse(paymentData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid payment data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      const {
        bookingId,
        subtotal,
        tax,
        discount,
        totalAmount,
        paymentMethod,
        paymentReference,
        dueDate,
        notes,
        billTo,
        items
      } = validationResult.data;

      // Check if booking exists
      const [bookings] = await database.execute(
        'SELECT id, user_id, status FROM bookings WHERE id = ?',
        [bookingId]
      ) as [RowDataPacket[], any];

      if (bookings.length === 0) {
        throw new NotFoundError('Booking not found');
      }

      // Check if payment already exists for this booking
      const [existingPayments] = await database.execute(
        'SELECT id FROM payments WHERE booking_id = ?',
        [bookingId]
      ) as [RowDataPacket[], any];

      if (existingPayments.length > 0) {
        throw new ConflictError('Payment already exists for this booking');
      }

      // Generate unique invoice number
      const year = new Date().getFullYear();
      const month = (new Date().getMonth() + 1).toString().padStart(2, '0');
      
      const [lastInvoice] = await database.execute(`
        SELECT invoice_number FROM payments 
        WHERE invoice_number LIKE ? 
        ORDER BY invoice_number DESC 
        LIMIT 1
      `, [`INV-${year}${month}%`]) as [RowDataPacket[], any];

      let sequence = 1;
      if (lastInvoice.length > 0) {
        const lastNumber = lastInvoice[0].invoice_number;
        const lastSequence = parseInt(lastNumber.substring(10));
        sequence = lastSequence + 1;
      }

      const invoiceNumber = `INV-${year}${month}${sequence.toString().padStart(4, '0')}`;

      // Calculate remaining amount
      const remainingAmount = totalAmount;

      // Insert new payment
      const paymentId = crypto.randomUUID();
      await database.execute(`
        INSERT INTO payments (
          id, booking_id, invoice_number, user_id, subtotal, tax, discount,
          total_amount, paid_amount, remaining_amount, status, payment_method,
          payment_reference, due_date, notes, bill_to, created_by
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      `, [
        paymentId,
        bookingId,
        invoiceNumber,
        bookings[0].user_id,
        subtotal,
        tax,
        discount,
        totalAmount,
        0, // initial paid amount
        remainingAmount,
        'pending',
        paymentMethod,
        paymentReference,
        dueDate,
        notes,
        JSON.stringify(billTo),
        userId
      ]);

      // Insert payment items
      for (const item of items) {
        await database.execute(`
          INSERT INTO payment_items (id, payment_id, description, quantity, unit_price, total_price, category)
          VALUES (?, ?, ?, ?, ?, ?, ?)
        `, [
          crypto.randomUUID(),
          paymentId,
          item.description,
          item.quantity,
          item.unitPrice,
          item.totalPrice,
          item.category
        ]);
      }

      // Get the created payment
      const [newPayment] = await database.execute(`
        SELECT 
          p.id,
          p.invoice_number,
          p.total_amount,
          p.status,
          p.created_at,
          b.title as booking_title
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.id
        WHERE p.id = ?
      `, [paymentId]) as [RowDataPacket[], any];

      ResponseHelper.created(res, newPayment[0], 'Payment created successfully');

    } catch (error) {
      logger.error('Error creating payment:', error);
      throw error;
    }
  }

  async updatePayment(req: Request, res: Response): Promise<void> {
    try {
      const { id } = req.params;
      const updateData = req.body;
      const userId = req.user?.userId;

      if (!z.string().uuid().safeParse(id).success) {
        throw new ValidationError('Invalid payment ID format');
      }

      // Validate update data
      const validationResult = UpdatePaymentSchema.safeParse(updateData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid payment data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      // Check if payment exists
      const [existingPayments] = await database.execute(
        'SELECT id, user_id, status, total_amount FROM payments WHERE id = ?',
        [id]
      ) as [RowDataPacket[], any];

      if (existingPayments.length === 0) {
        throw new NotFoundError('Payment not found');
      }

      const payment = existingPayments[0];

      // Check permission
      const userRole = req.user?.role;
      const canEdit = ['admin', 'director', 'vice_director', 'lab_head'].includes(userRole || '');

      if (!canEdit) {
        throw new ValidationError('Access denied');
      }

      // Build update query dynamically
      const updateFields: string[] = [];
      const updateValues: any[] = [];

      Object.entries(updateData).forEach(([key, value]) => {
        if (value !== undefined) {
          switch (key) {
            case 'bookingId':
              updateFields.push('booking_id = ?');
              updateValues.push(value);
              break;
            case 'paymentMethod':
              updateFields.push('payment_method = ?');
              updateValues.push(value);
              break;
            case 'paymentReference':
              updateFields.push('payment_reference = ?');
              updateValues.push(value);
              break;
            case 'dueDate':
              updateFields.push('due_date = ?');
              updateValues.push(value);
              break;
            case 'paidAmount':
              updateFields.push('paid_amount = ?');
              updateValues.push(value);
              // Calculate remaining amount
              const remaining = payment.total_amount - value;
              updateFields.push('remaining_amount = ?');
              updateValues.push(remaining);
              // Update status based on payment
              if (value >= payment.total_amount) {
                updateFields.push('status = ?');
                updateValues.push('paid');
              } else if (value > 0) {
                updateFields.push('status = ?');
                updateValues.push('partial');
              }
              break;
            case 'remainingAmount':
              updateFields.push('remaining_amount = ?');
              updateValues.push(value);
              break;
            case 'paidAt':
              updateFields.push('paid_at = ?');
              updateValues.push(value);
              break;
            case 'billTo':
              updateFields.push('bill_to = ?');
              updateValues.push(JSON.stringify(value));
              break;
            default:
              if (['subtotal', 'tax', 'discount', 'total_amount', 'status', 'notes'].includes(key)) {
                updateFields.push(`${key.replace(/([A-Z])/g, '_$1').toLowerCase()} = ?`);
                updateValues.push(value);
              }
              break;
          }
        }
      });

      if (updateFields.length === 0) {
        throw new ValidationError('No valid fields to update');
      }

      updateFields.push('updated_at = CURRENT_TIMESTAMP');
      updateValues.push(id);

      const updateQuery = `
        UPDATE payments 
        SET ${updateFields.join(', ')}
        WHERE id = ?
      `;

      await database.execute(updateQuery, updateValues);

      // Get updated payment
      const [updatedPayment] = await database.execute(`
        SELECT 
          p.id,
          p.invoice_number,
          p.status,
          p.total_amount,
          p.paid_amount,
          p.updated_at
        FROM payments p
        WHERE p.id = ?
      `, [id]) as [RowDataPacket[], any];

      ResponseHelper.success(res, updatedPayment[0], 'Payment updated successfully');

    } catch (error) {
      logger.error('Error updating payment:', error);
      throw error;
    }
  }

  async createPaymentRecord(req: Request, res: Response): Promise<void> {
    try {
      const recordData = req.body;
      const userId = req.user?.userId;

      if (!userId) {
        throw new ValidationError('User ID not found');
      }

      // Validate payment record data
      const validationResult = CreatePaymentRecordSchema.safeParse(recordData);
      if (!validationResult.success) {
        throw new ValidationError(
          'Invalid payment record data: ' + validationResult.error.errors.map(e => e.message).join(', ')
        );
      }

      const { paymentId, amount, method, reference, notes } = validationResult.data;

      // Check if payment exists
      const [payments] = await database.execute(
        'SELECT id, total_amount, paid_amount, status FROM payments WHERE id = ?',
        [paymentId]
      ) as [RowDataPacket[], any];

      if (payments.length === 0) {
        throw new NotFoundError('Payment not found');
      }

      const payment = payments[0];
      const newPaidAmount = payment.paid_amount + amount;
      const newRemainingAmount = payment.total_amount - newPaidAmount;

      // Check if payment amount is valid
      if (newPaidAmount > payment.total_amount) {
        throw new ValidationError('Payment amount exceeds total amount');
      }

      // Insert payment record
      const recordId = crypto.randomUUID();
      await database.execute(`
        INSERT INTO payment_records (id, payment_id, amount, method, reference, notes, processed_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
      `, [recordId, paymentId, amount, method, reference, notes, userId]);

      // Update payment with new amounts and status
      let newStatus = payment.status;
      if (newRemainingAmount <= 0) {
        newStatus = 'paid';
      } else if (newPaidAmount > 0) {
        newStatus = 'partial';
      }

      await database.execute(`
        UPDATE payments 
        SET paid_amount = ?, remaining_amount = ?, status = ?, paid_at = CURRENT_TIMESTAMP
        WHERE id = ?
      `, [newPaidAmount, newRemainingAmount, newStatus, paymentId]);

      ResponseHelper.created(res, {
        id: recordId,
        amount,
        newPaidAmount,
        newRemainingAmount,
        newStatus
      }, 'Payment record created successfully');

    } catch (error) {
      logger.error('Error creating payment record:', error);
      throw error;
    }
  }

  async getPaymentStats(req: Request, res: Response): Promise<void> {
    try {
      // Get payment statistics
      const [stats] = await database.execute(`
        SELECT 
          COUNT(*) as total_payments,
          SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
          SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_payments,
          SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) as partial_payments,
          SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_payments,
          SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_payments,
          SUM(total_amount) as total_revenue,
          SUM(paid_amount) as collected_revenue,
          SUM(remaining_amount) as outstanding_revenue,
          AVG(total_amount) as avg_payment_amount
        FROM payments
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
      `) as [RowDataPacket[], any];

      // Get revenue by month
      const [monthlyRevenue] = await database.execute(`
        SELECT 
          YEAR(created_at) as year,
          MONTH(created_at) as month,
          COUNT(*) as payment_count,
          SUM(total_amount) as total_revenue,
          SUM(paid_amount) as collected_revenue
        FROM payments
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY year DESC, month DESC
        LIMIT 12
      `) as [RowDataPacket[], any];

      // Get payment method distribution
      const [methodStats] = await database.execute(`
        SELECT 
          payment_method,
          COUNT(*) as payment_count,
          SUM(paid_amount) as total_amount
        FROM payments
        WHERE status IN ('paid', 'partial')
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY payment_method
        ORDER BY total_amount DESC
      `) as [RowDataPacket[], any];

      ResponseHelper.success(res, {
        overview: stats[0],
        monthlyRevenue,
        paymentMethodDistribution: methodStats
      }, 'Payment statistics retrieved successfully');

    } catch (error) {
      logger.error('Error getting payment stats:', error);
      throw error;
    }
  }
}

export const paymentsController = new PaymentsController();