<?php
/**
 * SOP Manager Class untuk ILab UNMUL
 * Handles 11 SOP categories dengan search, filter, dan download functionality
 */

class SOPManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get all SOP categories dengan document count
     */
    public function getSOPCategories() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sc.*,
                    COUNT(sd.id) as document_count,
                    COUNT(CASE WHEN sd.is_active = 1 THEN sd.id END) as active_documents
                FROM sop_categories sc
                LEFT JOIN sop_documents sd ON sc.id = sd.category_id
                GROUP BY sc.id
                ORDER BY sc.category_name
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get SOP categories error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search SOPs dengan advanced filtering
     */
    public function searchSOPs($search_term = '', $category_id = null, $safety_level = null, $limit = 20, $offset = 0) {
        try {
            $where_conditions = ['sd.is_active = 1'];
            $params = [];
            
            if (!empty($search_term)) {
                $where_conditions[] = "(sd.title LIKE ? OR sd.content_summary LIKE ? OR sc.category_name LIKE ?)";
                $search_param = '%' . $search_term . '%';
                $params[] = $search_param;
                $params[] = $search_param;
                $params[] = $search_param;
            }
            
            if ($category_id) {
                $where_conditions[] = "sd.category_id = ?";
                $params[] = $category_id;
            }
            
            if ($safety_level) {
                $where_conditions[] = "sc.safety_level = ?";
                $params[] = $safety_level;
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            // Get total count for pagination
            $count_stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM sop_documents sd
                JOIN sop_categories sc ON sd.category_id = sc.id
                WHERE $where_clause
            ");
            $count_stmt->execute($params);
            $total_count = $count_stmt->fetch()['total'];
            
            // Get documents
            $stmt = $this->db->prepare("
                SELECT 
                    sd.*,
                    sc.category_name,
                    sc.safety_level,
                    sc.description as category_description
                FROM sop_documents sd
                JOIN sop_categories sc ON sd.category_id = sc.id
                WHERE $where_clause
                ORDER BY sd.sop_code, sd.title
                LIMIT ? OFFSET ?
            ");
            
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $documents = $stmt->fetchAll();
            
            return [
                'documents' => $documents,
                'total' => $total_count,
                'has_more' => ($offset + $limit) < $total_count
            ];
            
        } catch (Exception $e) {
            error_log("Search SOPs error: " . $e->getMessage());
            return ['documents' => [], 'total' => 0, 'has_more' => false];
        }
    }
    
    /**
     * Get SOP by ID dengan detail lengkap
     */
    public function getSOPById($sop_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sd.*,
                    sc.category_name,
                    sc.safety_level,
                    sc.description as category_description
                FROM sop_documents sd
                JOIN sop_categories sc ON sd.category_id = sc.id
                WHERE sd.id = ? AND sd.is_active = 1
            ");
            $stmt->execute([$sop_id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get SOP by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get SOP by code
     */
    public function getSOPByCode($sop_code) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sd.*,
                    sc.category_name,
                    sc.safety_level,
                    sc.description as category_description
                FROM sop_documents sd
                JOIN sop_categories sc ON sd.category_id = sc.id
                WHERE sd.sop_code = ? AND sd.is_active = 1
            ");
            $stmt->execute([$sop_code]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get SOP by code error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Record SOP download
     */
    public function recordDownload($sop_id, $user_id = null) {
        try {
            $this->db->beginTransaction();
            
            // Update download count
            $stmt = $this->db->prepare("
                UPDATE sop_documents 
                SET download_count = download_count + 1 
                WHERE id = ?
            ");
            $stmt->execute([$sop_id]);
            
            // Log download activity
            if ($user_id) {
                log_activity($user_id, 'sop_download', "Downloaded SOP ID: $sop_id");
            }
            
            // Record download in separate table for analytics
            $stmt = $this->db->prepare("
                INSERT INTO sop_downloads (sop_id, user_id, ip_address, user_agent, downloaded_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $sop_id,
                $user_id,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Record download error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get popular SOPs
     */
    public function getPopularSOPs($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sd.*,
                    sc.category_name,
                    sc.safety_level
                FROM sop_documents sd
                JOIN sop_categories sc ON sd.category_id = sc.id
                WHERE sd.is_active = 1
                ORDER BY sd.download_count DESC, sd.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get popular SOPs error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent SOPs
     */
    public function getRecentSOPs($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sd.*,
                    sc.category_name,
                    sc.safety_level
                FROM sop_documents sd
                JOIN sop_categories sc ON sd.category_id = sc.id
                WHERE sd.is_active = 1
                ORDER BY sd.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get recent SOPs error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get SOPs by category
     */
    public function getSOPsByCategory($category_id, $limit = 20) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sd.*,
                    sc.category_name,
                    sc.safety_level
                FROM sop_documents sd
                JOIN sop_categories sc ON sd.category_id = sc.id
                WHERE sd.category_id = ? AND sd.is_active = 1
                ORDER BY sd.sop_code, sd.title
                LIMIT ?
            ");
            $stmt->execute([$category_id, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Get SOPs by category error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create/Update SOP document (untuk admin)
     */
    public function saveSOPDocument($data, $sop_id = null) {
        try {
            $this->db->beginTransaction();
            
            if ($sop_id) {
                // Update existing SOP
                $stmt = $this->db->prepare("
                    UPDATE sop_documents 
                    SET title = ?, category_id = ?, version = ?, issued_date = ?, 
                        effective_date = ?, review_date = ?, approved_by = ?, 
                        content_summary = ?, file_path = ?, file_size = ?,
                        equipment_specs = ?, usage_procedure = ?, safety_instructions = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $result = $stmt->execute([
                    $data['title'], $data['category_id'], $data['version'],
                    $data['issued_date'], $data['effective_date'], $data['review_date'],
                    $data['approved_by'], $data['content_summary'], $data['file_path'],
                    $data['file_size'], $data['equipment_specs'], $data['usage_procedure'],
                    $data['safety_instructions'], $sop_id
                ]);
                $document_id = $sop_id;
            } else {
                // Create new SOP
                $stmt = $this->db->prepare("
                    INSERT INTO sop_documents (
                        sop_code, title, category_id, version, issued_date, 
                        effective_date, review_date, approved_by, content_summary, 
                        file_path, file_size, equipment_specs, usage_procedure, 
                        safety_instructions
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([
                    $data['sop_code'], $data['title'], $data['category_id'], 
                    $data['version'], $data['issued_date'], $data['effective_date'],
                    $data['review_date'], $data['approved_by'], $data['content_summary'],
                    $data['file_path'], $data['file_size'], $data['equipment_specs'],
                    $data['usage_procedure'], $data['safety_instructions']
                ]);
                $document_id = $this->db->lastInsertId();
            }
            
            if ($result) {
                $this->db->commit();
                return ['success' => true, 'id' => $document_id];
            } else {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Failed to save SOP document'];
            }
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Save SOP document error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Delete SOP document (soft delete)
     */
    public function deleteSOPDocument($sop_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE sop_documents 
                SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $result = $stmt->execute([$sop_id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'SOP document deleted successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to delete SOP document'];
            }
            
        } catch (Exception $e) {
            error_log("Delete SOP document error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get SOP analytics
     */
    public function getSOPAnalytics($period = '30 days') {
        try {
            $date_filter = "DATE_SUB(NOW(), INTERVAL $period)";
            
            // Download statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_downloads,
                    COUNT(DISTINCT sop_id) as unique_documents,
                    COUNT(DISTINCT user_id) as unique_users
                FROM sop_downloads 
                WHERE downloaded_at >= $date_filter
            ");
            $stmt->execute();
            $download_stats = $stmt->fetch();
            
            // Popular categories
            $stmt = $this->db->prepare("
                SELECT 
                    sc.category_name,
                    COUNT(sd_dl.id) as download_count
                FROM sop_categories sc
                JOIN sop_documents sd ON sc.id = sd.category_id
                LEFT JOIN sop_downloads sd_dl ON sd.id = sd_dl.sop_id 
                    AND sd_dl.downloaded_at >= $date_filter
                GROUP BY sc.id, sc.category_name
                ORDER BY download_count DESC
                LIMIT 5
            ");
            $stmt->execute();
            $popular_categories = $stmt->fetchAll();
            
            // Most downloaded SOPs
            $stmt = $this->db->prepare("
                SELECT 
                    sd.title,
                    sd.sop_code,
                    sc.category_name,
                    COUNT(sd_dl.id) as download_count
                FROM sop_documents sd
                JOIN sop_categories sc ON sd.category_id = sc.id
                LEFT JOIN sop_downloads sd_dl ON sd.id = sd_dl.sop_id 
                    AND sd_dl.downloaded_at >= $date_filter
                WHERE sd.is_active = 1
                GROUP BY sd.id
                ORDER BY download_count DESC
                LIMIT 10
            ");
            $stmt->execute();
            $top_documents = $stmt->fetchAll();
            
            return [
                'download_stats' => $download_stats,
                'popular_categories' => $popular_categories,
                'top_documents' => $top_documents
            ];
            
        } catch (Exception $e) {
            error_log("Get SOP analytics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate unique SOP code
     */
    public function generateSOPCode($category_id) {
        try {
            // Get category prefix
            $stmt = $this->db->prepare("SELECT category_name FROM sop_categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $category = $stmt->fetch();
            
            if (!$category) {
                return false;
            }
            
            // Generate prefix from category name
            $words = explode(' ', $category['category_name']);
            $prefix = '';
            foreach ($words as $word) {
                $prefix .= strtoupper(substr($word, 0, 1));
            }
            $prefix = substr($prefix, 0, 3); // Max 3 characters
            
            // Get next number
            $stmt = $this->db->prepare("
                SELECT MAX(CAST(SUBSTRING(sop_code, 4) AS UNSIGNED)) as max_num
                FROM sop_documents 
                WHERE sop_code LIKE ?
            ");
            $stmt->execute([$prefix . '%']);
            $result = $stmt->fetch();
            
            $next_number = ($result['max_num'] ?? 0) + 1;
            
            return $prefix . str_pad($next_number, 3, '0', STR_PAD_LEFT);
            
        } catch (Exception $e) {
            error_log("Generate SOP code error: " . $e->getMessage());
            return false;
        }
    }
}

// Create SOP downloads table if not exists
try {
    $db = Database::getInstance()->getConnection();
    $db->exec("
        CREATE TABLE IF NOT EXISTS sop_downloads (
            id INT PRIMARY KEY AUTO_INCREMENT,
            sop_id INT NOT NULL,
            user_id INT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_sop_id (sop_id),
            INDEX idx_user_id (user_id),
            INDEX idx_downloaded_at (downloaded_at),
            FOREIGN KEY (sop_id) REFERENCES sop_documents(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
} catch (Exception $e) {
    error_log("Create SOP downloads table error: " . $e->getMessage());
}
?>