<?php
/**
 * File Upload Security Class - iLab UNMUL
 * Comprehensive security measures untuk file upload
 */

class FileUploadSecurity {
    private $max_size;
    private $allowed_extensions;
    private $allowed_mime_types;
    private $upload_path;
    private $scan_for_malware;
    
    public function __construct() {
        $this->max_size = $_ENV['MAX_UPLOAD_SIZE'] ?? 10485760; // 10MB
        $this->allowed_extensions = explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'pdf,doc,docx,jpg,jpeg,png,txt');
        $this->upload_path = realpath(__DIR__ . '/../../public/uploads/');
        $this->scan_for_malware = $_ENV['ENABLE_MALWARE_SCAN'] ?? false;
        
        $this->allowed_mime_types = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'txt' => 'text/plain',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
    }
    
    /**
     * Validate and process file upload
     */
    public function processUpload($file, $destination_folder = 'general', $custom_name = null) {
        try {
            // Basic validation
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return ['success' => false, 'error' => $validation['error']];
            }
            
            // Create destination directory
            $upload_dir = $this->upload_path . '/' . $destination_folder . '/';
            if (!$this->createSecureDirectory($upload_dir)) {
                return ['success' => false, 'error' => 'Failed to create upload directory'];
            }
            
            // Generate safe filename
            $safe_filename = $this->generateSafeFilename($file['name'], $custom_name);
            $full_path = $upload_dir . $safe_filename;
            
            // Check for duplicate files
            if (file_exists($full_path)) {
                $safe_filename = $this->generateUniqueFilename($upload_dir, $safe_filename);
                $full_path = $upload_dir . $safe_filename;
            }
            
            // Advanced security checks
            $security_check = $this->performSecurityChecks($file['tmp_name'], $file['name']);
            if (!$security_check['safe']) {
                return ['success' => false, 'error' => $security_check['reason']];
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $full_path)) {
                return ['success' => false, 'error' => 'Failed to move uploaded file'];
            }
            
            // Set secure file permissions
            chmod($full_path, 0644);
            
            // Log upload activity
            $this->logUploadActivity($safe_filename, $file['size'], $_SESSION['user_id'] ?? null);
            
            return [
                'success' => true,
                'filename' => $safe_filename,
                'path' => $destination_folder . '/' . $safe_filename,
                'size' => $file['size'],
                'mime_type' => $file['type']
            ];
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            return ['success' => false, 'error' => 'File upload failed'];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => $this->getUploadErrorMessage($file['error'])];
        }
        
        // Check file size
        if ($file['size'] > $this->max_size) {
            return ['valid' => false, 'error' => 'File size exceeds maximum allowed size of ' . $this->formatFileSize($this->max_size)];
        }
        
        if ($file['size'] == 0) {
            return ['valid' => false, 'error' => 'File is empty'];
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowed_extensions)) {
            return ['valid' => false, 'error' => 'File type not allowed. Allowed types: ' . implode(', ', $this->allowed_extensions)];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!isset($this->allowed_mime_types[$extension]) || 
            $this->allowed_mime_types[$extension] !== $mime_type) {
            return ['valid' => false, 'error' => 'File type mismatch. File may be corrupted or renamed.'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Perform advanced security checks
     */
    private function performSecurityChecks($tmp_path, $original_name) {
        try {
            // Check for executable content in file headers
            $file_content = file_get_contents($tmp_path, false, null, 0, 1024);
            
            // Check for script tags and potentially dangerous content
            $dangerous_patterns = [
                '/<\?php/i',
                '/<script/i',
                '/javascript:/i',
                '/vbscript:/i',
                '/onload=/i',
                '/onerror=/i',
                '/eval\(/i',
                '/exec\(/i',
                '/system\(/i',
                '/shell_exec\(/i',
                '/passthru\(/i',
                '/base64_decode\(/i'
            ];
            
            foreach ($dangerous_patterns as $pattern) {
                if (preg_match($pattern, $file_content)) {
                    return ['safe' => false, 'reason' => 'File contains potentially dangerous content'];
                }
            }
            
            // Check file signature (magic bytes)
            if (!$this->validateFileSignature($tmp_path, $original_name)) {
                return ['safe' => false, 'reason' => 'File signature does not match extension'];
            }
            
            // Scan for malware if enabled
            if ($this->scan_for_malware) {
                if (!$this->scanForMalware($tmp_path)) {
                    return ['safe' => false, 'reason' => 'File failed malware scan'];
                }
            }
            
            return ['safe' => true];
            
        } catch (Exception $e) {
            error_log("Security check error: " . $e->getMessage());
            return ['safe' => false, 'reason' => 'Security check failed'];
        }
    }
    
    /**
     * Validate file signature (magic bytes)
     */
    private function validateFileSignature($file_path, $filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_handle = fopen($file_path, 'rb');
        
        if (!$file_handle) {
            return false;
        }
        
        $header = fread($file_handle, 20);
        fclose($file_handle);
        
        $signatures = [
            'pdf' => ['%PDF'],
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47"],
            'doc' => ["\xD0\xCF\x11\xE0"],
            'docx' => ["\x50\x4B\x03\x04"],
            'xlsx' => ["\x50\x4B\x03\x04"],
            'pptx' => ["\x50\x4B\x03\x04"],
            'txt' => null // Text files don't have consistent signatures
        ];
        
        if ($extension === 'txt') {
            return true; // Skip signature check for text files
        }
        
        if (!isset($signatures[$extension])) {
            return false;
        }
        
        foreach ($signatures[$extension] as $signature) {
            if (strpos($header, $signature) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate safe filename
     */
    private function generateSafeFilename($original_name, $custom_name = null) {
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        if ($custom_name) {
            $base_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $custom_name);
        } else {
            $base_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
        }
        
        // Limit filename length
        $base_name = substr($base_name, 0, 50);
        
        // Add timestamp for uniqueness
        $timestamp = date('Y-m-d_H-i-s');
        
        return $base_name . '_' . $timestamp . '.' . $extension;
    }
    
    /**
     * Generate unique filename if file already exists
     */
    private function generateUniqueFilename($directory, $filename) {
        $base_name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $counter = 1;
        
        while (file_exists($directory . $filename)) {
            $filename = $base_name . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $filename;
    }
    
    /**
     * Create secure directory with proper permissions
     */
    private function createSecureDirectory($path) {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                return false;
            }
            
            // Create .htaccess to prevent direct access to uploaded files
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "Options -ExecCGI\n";
            $htaccess_content .= "AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi\n";
            $htaccess_content .= "<FilesMatch \"\\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$\">\n";
            $htaccess_content .= "    Order allow,deny\n";
            $htaccess_content .= "    Deny from all\n";
            $htaccess_content .= "</FilesMatch>\n";
            
            file_put_contents($path . '.htaccess', $htaccess_content);
        }
        
        return true;
    }
    
    /**
     * Scan file for malware (placeholder for external scanner integration)
     */
    private function scanForMalware($file_path) {
        // This is a placeholder - integrate with ClamAV or similar
        // For now, return true (no malware detected)
        return true;
        
        // Example ClamAV integration:
        /*
        $output = shell_exec("clamscan --no-summary " . escapeshellarg($file_path));
        return strpos($output, 'FOUND') === false;
        */
    }
    
    /**
     * Log upload activity
     */
    private function logUploadActivity($filename, $file_size, $user_id) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO file_uploads (
                    user_id, filename, file_size, upload_time, ip_address
                ) VALUES (?, ?, ?, NOW(), ?)
            ");
            
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->execute([$user_id, $filename, $file_size, $ip_address]);
            
        } catch (Exception $e) {
            error_log("Upload logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Delete uploaded file securely
     */
    public function deleteFile($file_path) {
        $full_path = $this->upload_path . '/' . $file_path;
        
        if (!file_exists($full_path)) {
            return ['success' => false, 'error' => 'File not found'];
        }
        
        // Security check - ensure file is within upload directory
        $real_path = realpath($full_path);
        if (strpos($real_path, $this->upload_path) !== 0) {
            return ['success' => false, 'error' => 'Invalid file path'];
        }
        
        if (unlink($full_path)) {
            // Log deletion
            log_activity($_SESSION['user_id'] ?? null, 'file_deleted', "File deleted: $file_path");
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Failed to delete file'];
        }
    }
    
    /**
     * Get secure download URL
     */
    public function getSecureDownloadUrl($file_path, $booking_id = null) {
        // Generate token for secure download
        $token = hash('sha256', $file_path . time() . ($_SESSION['user_id'] ?? ''));
        
        // Store token temporarily (could use cache or database)
        $_SESSION['download_tokens'][$token] = [
            'file_path' => $file_path,
            'booking_id' => $booking_id,
            'expires' => time() + 3600 // 1 hour
        ];
        
        return 'api/secure-download.php?token=' . $token;
    }
    
    /**
     * Utility methods
     */
    private function getUploadErrorMessage($error_code) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        return $messages[$error_code] ?? 'Unknown upload error';
    }
    
    private function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Clean up expired download tokens
     */
    public function cleanupExpiredTokens() {
        if (isset($_SESSION['download_tokens'])) {
            $current_time = time();
            foreach ($_SESSION['download_tokens'] as $token => $data) {
                if ($data['expires'] < $current_time) {
                    unset($_SESSION['download_tokens'][$token]);
                }
            }
        }
    }
    
    /**
     * Get file information safely
     */
    public function getFileInfo($file_path) {
        $full_path = $this->upload_path . '/' . $file_path;
        
        if (!file_exists($full_path)) {
            return null;
        }
        
        // Security check
        $real_path = realpath($full_path);
        if (strpos($real_path, $this->upload_path) !== 0) {
            return null;
        }
        
        return [
            'name' => basename($file_path),
            'size' => filesize($full_path),
            'modified' => filemtime($full_path),
            'mime_type' => mime_content_type($full_path),
            'path' => $file_path
        ];
    }
}
?>