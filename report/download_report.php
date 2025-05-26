<?php
session_start();
include "../config/session_check.php";
include "../config/config.php";

// Check if report ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid report ID');
}

$reportId = intval($_GET['id']);

// Get report details from database
$sql = "SELECT id, file_path, report_name, report_type, parameters, generated_by 
        FROM reports 
        WHERE id = ? AND generated_by = ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "ii", $reportId, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    http_response_code(404);
    die('Report not found or access denied');
}

// Check if file exists
if (!file_exists($row['file_path'])) {
    http_response_code(404);
    die('Report file not found on server');
}

// Get file information
$filePath = $row['file_path'];
$fileName = $row['report_name'];
$exportFormat = $row['export_format'] ?: 'pdf'; // Default to PDF if not set

// Parse parameters to get export format if available
if ($row['parameters']) {
    $parameters = json_decode($row['parameters'], true);
    if (isset($parameters['export_format'])) {
        $exportFormat = $parameters['export_format'];
    }
}

// Determine file extension and MIME type
$fileExtension = '';
$mimeType = '';

switch (strtolower($exportFormat)) {
    case 'html':
        $fileExtension = '.html';
        $mimeType = 'application/html';
        break;
    case 'excel':
        $fileExtension = '.csv';
        $mimeType = 'text/csv';
        break;
    case 'csv':
        $fileExtension = '.csv';
        $mimeType = 'text/csv';
        break;
    default:
        $fileExtension = '.html';
        $mimeType = 'application/html';
}

// Clean filename and add extension if not present
$downloadFileName = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $fileName);
$downloadFileName = trim($downloadFileName);

// Add extension if not already present
if (!preg_match('/\.' . preg_quote(trim($fileExtension, '.'), '/') . '$/i', $downloadFileName)) {
    $downloadFileName .= $fileExtension;
}

/* // Log the download attempt
$logSql = "INSERT INTO download_logs (report_id, user_id, download_time, ip_address) 
           VALUES (?, ?, NOW(), ?)";
$logStmt = mysqli_prepare($connect, $logSql);
$userIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
mysqli_stmt_bind_param($logStmt, "iis", $reportId, $_SESSION['user_id'], $userIP);
mysqli_stmt_execute($logStmt); */

// Get file size
$fileSize = filesize($filePath);

// Clear any output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Set headers for file download
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Prevent execution time limit for large files
set_time_limit(0);

// Read and output file in chunks to handle large files
$chunkSize = 8192; // 8KB chunks
$handle = fopen($filePath, 'rb');

if ($handle === false) {
    http_response_code(500);
    die('Error reading file');
}

while (!feof($handle)) {
    $chunk = fread($handle, $chunkSize);
    echo $chunk;
    
    // Flush output to browser
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

fclose($handle);

/* // Update download count in reports table
$updateSql = "UPDATE reports SET download_count = COALESCE(download_count, 0) + 1, last_downloaded = NOW() WHERE id = ?";
$updateStmt = mysqli_prepare($connect, $updateSql);
mysqli_stmt_bind_param($updateStmt, "i", $reportId);
mysqli_stmt_execute($updateStmt); */

exit();
?>