<?php
/**
 * Business Process Tracker untuk ILab UNMUL
 * Implementasi dual 8-step + 7-step business process dengan real-time tracking
 */

class ProcessTracker {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get process tracking untuk booking dengan real-time status
     */
    public function getBookingProcessTracking($booking_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    bpt.*,
                    bps.description as step_description,
                    bps.actor,
                    bps.input_required,
                    bps.output_generated,
                    bps.timeline_days,
                    bps.result_expected,
                    u_assigned.full_name as assigned_name,
                    u_assigned.email as assigned_email,
                    fb.process_type,
                    fb.status as booking_status
                FROM booking_process_tracking bpt
                JOIN business_process_steps bps ON bpt.process_step = bps.step_number 
                LEFT JOIN users u_assigned ON bpt.assigned_to = u_assigned.id
                LEFT JOIN facility_bookings fb ON bpt.booking_id = fb.id
                WHERE bpt.booking_id = ? AND bps.process_type = fb.process_type
                ORDER BY bpt.process_step
            ");
            $stmt->execute([$booking_id]);
            $tracking_data = $stmt->fetchAll();
            
            // Add progress calculation
            $total_steps = count($tracking_data);
            $completed_steps = count(array_filter($tracking_data, function($step) {
                return $step['status'] === 'completed';
            }));
            
            return [
                'steps' => $tracking_data,
                'progress' => [
                    'total' => $total_steps,
                    'completed' => $completed_steps,
                    'percentage' => $total_steps > 0 ? round(($completed_steps / $total_steps) * 100) : 0
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Get process tracking error: " . $e->getMessage());
            return ['steps' => [], 'progress' => ['total' => 0, 'completed' => 0, 'percentage' => 0]];
        }
    }
    
    /**
     * Advance booking ke step berikutnya
     */
    public function advanceBookingStep($booking_id, $current_step, $notes = '', $attachments = [], $assigned_to = null) {
        try {
            $this->db->beginTransaction();
            
            // Complete current step
            $stmt = $this->db->prepare("
                UPDATE booking_process_tracking 
                SET status = 'completed', 
                    completed_at = NOW(), 
                    notes = ?,
                    attachments = ?,
                    assigned_to = ?
                WHERE booking_id = ? AND process_step = ?
            ");
            $stmt->execute([
                $notes,
                json_encode($attachments),
                $assigned_to,
                $booking_id,
                $current_step
            ]);
            
            // Start next step if exists
            $next_step = $current_step + 1;
            $stmt = $this->db->prepare("
                UPDATE booking_process_tracking 
                SET status = 'in_progress',
                    started_at = NOW(),
                    assigned_to = ?
                WHERE booking_id = ? AND process_step = ?
            ");
            $stmt->execute([$assigned_to, $booking_id, $next_step]);
            
            // Update booking status based on step
            $new_booking_status = $this->getBookingStatusFromStep($next_step);
            if ($new_booking_status) {
                $stmt = $this->db->prepare("
                    UPDATE facility_bookings 
                    SET status = ?, current_process_step = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$new_booking_status, $next_step, $booking_id]);
            }
            
            // Log activity
            log_activity($assigned_to ?? 0, 'process_advanced', "Booking $booking_id advanced to step $next_step");
            
            // Send notification
            $this->sendProcessNotification($booking_id, $next_step, 'step_advanced');
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Proses berhasil dilanjutkan ke tahap berikutnya',
                'next_step' => $next_step
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Advance booking step error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Gagal melanjutkan proses'];
        }
    }
    
    /**
     * Update specific step dengan notes dan attachments
     */
    public function updateProcessStep($booking_id, $step_number, $status, $notes = '', $attachments = [], $assigned_to = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE booking_process_tracking 
                SET status = ?,
                    notes = ?,
                    attachments = ?,
                    assigned_to = ?,
                    " . ($status === 'in_progress' ? 'started_at = NOW()' : '') . "
                    " . ($status === 'completed' ? 'completed_at = NOW()' : '') . "
                WHERE booking_id = ? AND process_step = ?
            ");
            
            $result = $stmt->execute([
                $status,
                $notes,
                json_encode($attachments),
                $assigned_to,
                $booking_id,
                $step_number
            ]);
            
            if ($result) {
                // Log activity
                log_activity($assigned_to ?? 0, 'process_updated', "Step $step_number updated for booking $booking_id");
                
                // Send notification if status changed
                $this->sendProcessNotification($booking_id, $step_number, 'step_updated');
                
                return ['success' => true, 'message' => 'Status tahap berhasil diperbarui'];
            }
            
            return ['success' => false, 'error' => 'Gagal memperbarui status tahap'];
            
        } catch (Exception $e) {
            error_log("Update process step error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Terjadi kesalahan sistem'];
        }
    }
    
    /**
     * Get process steps template berdasarkan process type
     */
    public function getProcessStepsTemplate($process_type) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM business_process_steps 
                WHERE process_type = ? 
                ORDER BY step_number
            ");
            $stmt->execute([$process_type]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get process template error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get analytics data untuk process performance
     */
    public function getProcessAnalytics($date_from = null, $date_to = null) {
        try {
            $where_clause = '';
            $params = [];
            
            if ($date_from && $date_to) {
                $where_clause = 'WHERE fb.created_at BETWEEN ? AND ?';
                $params = [$date_from, $date_to];
            }
            
            // Average completion time per step
            $stmt = $this->db->prepare("
                SELECT 
                    bpt.process_step,
                    bps.step_name,
                    COUNT(*) as total_processed,
                    AVG(TIMESTAMPDIFF(HOUR, bpt.started_at, bpt.completed_at)) as avg_completion_hours,
                    MIN(TIMESTAMPDIFF(HOUR, bpt.started_at, bpt.completed_at)) as min_completion_hours,
                    MAX(TIMESTAMPDIFF(HOUR, bpt.started_at, bpt.completed_at)) as max_completion_hours
                FROM booking_process_tracking bpt
                JOIN business_process_steps bps ON bpt.process_step = bps.step_number
                JOIN facility_bookings fb ON bpt.booking_id = fb.id
                $where_clause
                AND bpt.status = 'completed'
                AND bpt.started_at IS NOT NULL 
                AND bpt.completed_at IS NOT NULL
                GROUP BY bpt.process_step, bps.step_name
                ORDER BY bpt.process_step
            ");
            $stmt->execute($params);
            $step_analytics = $stmt->fetchAll();
            
            // Overall process performance
            $stmt = $this->db->prepare("
                SELECT 
                    fb.process_type,
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN fb.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN fb.status IN ('submitted', 'verified', 'scheduled', 'in_progress', 'testing', 'reporting', 'payment_pending') THEN 1 ELSE 0 END) as active_bookings,
                    AVG(TIMESTAMPDIFF(DAY, fb.created_at, 
                        CASE WHEN fb.status = 'completed' THEN fb.updated_at ELSE NULL END
                    )) as avg_completion_days
                FROM facility_bookings fb
                $where_clause
                GROUP BY fb.process_type
            ");
            $stmt->execute($params);
            $overall_analytics = $stmt->fetchAll();
            
            // Bottleneck analysis
            $stmt = $this->db->prepare("
                SELECT 
                    bpt.process_step,
                    bps.step_name,
                    COUNT(*) as stuck_count,
                    AVG(TIMESTAMPDIFF(HOUR, bpt.started_at, NOW())) as avg_stuck_hours
                FROM booking_process_tracking bpt
                JOIN business_process_steps bps ON bpt.process_step = bps.step_number
                JOIN facility_bookings fb ON bpt.booking_id = fb.id
                $where_clause
                AND bpt.status = 'in_progress'
                AND bpt.started_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY bpt.process_step, bps.step_name
                ORDER BY stuck_count DESC
            ");
            $stmt->execute($params);
            $bottlenecks = $stmt->fetchAll();
            
            return [
                'step_analytics' => $step_analytics,
                'overall_analytics' => $overall_analytics,
                'bottlenecks' => $bottlenecks
            ];
            
        } catch (Exception $e) {
            error_log("Get process analytics error: " . $e->getMessage());
            return ['step_analytics' => [], 'overall_analytics' => [], 'bottlenecks' => []];
        }
    }
    
    /**
     * Get active bookings yang perlu attention
     */
    public function getActiveBookingsForReview($assigned_to = null, $limit = 20) {
        try {
            $where_clause = "WHERE fb.status NOT IN ('completed', 'cancelled')";
            $params = [];
            
            if ($assigned_to) {
                $where_clause .= " AND bpt.assigned_to = ?";
                $params[] = $assigned_to;
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    fb.id,
                    fb.booking_code,
                    fb.facility_requested,
                    fb.booking_date,
                    fb.status,
                    fb.current_process_step,
                    fb.priority,
                    u.full_name as user_name,
                    sc.category_name,
                    bpt.step_name as current_step_name,
                    bpt.started_at,
                    TIMESTAMPDIFF(HOUR, bpt.started_at, NOW()) as hours_in_step,
                    bps.timeline_days
                FROM facility_bookings fb
                JOIN users u ON fb.user_id = u.id
                JOIN service_categories sc ON fb.service_category_id = sc.id
                LEFT JOIN booking_process_tracking bpt ON fb.id = bpt.booking_id AND fb.current_process_step = bpt.process_step
                LEFT JOIN business_process_steps bps ON bpt.process_step = bps.step_number AND bps.process_type = fb.process_type
                $where_clause
                ORDER BY 
                    CASE fb.priority 
                        WHEN 'emergency' THEN 1 
                        WHEN 'urgent' THEN 2 
                        ELSE 3 
                    END,
                    hours_in_step DESC
                LIMIT ?
            ");
            
            $params[] = $limit;
            $stmt->execute($params);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Get active bookings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate process timeline untuk booking
     */
    public function generateProcessTimeline($booking_id) {
        try {
            $tracking = $this->getBookingProcessTracking($booking_id);
            $timeline = [];
            
            foreach ($tracking['steps'] as $step) {
                $timeline_item = [
                    'step' => $step['process_step'],
                    'title' => $step['step_name'],
                    'description' => $step['step_description'],
                    'status' => $step['status'],
                    'started_at' => $step['started_at'],
                    'completed_at' => $step['completed_at'],
                    'assigned_to' => $step['assigned_name'],
                    'notes' => $step['notes'],
                    'attachments' => json_decode($step['attachments'] ?? '[]', true),
                    'actor' => $step['actor'],
                    'input_required' => $step['input_required'],
                    'output_generated' => $step['output_generated'],
                    'timeline_days' => $step['timeline_days']
                ];
                
                // Calculate duration if both dates available
                if ($step['started_at'] && $step['completed_at']) {
                    $start = new DateTime($step['started_at']);
                    $end = new DateTime($step['completed_at']);
                    $timeline_item['duration_hours'] = $end->diff($start)->days * 24 + $end->diff($start)->h;
                } elseif ($step['started_at'] && $step['status'] === 'in_progress') {
                    $start = new DateTime($step['started_at']);
                    $now = new DateTime();
                    $timeline_item['duration_hours'] = $now->diff($start)->days * 24 + $now->diff($start)->h;
                }
                
                $timeline[] = $timeline_item;
            }
            
            return $timeline;
            
        } catch (Exception $e) {
            error_log("Generate timeline error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Map booking status ke process step
     */
    private function getBookingStatusFromStep($step_number) {
        $status_map = [
            1 => 'submitted',
            2 => 'verified',
            3 => 'scheduled',
            4 => 'in_progress',
            5 => 'testing',
            6 => 'reporting',
            7 => 'payment_pending',
            8 => 'completed'
        ];
        
        return $status_map[$step_number] ?? null;
    }
    
    /**
     * Send process notification
     */
    private function sendProcessNotification($booking_id, $step_number, $type) {
        try {
            // Get booking and user details
            $stmt = $this->db->prepare("
                SELECT fb.*, u.email, u.full_name, bps.step_name
                FROM facility_bookings fb
                JOIN users u ON fb.user_id = u.id
                LEFT JOIN business_process_steps bps ON bps.step_number = ? AND bps.process_type = fb.process_type
                WHERE fb.id = ?
            ");
            $stmt->execute([$step_number, $booking_id]);
            $data = $stmt->fetch();
            
            if (!$data) return;
            
            // Prepare notification content
            $subject = '';
            $message = '';
            
            switch ($type) {
                case 'step_advanced':
                    $subject = "Update Proses Booking - {$data['booking_code']}";
                    $message = "Booking Anda telah masuk ke tahap: {$data['step_name']}";
                    break;
                case 'step_updated':
                    $subject = "Pembaruan Status - {$data['booking_code']}";
                    $message = "Status tahap '{$data['step_name']}' telah diperbarui";
                    break;
            }
            
            // Log notification (implement actual email sending here)
            error_log("Process notification: $subject to {$data['email']}");
            
        } catch (Exception $e) {
            error_log("Send process notification error: " . $e->getMessage());
        }
    }
    
    /**
     * Get process KPI metrics
     */
    public function getProcessKPIs($period = '30 days') {
        try {
            $date_filter = "DATE_SUB(NOW(), INTERVAL $period)";
            
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN fb.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    AVG(CASE WHEN fb.status = 'completed' 
                        THEN TIMESTAMPDIFF(DAY, fb.created_at, fb.updated_at) 
                        ELSE NULL END) as avg_completion_days,
                    SUM(CASE WHEN fb.priority = 'emergency' THEN 1 ELSE 0 END) as emergency_requests,
                    SUM(CASE WHEN fb.priority = 'urgent' THEN 1 ELSE 0 END) as urgent_requests,
                    AVG(CASE WHEN fb.status = 'completed' 
                        THEN fb.current_process_step 
                        ELSE NULL END) as avg_steps_to_complete
                FROM facility_bookings fb
                WHERE fb.created_at >= $date_filter
            ");
            $stmt->execute();
            $kpis = $stmt->fetch();
            
            // Calculate completion rate
            $kpis['completion_rate'] = $kpis['total_bookings'] > 0 
                ? round(($kpis['completed_bookings'] / $kpis['total_bookings']) * 100, 2) 
                : 0;
            
            // SLA compliance (assuming 3 days SLA)
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as sla_total,
                    SUM(CASE WHEN TIMESTAMPDIFF(DAY, created_at, 
                        CASE WHEN status = 'completed' THEN updated_at ELSE NOW() END
                    ) <= 3 THEN 1 ELSE 0 END) as sla_compliant
                FROM facility_bookings
                WHERE created_at >= $date_filter
                AND status != 'cancelled'
            ");
            $stmt->execute();
            $sla_data = $stmt->fetch();
            
            $kpis['sla_compliance'] = $sla_data['sla_total'] > 0 
                ? round(($sla_data['sla_compliant'] / $sla_data['sla_total']) * 100, 2) 
                : 0;
            
            return $kpis;
            
        } catch (Exception $e) {
            error_log("Get process KPIs error: " . $e->getMessage());
            return [];
        }
    }
}
?>