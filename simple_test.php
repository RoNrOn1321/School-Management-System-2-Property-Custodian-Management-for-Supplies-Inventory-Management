<?php
header('Content-Type: application/json');

// Simple test - return mock data to see if the frontend works
$mockAssets = [
    [
        'id' => 1,
        'asset_code' => 'TEST-001',
        'name' => 'Test Asset',
        'description' => 'Test Description',
        'category_name' => 'Test Category',
        'status' => 'available',
        'location' => 'Test Location',
        'current_value' => '1000.00',
        'qr_generated' => false,
        'tags' => []
    ]
];

echo json_encode($mockAssets);
?>