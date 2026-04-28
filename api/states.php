<?php
// api/states.php — Returns available states/regions for a country

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

$country = strtolower(trim($_GET['country'] ?? 'us'));
$states  = AddressGenerator::states($country);

echo json_encode(['states' => $states]);
