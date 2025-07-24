<?php
/**
 * SOP Download Handler - Website Integrated Laboratory UNMUL
 * Handles secure SOP file downloads dengan tracking
 */

session_start();
require_once '../includes/config/database.php';
require_once '../includes/functions/common.php';
require_once '../includes/classes/SOPManager.php';

// Get SOP ID
$sop_id = intval($_GET['id'] ?? 0);

if (!$sop_id) {
    header('Location: sop.php?error=invalid_id');
    exit;
}

// Initialize SOP Manager
$sopManager = new SOPManager();

// Get SOP details
$sop = $sopManager->getSOPById($sop_id);

if (!$sop) {
    header('Location: sop.php?error=not_found');
    exit;
}

// Check if user is logged in (optional requirement)
$user_id = $_SESSION['user_id'] ?? null;

// Record download
$sopManager->recordDownload($sop_id, $user_id);

// File path (you would need to implement actual file storage)
$file_path = '../uploads/sop/' . $sop['file_path'];

// Check if file exists
if (!file_exists($file_path)) {
    // For demo purposes, generate a sample PDF content
    $pdf_content = generateSamplePDF($sop);
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $sop['sop_code'] . '_' . sanitizeFilename($sop['title']) . '.pdf"');
    header('Content-Length: ' . strlen($pdf_content));
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    
    // Output PDF content
    echo $pdf_content;
    exit;
} else {
    // Serve actual file
    $file_size = filesize($file_path);
    $file_name = $sop['sop_code'] . '_' . sanitizeFilename($sop['title']) . '.pdf';
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Length: ' . $file_size);
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file_path)) . ' GMT');
    
    // Output file
    readfile($file_path);
    exit;
}

function sanitizeFilename($filename) {
    // Remove special characters and spaces
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return substr($filename, 0, 100); // Limit length
}

function generateSamplePDF($sop) {
    // This is a simplified PDF generation for demo purposes
    // In production, you would use a proper PDF library like TCPDF or FPDF
    
    $content = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Resources <<
/Font <<
/F1 4 0 R
>>
>>
/Contents 5 0 R
>>
endobj

4 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj

5 0 obj
<<
/Length 200
>>
stream
BT
/F1 16 Tf
50 750 Td
(STANDARD OPERATING PROCEDURE) Tj
0 -30 Td
/F1 14 Tf
(Code: ' . $sop['sop_code'] . ') Tj
0 -25 Td
(Title: ' . substr($sop['title'], 0, 50) . ') Tj
0 -25 Td
(Category: ' . $sop['category_name'] . ') Tj
0 -25 Td
(Version: ' . $sop['version'] . ') Tj
0 -40 Td
/F1 12 Tf
(This is a sample SOP document.) Tj
0 -20 Td
(Please contact ILab UNMUL for the complete document.) Tj
ET
endstream
endobj

xref
0 6
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000274 00000 n 
0000000351 00000 n 
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
604
%%EOF';

    return $content;
}
?>