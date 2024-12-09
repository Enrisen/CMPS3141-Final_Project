<?php
// saveCheckout.php
header('Content-Type: application/json');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $qrCodeId = $data['visitor']['QR Code ID'];
    $leaveTime = $data['visitor']['Leave Time'];

    // Read both files
    $pendingFile = '../json/pendingVisitors.json';
    $scannedFile = '../json/scannedVisitors.json';

    $pendingData = json_decode(file_get_contents($pendingFile), true);
    $scannedData = file_exists($scannedFile) 
        ? json_decode(file_get_contents($scannedFile), true)
        : ['scannedVisitors' => []];

    // Find the visitor to move
    $visitorToMove = null;
    $pendingData['pendingVisitors'] = array_filter(
        $pendingData['pendingVisitors'],
        function($visitor) use ($qrCodeId, $leaveTime, &$visitorToMove) {
            if ($visitor['QR Code ID'] === $qrCodeId) {
                $visitor['Leave Time'] = $leaveTime;
                $visitorToMove = $visitor;
                return false;
            }
            return true;
        }
    );

    // Add to scannedVisitors if found
    if ($visitorToMove) {
        $scannedData['scannedVisitors'][] = $visitorToMove;
    }

    // Save both files
    $success = file_put_contents($pendingFile, json_encode($pendingData, JSON_PRETTY_PRINT)) &&
               file_put_contents($scannedFile, json_encode($scannedData, JSON_PRETTY_PRINT));

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to write to files']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}