<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\GeneratedImage;

echo "Checking GeneratedImage records for framed_image column\n";
echo "=====================================================\n\n";

try {
    // Get the latest 10 records
    $images = GeneratedImage::latest()->take(10)->get();
    
    echo "Found " . $images->count() . " records:\n\n";
    
    foreach ($images as $image) {
        echo "ID: " . $image->id . "\n";
        echo "Phone: " . $image->phone_number . "\n";
        echo "Framed Image: " . ($image->framed_image ?? 'NULL') . "\n";
        echo "Photo Frame Path: " . ($image->photo_frame_path ?? 'NULL') . "\n";
        echo "Created: " . $image->created_at . "\n";
        echo "---\n";
    }
    
    // Check if any records have framed_image
    $withFramed = GeneratedImage::whereNotNull('framed_image')->count();
    echo "\nRecords with framed_image: " . $withFramed . "\n";
    
    // Check if any records have photo_frame_path
    $withPhotoFrame = GeneratedImage::whereNotNull('photo_frame_path')->count();
    echo "Records with photo_frame_path: " . $withPhotoFrame . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
