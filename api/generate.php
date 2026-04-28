<?php
// api/generate.php — AJAX endpoint for address generation

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $country = strtolower(trim($_GET['country'] ?? 'us'));
    $gender  = strtolower(trim($_GET['gender']  ?? 'random'));
    $state   = trim($_GET['state'] ?? '');

    // Validate gender
    if (!in_array($gender, ['male', 'female', 'random'], true)) {
        $gender = 'random';
    }

    $address = AddressGenerator::generate($country, $gender, $state ?: null);

    // Log asynchronously (ignore failures)
    AddressGenerator::log($address);

    echo json_encode(['success' => true, 'data' => $address]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Generation failed']);
}
