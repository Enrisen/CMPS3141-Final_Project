<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$newVisitor = json_decode($input, true);

// Path to pending visitors JSON file
$pendingVisitorsFile = 'json/pendingVisitors.json';

// Read existing pending visitors
$pendingVisitorsJson = file_get_contents($pendingVisitorsFile);
$pendingVisitorsData = json_decode($pendingVisitorsJson, true);

// Add new visitor
$pendingVisitorsData['pendingVisitors'][] = $newVisitor;

// Write updated data back to file
$updatedJson = json_encode($pendingVisitorsData, JSON_PRETTY_PRINT);
if (file_put_contents($pendingVisitorsFile, $updatedJson) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to write visitor data']);
    exit();
}

// Success response
echo json_encode(['success' => true, 'message' => 'Visitor added successfully']);
exit();
