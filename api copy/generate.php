<?php
// api/generate.php

require_once __DIR__ . '/../includes/bootstrap.php';

use App\AddressGenerator;
use App\LocaleRegistry;

header('Content-Type: application/json');

try {
    $locale = trim($_GET['locale'] ?? 'en_US');
    $gender = strtolower(trim($_GET['gender'] ?? 'random'));

    if (!LocaleRegistry::get($locale)) $locale = 'en_US';
    if (!in_array($gender, ['male','female','random'])) $gender = 'random';

    $address = AddressGenerator::generate($locale, $gender);
    AddressGenerator::log($address);

    echo json_encode(['success' => true, 'data' => $address]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Generation failed']);
}
