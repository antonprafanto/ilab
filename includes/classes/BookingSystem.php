<?php
/**
 * Advanced Booking System untuk ILab UNMUL
 * Dengan calendar integration dan 8-step business process tracking
 */

class BookingSystem {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create new booking dengan multi-service integration
     */
    public function createBooking($user_id, $booking_data) {
        try {
            $this->db->beginTransaction();
            
            // Validate booking data
            $errors = $this->validateBookingData($booking_data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Check time slot availability
            if (!$this->isTimeSlotAvailable($booking_data['booking_date'], $booking_data['time_start'], $booking_data['time_end'])) {
                return ['success' => false, 'errors' => ['Waktu yang dipilih tidak tersedia']];
            }
            
            // Generate booking code
            $booking_code = $this->generateBookingCode();
            
            // Calculate estimated cost
            $estimated_cost = $this->calculateEstimatedCost($booking_data);
            
            // Insert booking
            $stmt = $this->db->prepare("
                INSERT INTO facility_bookings (
                    booking_code, user_id, service_category_id, service_type_id, 
                    facility_requested, purpose, sample_description, 
                    booking_date, time_start, time_end, 
                    estimated_cost, status, current_process_step, 
                    process_type, priority
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', 1, ?, ?)
            ");
            
            $result = $stmt->execute([
                $booking_code,
                $user_id,
                $booking_data['service_category_id'],
                $booking_data['service_type_id'],
                $booking_data['facility_requested'],
                $booking_data['purpose'],
                $booking_data['sample_description'] ?? null,
                $booking_data['booking_date'],
                $booking_data['time_start'],
                $booking_data['time_end'],
                $estimated_cost,
                $booking_data['process_type'] ?? 'text_based_8step',
                $booking_data['priority'] ?? 'normal'
            ]);
            
            if (!$result) {
                throw new Exception('Failed to create booking');
            }
            
            $booking_id = $this->db->lastInsertId();
            
            // Handle equipment booking
            if (!empty($booking_data['equipment_ids']) && is_array($booking_data['equipment_ids'])) {
                $this->bookEquipment($booking_id, $booking_data['equipment_ids'], $booking_data['booking_date'], 
                                   $booking_data['time_start'], $booking_data['time_end']);
            }
            
            // Initialize process tracking
            $this->initializeProcessTracking($booking_id, $booking_data['process_type'] ?? 'text_based_8step');
            
            // Send notification email
            $this->sendBookingNotification($booking_id, 'created');
            
            // Log activity
            log_activity($user_id, 'booking_created', "Booking created: $booking_code");
            
            $this->db->commit();
            
            return [
                'success' => true,
                'booking_id' => $booking_id,
                'booking_code' => $booking_code,
                'message' => 'Booking berhasil dibuat. Anda akan menerima notifikasi untuk langkah selanjutnya.'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Booking creation error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Terjadi kesalahan sistem. Silakan coba lagi.']];
        }
    }
    
    /**
     * Update booking status dan advance process step
     */
    public function updateBookingStatus($booking_id, $new_status, $admin_notes = '', $assigned_to = null) {
        try {
            $this->db->beginTransaction();
            
            // Get current booking
            $booking = $this->getBookingById($booking_id);
            if (!$booking) {
                return ['success' => false, 'error' => 'Booking tidak ditemukan'];
            }
            
            // Update booking status
            $stmt = $this->db->prepare("
                UPDATE facility_bookings 
                SET status = ?, admin_notes = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$new_status, $admin_notes, $booking_id]);
            
            // Update process tracking
            $this->updateProcessTracking($booking_id, $new_status, $assigned_to);
            
            // Send notification
            $this->sendBookingNotification($booking_id, 'status_updated');
            
            // Log activity
            log_activity($assigned_to ?? 0, 'booking_updated', "Booking $booking[booking_code] updated to $new_status");
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Status booking berhasil diperbarui'];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Booking update error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Gagal memperbarui status booking'];
        }
    }
    
    /**
     * Get available time slots untuk calendar integration
     */
    public function getAvailableTimeSlots($date, $duration_hours = 2) {
        try {
            // Get existing bookings for the date
            $stmt = $this->db->prepare("
                SELECT time_start, time_end 
                FROM facility_bookings 
                WHERE booking_date = ? AND status NOT IN ('cancelled', 'completed')
                ORDER BY time_start
            ");
            $stmt->execute([$date]);
            $existing_bookings = $stmt->fetchAll();
            
            // Define working hours (08:00 - 17:00)
            $start_hour = 8;
            $end_hour = 17;
            $available_slots = [];
            
            for ($hour = $start_hour; $hour <= $end_hour - $duration_hours; $hour++) {
                $slot_start = sprintf('%02d:00', $hour);
                $slot_end = sprintf('%02d:00', $hour + $duration_hours);
                
                // Check if slot conflicts with existing bookings
                $is_available = true;
                foreach ($existing_bookings as $booking) {
                    if ($this->timeSlotsOverlap($slot_start, $slot_end, $booking['time_start'], $booking['time_end'])) {
                        $is_available = false;
                        break;
                    }
                }
                
                if ($is_available) {
                    $available_slots[] = [
                        'start' => $slot_start,
                        'end' => $slot_end,
                        'duration' => $duration_hours
                    ];
                }
            }
            
            return $available_slots;
            
        } catch (Exception $e) {
            error_log("Get available slots error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get calendar data untuk booking interface
     */
    public function getCalendarData($month, $year) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    booking_date,
                    COUNT(*) as booking_count,
                    SUM(CASE WHEN status IN ('submitted', 'verified', 'scheduled') THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as active_count
                FROM facility_bookings 
                WHERE MONTH(booking_date) = ? AND YEAR(booking_date) = ?
                GROUP BY booking_date
                ORDER BY booking_date
            ");
            $stmt->execute([$month, $year]);
            $calendar_data = $stmt->fetchAll();
            
            // Format untuk calendar widget
            $formatted_data = [];
            foreach ($calendar_data as $data) {
                $formatted_data[$data['booking_date']] = [
                    'total' => $data['booking_count'],
                    'pending' => $data['pending_count'],
                    'active' => $data['active_count'],
                    'availability' => $this->calculateDayAvailability($data['booking_date'])
                ];
            }
            
            return $formatted_data;
            
        } catch (Exception $e) {
            error_log("Get calendar data error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get booking by ID dengan full details
     */
    public function getBookingById($booking_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    fb.*,
                    u.full_name as user_name,
                    u.email as user_email,
                    ur.role_name,
                    sc.category_name,
                    st.type_name
                FROM facility_bookings fb
                JOIN users u ON fb.user_id = u.id
                JOIN user_roles ur ON u.role_id = ur.id
                JOIN service_categories sc ON fb.service_category_id = sc.id
                JOIN service_types st ON fb.service_type_id = st.id
                WHERE fb.id = ?
            ");
            $stmt->execute([$booking_id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get booking error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get process tracking untuk booking
     */
    public function getProcessTracking($booking_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    bpt.*,
                    u.full_name as assigned_name
                FROM booking_process_tracking bpt
                LEFT JOIN users u ON bpt.assigned_to = u.id
                WHERE bpt.booking_id = ?
                ORDER BY bpt.process_step
            ");
            $stmt->execute([$booking_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get process tracking error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user bookings dengan pagination
     */
    public function getUserBookings($user_id, $limit = 10, $offset = 0, $status_filter = null) {
        try {
            $where_clause = "WHERE fb.user_id = ?";
            $params = [$user_id];
            
            if ($status_filter) {
                $where_clause .= " AND fb.status = ?";
                $params[] = $status_filter;
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    fb.*,
                    sc.category_name,
                    st.type_name
                FROM facility_bookings fb
                JOIN service_categories sc ON fb.service_category_id = sc.id
                JOIN service_types st ON fb.service_type_id = st.id
                $where_clause
                ORDER BY fb.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get user bookings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get service categories and types
     */
    public function getServiceCategories() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM service_categories ORDER BY category_name");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get service categories error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getServiceTypes($category_id = null) {
        try {
            if ($category_id) {
                $stmt = $this->db->prepare("
                    SELECT * FROM service_types 
                    WHERE JSON_CONTAINS(applicable_categories, ?)
                    ORDER BY type_name
                ");
                $stmt->execute([json_encode((string)$category_id)]);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM service_types ORDER BY type_name");
                $stmt->execute();
            }
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get service types error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate booking data
     */
    private function validateBookingData($data) {
        $errors = [];
        
        if (empty($data['service_category_id'])) {
            $errors[] = 'Kategori layanan harus dipilih';
        }
        
        if (empty($data['service_type_id'])) {
            $errors[] = 'Jenis layanan harus dipilih';
        }
        
        if (empty($data['facility_requested'])) {
            $errors[] = 'Fasilitas yang diminta harus diisi';
        }
        
        if (empty($data['purpose'])) {
            $errors[] = 'Tujuan penggunaan harus diisi';
        }
        
        if (empty($data['booking_date'])) {
            $errors[] = 'Tanggal booking harus dipilih';
        } elseif (strtotime($data['booking_date']) < strtotime('today')) {
            $errors[] = 'Tanggal booking tidak boleh hari yang sudah lewat';
        }
        
        if (empty($data['time_start']) || empty($data['time_end'])) {
            $errors[] = 'Waktu mulai dan selesai harus diisi';
        } elseif (strtotime($data['time_start']) >= strtotime($data['time_end'])) {
            $errors[] = 'Waktu selesai harus lebih besar dari waktu mulai';
        }
        
        return $errors;
    }
    
    /**
     * Check if time slot is available
     */
    private function isTimeSlotAvailable($date, $time_start, $time_end) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM facility_bookings 
                WHERE booking_date = ? 
                AND status NOT IN ('cancelled', 'completed')
                AND (
                    (time_start <= ? AND time_end > ?) OR 
                    (time_start < ? AND time_end >= ?) OR 
                    (time_start >= ? AND time_end <= ?)
                )
            ");
            $stmt->execute([$date, $time_start, $time_start, $time_end, $time_end, $time_start, $time_end]);
            $result = $stmt->fetch();
            
            return $result['count'] == 0;
        } catch (Exception $e) {
            error_log("Check availability error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate unique booking code
     */
    private function generateBookingCode() {
        $prefix = 'BK' . date('y');
        do {
            $code = $prefix . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("SELECT id FROM facility_bookings WHERE booking_code = ?");
            $stmt->execute([$code]);
        } while ($stmt->fetch());
        
        return $code;
    }
    
    /**
     * Calculate estimated cost
     */
    private function calculateEstimatedCost($booking_data) {
        try {
            // Get pricing based on service category and type
            $stmt = $this->db->prepare("
                SELECT price, pricing_type 
                FROM service_pricing 
                WHERE service_category_id = ? AND service_type_id = ? 
                AND is_active = 1 AND effective_from <= CURDATE() 
                AND (effective_until IS NULL OR effective_until >= CURDATE())
                ORDER BY effective_from DESC LIMIT 1
            ");
            $stmt->execute([$booking_data['service_category_id'], $booking_data['service_type_id']]);
            $pricing = $stmt->fetch();
            
            if (!$pricing) {
                return 0; // Default free or will be calculated manually
            }
            
            $duration_hours = (strtotime($booking_data['time_end']) - strtotime($booking_data['time_start'])) / 3600;
            
            switch ($pricing['pricing_type']) {
                case 'per_hour':
                    return $pricing['price'] * $duration_hours;
                case 'per_sample':
                case 'per_analysis':
                    return $pricing['price']; // Will be multiplied by sample count later
                case 'fixed':
                default:
                    return $pricing['price'];
            }
        } catch (Exception $e) {
            error_log("Calculate cost error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Initialize process tracking untuk booking baru
     */
    private function initializeProcessTracking($booking_id, $process_type) {
        try {
            // Get process steps
            $stmt = $this->db->prepare("
                SELECT * FROM business_process_steps 
                WHERE process_type = ? 
                ORDER BY step_number
            ");
            $stmt->execute([$process_type]);
            $steps = $stmt->fetchAll();
            
            // Initialize tracking untuk setiap step
            foreach ($steps as $step) {
                $status = ($step['step_number'] == 1) ? 'in_progress' : 'pending';
                
                $stmt = $this->db->prepare("
                    INSERT INTO booking_process_tracking (
                        booking_id, process_step, step_name, status, created_at
                    ) VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $booking_id,
                    $step['step_number'],
                    $step['step_name'],
                    $status
                ]);
            }
        } catch (Exception $e) {
            error_log("Initialize process tracking error: " . $e->getMessage());
        }
    }
    
    /**
     * Update process tracking
     */
    private function updateProcessTracking($booking_id, $new_status, $assigned_to) {
        try {
            // Map status to process step
            $status_step_map = [
                'submitted' => 1,
                'verified' => 2,
                'scheduled' => 3,
                'in_progress' => 4,
                'testing' => 5,
                'reporting' => 6,
                'payment_pending' => 7,
                'completed' => 8
            ];
            
            $current_step = $status_step_map[$new_status] ?? 1;
            
            // Update current step
            $stmt = $this->db->prepare("
                UPDATE booking_process_tracking 
                SET status = 'completed', completed_at = NOW(), assigned_to = ?
                WHERE booking_id = ? AND process_step < ?
            ");
            $stmt->execute([$assigned_to, $booking_id, $current_step]);
            
            // Set current step as in_progress
            $stmt = $this->db->prepare("
                UPDATE booking_process_tracking 
                SET status = 'in_progress', started_at = NOW(), assigned_to = ?
                WHERE booking_id = ? AND process_step = ?
            ");
            $stmt->execute([$assigned_to, $booking_id, $current_step]);
            
            // Update booking current_process_step
            $stmt = $this->db->prepare("
                UPDATE facility_bookings 
                SET current_process_step = ? 
                WHERE id = ?
            ");
            $stmt->execute([$current_step, $booking_id]);
            
        } catch (Exception $e) {
            error_log("Update process tracking error: " . $e->getMessage());
        }
    }
    
    /**
     * Send booking notification
     */
    private function sendBookingNotification($booking_id, $type) {
        try {
            // Get booking details
            $booking = $this->getBookingById($booking_id);
            if (!$booking) return;
            
            // Prepare email content based on type
            $subject = '';
            $message = '';
            
            switch ($type) {
                case 'created':
                    $subject = "Booking Konfirmasi - {$booking['booking_code']}";
                    $message = "Booking Anda telah berhasil dibuat dan sedang dalam proses verifikasi.";
                    break;
                case 'status_updated':
                    $subject = "Update Status Booking - {$booking['booking_code']}";
                    $message = "Status booking Anda telah diperbarui menjadi: " . ucfirst($booking['status']);
                    break;
            }
            
            // Send email (implement according to your email service)
            // mail($booking['user_email'], $subject, $message);
            
            error_log("Email notification sent: $subject to {$booking['user_email']}");
            
        } catch (Exception $e) {
            error_log("Send notification error: " . $e->getMessage());
        }
    }
    
    /**
     * Helper methods
     */
    private function timeSlotsOverlap($start1, $end1, $start2, $end2) {
        return (strtotime($start1) < strtotime($end2)) && (strtotime($end1) > strtotime($start2));
    }
    
    private function calculateDayAvailability($date) {
        $total_slots = 9; // 8 AM - 5 PM = 9 hours
        $booked_slots = count($this->getAvailableTimeSlots($date, 1));
        return max(0, $total_slots - $booked_slots);
    }
    
    /**
     * Book equipment for a specific booking
     */
    private function bookEquipment($booking_id, $equipment_ids, $booking_date, $time_start, $time_end) {
        try {
            foreach ($equipment_ids as $equipment_id) {
                // Check if equipment is available
                if (!$this->isEquipmentAvailable($equipment_id, $booking_date, $time_start, $time_end)) {
                    // Get equipment name for error message
                    $stmt = $this->db->prepare("SELECT equipment_name FROM equipment WHERE id = ?");
                    $stmt->execute([$equipment_id]);
                    $equipment = $stmt->fetch();
                    $equipment_name = $equipment ? $equipment['equipment_name'] : "Equipment ID $equipment_id";
                    
                    throw new Exception("Equipment '$equipment_name' is not available for the selected time slot");
                }
                
                // Create equipment booking record
                $stmt = $this->db->prepare("
                    INSERT INTO equipment_bookings (
                        booking_id, equipment_id, booking_date, time_start, time_end, 
                        status, created_at
                    ) VALUES (?, ?, ?, ?, ?, 'booked', NOW())
                ");
                $stmt->execute([$booking_id, $equipment_id, $booking_date, $time_start, $time_end]);
                
                // Update equipment status to 'reserved' for the time period
                $stmt = $this->db->prepare("
                    UPDATE equipment 
                    SET status = 'in_use', last_used_date = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$booking_date, $equipment_id]);
            }
        } catch (Exception $e) {
            throw new Exception("Equipment booking failed: " . $e->getMessage());
        }
    }
    
    /**
     * Check if equipment is available for booking
     */
    private function isEquipmentAvailable($equipment_id, $booking_date, $time_start, $time_end) {
        try {
            // Check if equipment exists and is available
            $stmt = $this->db->prepare("
                SELECT status FROM equipment 
                WHERE id = ? AND status IN ('available', 'in_use')
            ");
            $stmt->execute([$equipment_id]);
            $equipment = $stmt->fetch();
            
            if (!$equipment) {
                return false; // Equipment doesn't exist or not available
            }
            
            // Check for conflicting bookings
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM equipment_bookings eb
                JOIN facility_bookings fb ON eb.booking_id = fb.id
                WHERE eb.equipment_id = ? 
                AND eb.booking_date = ?
                AND fb.status NOT IN ('cancelled', 'completed')
                AND (
                    (eb.time_start <= ? AND eb.time_end > ?) OR 
                    (eb.time_start < ? AND eb.time_end >= ?) OR 
                    (eb.time_start >= ? AND eb.time_end <= ?)
                )
            ");
            $stmt->execute([
                $equipment_id, $booking_date,
                $time_start, $time_start,
                $time_end, $time_end,
                $time_start, $time_end
            ]);
            $result = $stmt->fetch();
            
            return $result['count'] == 0;
            
        } catch (Exception $e) {
            error_log("Equipment availability check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get equipment bookings for a specific booking
     */
    public function getBookingEquipment($booking_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    e.*,
                    ec.category_name,
                    eb.booking_date,
                    eb.time_start,
                    eb.time_end,
                    eb.status as booking_status
                FROM equipment_bookings eb
                JOIN equipment e ON eb.equipment_id = e.id
                JOIN equipment_categories ec ON e.category_id = ec.id
                WHERE eb.booking_id = ?
                ORDER BY ec.category_name, e.equipment_name
            ");
            $stmt->execute([$booking_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get booking equipment error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update equipment status when booking status changes
     */
    public function updateEquipmentStatus($booking_id, $new_status) {
        try {
            if ($new_status === 'completed' || $new_status === 'cancelled') {
                // Release equipment
                $stmt = $this->db->prepare("
                    UPDATE equipment e
                    JOIN equipment_bookings eb ON e.id = eb.equipment_id
                    SET e.status = 'available'
                    WHERE eb.booking_id = ?
                ");
                $stmt->execute([$booking_id]);
                
                // Update equipment booking status
                $stmt = $this->db->prepare("
                    UPDATE equipment_bookings 
                    SET status = ? 
                    WHERE booking_id = ?
                ");
                $stmt->execute([$new_status, $booking_id]);
            }
        } catch (Exception $e) {
            error_log("Update equipment status error: " . $e->getMessage());
        }
    }
}
?>