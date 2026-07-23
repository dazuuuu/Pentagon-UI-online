<?php
// api/packages.php
require_once 'config.php';

// Mock packages data for Pentagon Quest
$packages = [
    [
        "id" => 1,
        "title" => "Golden Savannah Safari",
        "description" => "Experience the vast savannah under the golden sunset.",
        "price" => 1200,
        "duration" => "5 Days",
        "location" => "Maasai Mara"
    ],
    [
        "id" => 2,
        "title" => "Tropical Emerald Retreat",
        "description" => "A lush green rainforest expedition for nature lovers.",
        "price" => 850,
        "duration" => "3 Days",
        "location" => "Rwanda"
    ],
    [
        "id" => 3,
        "title" => "Black Sand Coastal Escape",
        "description" => "Discover the rare black sand beaches and pure white waves.",
        "price" => 1500,
        "duration" => "7 Days",
        "location" => "Diani Coast"
    ]
];

// In the future, this would be a DB query like:
// $stmt = $pdo->query("SELECT * FROM packages");
// $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => "success",
    "data" => $packages
]);
?>
