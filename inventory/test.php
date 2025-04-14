<?php
// Nothing but pure JSON output
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Test successful']);
?>