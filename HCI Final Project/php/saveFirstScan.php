<?php 
// saveFirstScan.php
header('Content-Type: application/json');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $qrCodeId = $data['visitor']['QR Code ID'];
    $currentTime = $data['visitor']['Time QR Scanned In'];

    // Read pendingVisitors.json
    $pendingFile = '../json/pendingVisitors.json';
    $pendingData = json_decode(file_get_contents($pendingFile), true);

    // Find and update the visitor with scan time
    foreach ($pendingData['pendingVisitors'] as &$visitor) {
        if ($visitor['QR Code ID'] === $qrCodeId) {
            $visitor['Time QR Scanned In'] = $currentTime;
            break;
        }
    }

    // Save updated pendingVisitors.json
    $success = file_put_contents($pendingFile, json_encode($pendingData, JSON_PRETTY_PRINT));

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to write to file']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
