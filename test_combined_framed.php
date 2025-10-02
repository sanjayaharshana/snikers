<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\SnickersController;
use Illuminate\Support\Facades\Storage;

echo "Testing createCombinedFramedImage method\n";
echo "======================================\n\n";

try {
    // Create controller instance
    $controller = new SnickersController();
    
    // Use reflection to access private methods
    $reflection = new ReflectionClass($controller);
    
    // Test with existing images
    $sadImagePath = 'generated/sad_1759358646_qJffTq6Ycc.jpg';
    $happyImagePath = 'generated/happy_1759358646_8phpFt3srB.jpg';
    
    echo "1. Testing with existing images:\n";
    echo "   Sad image: $sadImagePath\n";
    echo "   Happy image: $happyImagePath\n";
    
    // Check if images exist
    if (!Storage::disk('public')->exists($sadImagePath)) {
        echo "   ✗ Sad image not found\n";
        exit(1);
    }
    if (!Storage::disk('public')->exists($happyImagePath)) {
        echo "   ✗ Happy image not found\n";
        exit(1);
    }
    echo "   ✓ Both images exist\n";
    
    // Check if photo frame template exists
    $frameTemplatePath = public_path('05/photo_frame.png');
    if (!file_exists($frameTemplatePath)) {
        echo "   ✗ Photo frame template not found at: $frameTemplatePath\n";
        exit(1);
    }
    echo "   ✓ Photo frame template exists\n\n";
    
    // Test createCombinedFramedImage method
    echo "2. Testing createCombinedFramedImage method:\n";
    $createCombinedFramedImageMethod = $reflection->getMethod('createCombinedFramedImage');
    $createCombinedFramedImageMethod->setAccessible(true);
    
    $combinedFramedPath = $createCombinedFramedImageMethod->invoke($controller, $sadImagePath, $happyImagePath);
    
    if ($combinedFramedPath) {
        echo "   ✓ Combined framed image generated: $combinedFramedPath\n";
        if (Storage::disk('public')->exists($combinedFramedPath)) {
            echo "   ✓ Combined framed image file exists\n";
            
            // Get image info
            $imageInfo = getimagesize(Storage::disk('public')->path($combinedFramedPath));
            if ($imageInfo) {
                echo "   ✓ Image dimensions: {$imageInfo[0]}x{$imageInfo[1]} pixels\n";
                echo "   ✓ Image type: {$imageInfo['mime']}\n";
            }
        } else {
            echo "   ✗ Combined framed image file not found\n";
        }
    } else {
        echo "   ✗ Combined framed image generation failed\n";
        echo "   This explains why framed_image column is empty!\n";
    }
    
    // Test overlayFrameOnImages method
    echo "\n3. Testing overlayFrameOnImages method:\n";
    $overlayFrameOnImagesMethod = $reflection->getMethod('overlayFrameOnImages');
    $overlayFrameOnImagesMethod->setAccessible(true);
    
    $framedImages = $overlayFrameOnImagesMethod->invoke($controller, $sadImagePath, $happyImagePath);
    
    if ($framedImages && isset($framedImages['sad_framed']) && isset($framedImages['happy_framed'])) {
        echo "   ✓ Frame overlay images generated:\n";
        echo "     - Sad framed: " . $framedImages['sad_framed'] . "\n";
        echo "     - Happy framed: " . $framedImages['happy_framed'] . "\n";
    } else {
        echo "   ⚠ Frame overlay images not generated\n";
    }
    
    // Test generateFramedImage method
    echo "\n4. Testing generateFramedImage method:\n";
    $generateFramedImageMethod = $reflection->getMethod('generateFramedImage');
    $generateFramedImageMethod->setAccessible(true);
    
    $framedImagePath = $generateFramedImageMethod->invoke($controller, $sadImagePath, $happyImagePath);
    
    if ($framedImagePath) {
        echo "   ✓ Framed image generated: $framedImagePath\n";
    } else {
        echo "   ✗ Framed image generation failed\n";
    }
    
    echo "\n5. Summary:\n";
    echo "   createCombinedFramedImage: " . ($combinedFramedPath ? "SUCCESS" : "FAILED") . "\n";
    echo "   overlayFrameOnImages: " . (($framedImages && isset($framedImages['sad_framed'])) ? "SUCCESS" : "FAILED") . "\n";
    echo "   generateFramedImage: " . ($framedImagePath ? "SUCCESS" : "FAILED") . "\n";
    
    if (!$combinedFramedPath) {
        echo "\n⚠ ISSUE FOUND: createCombinedFramedImage is returning null!\n";
        echo "  This is why the framed_image column is empty in the database.\n";
        echo "  The method is being called but not generating images successfully.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
