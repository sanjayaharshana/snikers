<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageComplianceService
{
    private $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Preprocess image to improve API compliance
     */
    public function preprocessImageForAPI($imagePath, $emotion = 'sad')
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullPath)) {
                Log::error("Image file not found: {$fullPath}");
                return null;
            }

            // Load the image
            $image = $this->imageManager->read($fullPath);
            
            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();
            
            Log::info("Original image dimensions: {$originalWidth}x{$originalHeight}");

            // Apply compliance improvements
            $processedImage = $this->applyComplianceFixes($image, $emotion);
            
            // Save the processed image
            $processedPath = $this->saveProcessedImage($processedImage, $imagePath, $emotion);
            
            Log::info("Image preprocessed successfully: {$processedPath}");
            return $processedPath;
            
        } catch (\Exception $e) {
            Log::error("Image preprocessing failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Apply various fixes to improve API compliance
     */
    private function applyComplianceFixes($image, $emotion)
    {
        // 1. Ensure minimum dimensions (some APIs require minimum size)
        $minWidth = 512;
        $minHeight = 512;
        
        if ($image->width() < $minWidth || $image->height() < $minHeight) {
            Log::info("Resizing image to meet minimum dimensions");
            $image = $image->resize($minWidth, $minHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // 2. Ensure proper aspect ratio (square or portrait works better for face APIs)
        $width = $image->width();
        $height = $image->height();
        
        if ($width > $height * 1.5) {
            // Too wide, crop to better aspect ratio
            Log::info("Cropping wide image to better aspect ratio");
            $newWidth = (int)($height * 1.2);
            $image = $image->crop($newWidth, $height, ($width - $newWidth) / 2, 0);
        }

        // 3. Enhance image quality for better API processing
        $image = $image->sharpen(10); // Slight sharpening
        
        // 4. Adjust brightness/contrast slightly for better face detection
        $image = $image->brightness(5); // Slight brightness increase
        $image = $image->contrast(5);   // Slight contrast increase

        // 5. Ensure proper color space
        $image = $image->toJpeg(90); // High quality JPEG

        return $image;
    }

    /**
     * Save the processed image
     */
    private function saveProcessedImage($image, $originalPath, $emotion)
    {
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);
        $processedFilename = $filename . '_processed_' . $emotion . '_' . time() . '.jpg';
        $processedPath = 'generated/processed/' . $processedFilename;
        
        // Ensure directory exists
        $directory = dirname(Storage::disk('public')->path($processedPath));
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Save the processed image
        $image->save(Storage::disk('public')->path($processedPath));
        
        return $processedPath;
    }

    /**
     * Check if an image needs preprocessing based on its properties
     */
    public function needsPreprocessing($imagePath)
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            $image = $this->imageManager->read($fullPath);
            
            $width = $image->width();
            $height = $image->height();
            
            // Check if image meets minimum requirements
            if ($width < 512 || $height < 512) {
                return true;
            }
            
            // Check aspect ratio
            if ($width > $height * 1.5) {
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error("Error checking image preprocessing needs: " . $e->getMessage());
            return true; // Preprocess if we can't determine
        }
    }
}
