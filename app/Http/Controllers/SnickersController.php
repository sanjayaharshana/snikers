<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\GeneratedImage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SnickersController extends Controller
{
    public function index()
    {
        return view('snickers.campaign');
    }

    public function capture(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'selfie_image' => 'required|string',
        ]);

            // Decode base64 image
            $imageData = $request->selfie_image;
            if (strpos($imageData, 'data:image') === 0) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            }
            $imageData = base64_decode($imageData);

            // Generate unique filename
            $filename = 'selfie_' . time() . '_' . Str::random(10) . '.jpg';
            $tempPath = 'temp/' . $filename;

            // Save temporary image
            Storage::disk('public')->put($tempPath, $imageData);

            // Process with AI emotion editor for both SAD and HAPPY
            $sadImage = $this->processWithAI($tempPath, 'sad');
            $happyImage = $this->processWithAI($tempPath, 'happy');

            // If AI processing fails, use original image for both sad and happy
            if (!$sadImage || !$happyImage) {
                \Log::warning('AI processing failed, using original image for both sad and happy');
                $originalImageData = Storage::disk('public')->get($tempPath);
                $sadImage = base64_encode($originalImageData);
                $happyImage = base64_encode($originalImageData);
            }

            // Save processed images
            $sadFilename = 'sad_' . time() . '_' . Str::random(10) . '.jpg';
            $happyFilename = 'happy_' . time() . '_' . Str::random(10) . '.jpg';
            $sadPath = 'generated/' . $sadFilename;
            $happyPath = 'generated/' . $happyFilename;

            Storage::disk('public')->put($sadPath, base64_decode($sadImage));
            Storage::disk('public')->put($happyPath, base64_decode($happyImage));

            // Generate photo frame combining both images
            $photoFramePath = $this->generatePhotoFrame($sadPath, $happyPath);

            // Generate framed image (enhanced version with better styling)
            $framedImagePath = $this->generateFramedImage($sadPath, $happyPath);

            // Overlay frame on both images using Intervention Image
            $framedImages = $this->overlayFrameOnImages($sadPath, $happyPath);

            // Create combined framed image
            $combinedFramedPath = $this->createCombinedFramedImage($sadPath, $happyPath);
            \Log::info('Combined framed path result: ' . ($combinedFramedPath ?: 'NULL'));

            // Save to database
            $generatedImage = GeneratedImage::create([
                'phone_number' => $request->phone_number,
                'original_image' => $tempPath,
                'sad_image' => $sadPath,
                'happy_image' => $happyPath,
                'photo_frame_path' => $photoFramePath,
                'framed_image' => $combinedFramedPath, // Store the combined framed image
                'emotion_data' => json_encode([
                    'sad_image' => $sadPath,
                    'happy_image' => $happyPath,
                    'photo_frame_path' => $photoFramePath,
                    'framed_image' => $framedImagePath,
                    'sad_framed' => $framedImages['sad_framed'] ?? null,
                    'happy_framed' => $framedImages['happy_framed'] ?? null,
                    'combined_framed' => $combinedFramedPath,
                    'both_processed' => true
                ]),
            ]);

            return response()->json([
                'success' => true,
                'phone_number' => $request->phone_number,
                'original_image_url' => Storage::url($tempPath),
                'sad_image_url' => Storage::url($sadPath),
                'happy_image_url' => Storage::url($happyPath),
                'photo_frame_url' => $photoFramePath ? Storage::url($photoFramePath) : null,
                'framed_image_url' => $combinedFramedPath ? Storage::url($combinedFramedPath) : null, // Main framed image from database column
                'sad_framed_url' => $framedImages['sad_framed'] ? Storage::url($framedImages['sad_framed']) : null,
                'happy_framed_url' => $framedImages['happy_framed'] ? Storage::url($framedImages['happy_framed']) : null,
                'combined_framed_url' => $combinedFramedPath ? Storage::url($combinedFramedPath) : null,
                'generated_image_id' => $generatedImage->id,
                'message' => 'Both emotions processed successfully with frame overlays!'
            ]);

    }

    public function processFirstSelfie(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'selfie_image' => 'required|string',
        ]);

            // Decode base64 image
            $imageData = $request->selfie_image;
            if (strpos($imageData, 'data:image') === 0) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            }
            $imageData = base64_decode($imageData);

            // Generate unique filename for original image
            $filename = 'original_' . time() . '_' . Str::random(10) . '.jpg';
            $originalPath = 'generated/' . $filename;

            // Save original image permanently
            Storage::disk('public')->put($originalPath, $imageData);

            // Process with AI emotion editor for SAD emotion
            \Log::info('Processing first selfie for sad emotion...');
            $sadImage = $this->processWithAI($originalPath, 'sad');
            \Log::info('Sad image processing result: ' . ($sadImage ? 'Success' : 'Failed'));

            // Process with AI emotion editor for HAPPY emotion
            \Log::info('Processing first selfie for happy emotion...');
            $happyImage = $this->processWithAI($originalPath, 'happy');
            \Log::info('Happy image processing result: ' . ($happyImage ? 'Success' : 'Failed'));

            if ($sadImage && $happyImage) {
                // Save processed images
                $sadFilename = 'sad_' . time() . '_' . Str::random(10) . '.jpg';
                $happyFilename = 'happy_' . time() . '_' . Str::random(10) . '.jpg';
                $sadPath = 'generated/' . $sadFilename;
                $happyPath = 'generated/' . $happyFilename;

                Storage::disk('public')->put($sadPath, base64_decode($sadImage));
                Storage::disk('public')->put($happyPath, base64_decode($happyImage));

                // Generate photo frame combining both images
                $photoFramePath = $this->generatePhotoFrame($sadPath, $happyPath);

                // Generate framed image (enhanced version with better styling)
                $framedImagePath = $this->generateFramedImage($sadPath, $happyPath);

                // Overlay frame on both images using Intervention Image
                $framedImages = $this->overlayFrameOnImages($sadPath, $happyPath);

                // Create combined framed image
                $combinedFramedPath = $this->createCombinedFramedImage($sadPath, $happyPath);

                // Save to database with all three images
                $generatedImage = GeneratedImage::create([
                    'phone_number' => $request->phone_number,
                    'original_image' => $originalPath,
                    'sad_image' => $sadPath,
                    'happy_image' => $happyPath,
                    'photo_frame_path' => $photoFramePath,
                    'framed_image' => $combinedFramedPath, // Store the combined framed image
                    'emotion_data' => json_encode([
                        'original_processed' => true,
                        'sad_processed' => true,
                        'happy_processed' => true,
                        'photo_frame_path' => $photoFramePath,
                        'framed_image' => $framedImagePath,
                        'sad_framed' => $framedImages['sad_framed'] ?? null,
                        'happy_framed' => $framedImages['happy_framed'] ?? null,
                        'combined_framed' => $combinedFramedPath,
                        'campaign_completed' => true
                    ]),
                ]);

                return response()->json([
                    'success' => true,
                    'phone_number' => $request->phone_number,
                    'original_image_url' => Storage::url($originalPath),
                    'sad_image_url' => Storage::url($sadPath),
                    'happy_image_url' => Storage::url($happyPath),
                    'photo_frame_url' => $photoFramePath ? Storage::url($photoFramePath) : null,
                    'framed_image_url' => $combinedFramedPath ? Storage::url($combinedFramedPath) : null, // Main framed image from database column
                    'sad_framed_url' => $framedImages['sad_framed'] ? Storage::url($framedImages['sad_framed']) : null,
                    'happy_framed_url' => $framedImages['happy_framed'] ? Storage::url($framedImages['happy_framed']) : null,
                    'combined_framed_url' => $combinedFramedPath ? Storage::url($combinedFramedPath) : null,
                    'generated_image_id' => $generatedImage->id,
                    'message' => 'All emotions processed successfully with frame overlays! Campaign completed!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to process emotions with AI'
            ], 500);


    }

    public function processSecondSelfie(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'selfie_image' => 'required|string',
        ]);

        try {
            // Decode base64 image
            $imageData = $request->selfie_image;
            if (strpos($imageData, 'data:image') === 0) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            }
            $imageData = base64_decode($imageData);

            // Generate unique filename
            $filename = 'second_selfie_' . time() . '_' . Str::random(10) . '.jpg';
            $tempPath = 'temp/' . $filename;

            // Save temporary image
            Storage::disk('public')->put($tempPath, $imageData);

            // Process with AI emotion editor for HAPPY emotion only
            \Log::info('Processing second selfie for happy emotion...');
            $happyImage = $this->processWithAI($tempPath, 'happy');
            \Log::info('Happy image processing result: ' . ($happyImage ? 'Success' : 'Failed'));

            if ($happyImage) {
                // Save processed image
                $happyFilename = 'second_happy_' . time() . '_' . Str::random(10) . '.jpg';
                $happyPath = 'generated/' . $happyFilename;

                Storage::disk('public')->put($happyPath, base64_decode($happyImage));

                return response()->json([
                    'success' => true,
                    'phone_number' => $request->phone_number,
                    'original_image_url' => Storage::url($tempPath),
                    'happy_image_url' => Storage::url($happyPath),
                    'message' => 'Happy emotion processed successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to process happy emotion with AI'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing second selfie: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processWithAI($imagePath, $emotion = 'happy')
    {
        // Check if AI mode is disabled for testing
        if (env('AI_MODE', true) === false) {
            \Log::info('AI_MODE is disabled, using dummy processing for emotion: ' . $emotion);
            return $this->processWithDummyAI($imagePath, $emotion);
        }

        $fullPath = Storage::disk('public')->path($imagePath);

        // Option 1: Use Google Gemini Imagen API (recommended for image emotion editing)
        if (env('USE_GOOGLE_GEMINI_API', false)) {
            return $this->processWithGoogleGemini($fullPath, $emotion);
        }

        // Option 2: Use Replicate API
        if (env('USE_REPLICATE_API', false)) {
            return $this->processWithReplicate($fullPath, $emotion);
        }

        // Option 3: Use Hugging Face API
        if (env('USE_HUGGINGFACE_API', false)) {
            return $this->processWithHuggingFace($fullPath, $emotion);
        }

        // Option 4: Use Google Cloud Vision + Custom Processing
        if (env('USE_GOOGLE_VISION_API', false)) {
            return $this->processWithGoogleVision($fullPath, $emotion);
        }

        // Fallback to original API
        return $this->processWithOriginalAPI($fullPath, $emotion);
    }

    private function processWithGoogleGemini($imagePath, $emotion)
    {
            // Convert image to base64
            $imageData = base64_encode(file_get_contents($imagePath));

            // Create emotion-specific prompts
            $prompts = [
                'sad' => 'Modify this image to show a sad facial expression. Make the person look disappointed, downcast, or melancholy while keeping their identity and overall appearance the same.',
                'happy' => 'Modify this image to show a happy facial expression. Make the person look joyful, cheerful, and satisfied while keeping their identity and overall appearance the same.'
            ];

            $prompt = $prompts[$emotion] ?? $prompts['happy'];

            // Google Gemini Imagen API call
            $response = Http::withHeaders([
                'X-goog-api-key' =>  env('GOOGLE_GEMINI_API_KEY'),
                'Content-Type' => 'application/json'
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image-preview:generateContent?key=' . env('GOOGLE_GEMINI_API_KEY'), [
                    'contents' => [[
                        'parts' => [
                            [ 'text' => $prompt ],
                            [ 'inlineData' => [
                                'mimeType' => 'image/jpeg',
                                'data' => $imageData
                            ]]
                        ]
                    ]],
                    'generationConfig' => [
                        'responseModalities' => ["IMAGE"]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();

                \Log::info('Google Gemini API Response:', $data);

                // Extract the generated image
                if (isset($data['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
                    return $data['candidates'][0]['content']['parts'][0]['inlineData']['data'];
                }

                // Check for alternative response format
                if (isset($data['candidates'][0]['content']['parts'][1]['inlineData']['data'])) {
                    return $data['candidates'][0]['content']['parts'][1]['inlineData']['data'];
                }
            } else {
                \Log::error('Google Gemini API Error: ' . $response->status() . ' - ' . $response->body());
            }

            return null;


    }

    private function processWithReplicate($imagePath, $emotion)
    {
        try {
            // Convert image to base64
            $imageData = base64_encode(file_get_contents($imagePath));

            // Replicate API for emotion-based image editing
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . env('REPLICATE_API_TOKEN'),
                'Content-Type' => 'application/json'
            ])->post('https://api.replicate.com/v1/predictions', [
                'version' => env('REPLICATE_EMOTION_MODEL_VERSION', 'your-model-version'),
                'input' => [
                    'image' => 'data:image/jpeg;base64,' . $imageData,
                    'emotion' => $emotion,
                    'strength' => 0.8
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $predictionId = $data['id'];

                // Poll for completion
                return $this->pollReplicateResult($predictionId);
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Replicate API error: ' . $e->getMessage());
            return null;
        }
    }

    private function processWithHuggingFace($imagePath, $emotion)
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('HUGGINGFACE_API_TOKEN'),
                'Content-Type' => 'application/json'
            ])->post('https://api-inference.huggingface.co/models/your-emotion-model', [
                'inputs' => [
                    'image' => 'data:image/jpeg;base64,' . $imageData,
                    'parameters' => [
                        'emotion' => $emotion,
                        'return_base64' => true
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['generated_image'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Hugging Face API error: ' . $e->getMessage());
            return null;
        }
    }

    private function processWithGoogleVision($imagePath, $emotion)
    {
        try {
            // First, detect emotions using Google Vision API
            $imageData = base64_encode(file_get_contents($imagePath));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('GOOGLE_VISION_API_KEY'),
                'Content-Type' => 'application/json'
            ])->post('https://vision.googleapis.com/v1/images:annotate', [
                'requests' => [
                    [
                        'image' => ['content' => $imageData],
                        'features' => [
                            ['type' => 'FACE_DETECTION'],
                            ['type' => 'EMOTION_DETECTION']
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Process the emotion detection results
                // Note: Google Vision only detects emotions, doesn't modify images
                // You would need additional processing to modify the image
                return $this->applyEmotionToImage($imagePath, $emotion, $data);
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Google Vision API error: ' . $e->getMessage());
            return null;
        }
    }

    private function processWithOriginalAPI($imagePath, $emotion)
    {
        // Keep the original implementation as fallback
        $serviceChoice = $emotion === 'sad' ? '15' : '12';

        $response = Http::withHeaders([
            'ailabapi-api-key' => env('AILABTOOLS_API_KEY', 'imff7TwAtdh9xZku1PWRCMjN9CJqLFvr5BevQyKI3ZzEy6DTOrXVI8S4hWgo146U')
        ])->attach('image_target', file_get_contents($imagePath), basename($imagePath))
        ->post('https://www.ailabapi.com/api/portrait/effects/emotion-editor', [
            'service_choice' => $serviceChoice
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['data']['image'])) {
                return $data['data']['image'];
            }
        }

        return null;
    }

    private function pollReplicateResult($predictionId)
    {
        $maxAttempts = 30; // 30 seconds max
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . env('REPLICATE_API_TOKEN')
            ])->get("https://api.replicate.com/v1/predictions/{$predictionId}");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'succeeded') {
                    return $data['output'] ?? null;
                } elseif ($data['status'] === 'failed') {
                    return null;
                }

                // Still processing, wait and try again
                sleep(1);
                $attempt++;
            } else {
                return null;
            }
        }

        return null;
    }

    private function applyEmotionToImage($imagePath, $emotion, $visionData)
    {
        // This is a placeholder for custom image processing
        // You would implement your own emotion application logic here
        // For now, return the original image
        return base64_encode(file_get_contents($imagePath));
    }

    private function processWithDummyAI($imagePath, $emotion = 'happy')
    {
        \Log::info('Processing with dummy AI for emotion: ' . $emotion);

        // Simulate processing delay
        sleep(2);

        // For dummy mode, we'll return the original image as base64
        // This allows the UI to work without making actual API calls
        $imageData = base64_encode(file_get_contents($imagePath));

        \Log::info('Dummy AI processing completed for emotion: ' . $emotion);

        return $imageData;
    }

    public function getImage($filename)
    {
        $path = 'generated/' . $filename;
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }
        return response()->json(['error' => 'Image not found'], 404);
    }

    public function testStep4Data()
    {
        // Get the most recent generated image for testing
        $latestImage = GeneratedImage::latest()->first();

        if (!$latestImage) {
            return response()->json([
                'success' => false,
                'message' => 'No generated images found'
            ]);
        }

        return response()->json([
            'success' => true,
            'phone_number' => $latestImage->phone_number,
            'original_image_url' => Storage::url($latestImage->original_image),
            'sad_image_url' => Storage::url($latestImage->sad_image),
            'happy_image_url' => Storage::url($latestImage->happy_image),
            'photo_frame_url' => $latestImage->photo_frame_path ? Storage::url($latestImage->photo_frame_path) : null,
            'framed_image_url' => $latestImage->framed_image ? Storage::url($latestImage->framed_image) : null,
            'generated_image_id' => $latestImage->id,
            'emotion_data' => $latestImage->emotion_data,
            'message' => 'Test data retrieved successfully'
        ]);
    }

    /**
     * Generate photo frame with split-screen layout: sad on top, happy on bottom
     * Following the Snickers promotional design with branding elements
     */
    private function generatePhotoFrame($sadImagePath, $happyImagePath)
    {
        try {
            // Check if GD functions are available
            if (!function_exists('imagecreate')) {
                \Log::warning('GD functions not available, using Intervention Image');
                return $this->generatePhotoFrameWithIntervention($sadImagePath, $happyImagePath);
            }

            // Load the sad and happy images (detect format)
            $sadImagePathFull = Storage::disk('public')->path($sadImagePath);
            $happyImagePathFull = Storage::disk('public')->path($happyImagePath);

            $sadImage = $this->loadImageByFormat($sadImagePathFull);
            $happyImage = $this->loadImageByFormat($happyImagePathFull);

            if (!$sadImage || !$happyImage) {
                \Log::error('Failed to load images for photo frame generation');
                return $this->generatePhotoFrameWithIntervention($sadImagePath, $happyImagePath);
            }

            // Get dimensions
            $sadWidth = imagesx($sadImage);
            $sadHeight = imagesy($sadImage);
            $happyWidth = imagesx($happyImage);
            $happyHeight = imagesy($happyImage);

            // Calculate frame dimensions (vertical split)
            $frameWidth = max($sadWidth, $happyWidth);
            $frameHeight = $sadHeight + $happyHeight + 20; // 20px gap between images

            // Create the photo frame canvas
            $photoFrame = imagecreatetruecolor($frameWidth, $frameHeight);

            // Set background to white
            $white = imagecolorallocate($photoFrame, 255, 255, 255);
            imagefill($photoFrame, 0, 0, $white);

            // Add red border
            $red = imagecolorallocate($photoFrame, 223, 1, 0); // Snickers red
            imagerectangle($photoFrame, 0, 0, $frameWidth - 1, $frameHeight - 1, $red);

            // Copy sad image to the top half
            $sadX = ($frameWidth - $sadWidth) / 2; // Center horizontally
            imagecopy($photoFrame, $sadImage, $sadX, 5, 0, 0, $sadWidth, $sadHeight);

            // Copy happy image to the bottom half
            $happyX = ($frameWidth - $happyWidth) / 2; // Center horizontally
            $happyY = $sadHeight + 15; // 15px gap from sad image
            imagecopy($photoFrame, $happyImage, $happyX, $happyY, 0, 0, $happyWidth, $happyHeight);

            // Add Snickers branding text
            $this->addSnickersBranding($photoFrame, $frameWidth, $frameHeight);

            // Generate filename and save
            $frameFilename = 'photo_frame.png';
            $framePath = '05/' . $frameFilename;

            // Ensure the 05 directory exists
            $frameDir = Storage::disk('public')->path('05');
            if (!is_dir($frameDir)) {
                mkdir($frameDir, 0755, true);
            }

            // Save as PNG
            $fullFramePath = Storage::disk('public')->path($framePath);
            if (imagepng($photoFrame, $fullFramePath)) {
                // Clean up memory
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                imagedestroy($photoFrame);

                \Log::info('Photo frame generated successfully: ' . $framePath);
                return $framePath;
            } else {
                \Log::error('Failed to save photo frame image');
                return null;
            }

        } catch (\Exception $e) {
            \Log::error('Error generating photo frame: ' . $e->getMessage());
            return $this->generatePhotoFrameWithIntervention($sadImagePath, $happyImagePath);
        }
    }

    /**
     * Fallback method when GD is not available - creates a simple image-based photo frame
     */
    private function generateSimplePhotoFrame($sadImagePath, $happyImagePath)
    {
        try {
            // Check if Intervention Image is available as fallback
            if (class_exists('Intervention\Image\ImageManager')) {
                return $this->generatePhotoFrameWithIntervention($sadImagePath, $happyImagePath);
            }

            // If no image processing is available, return null
            \Log::warning('No image processing library available for photo frame generation');
            return null;

        } catch (\Exception $e) {
            \Log::error('Error generating simple photo frame: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate photo frame using Intervention Image as fallback
     */
    private function generatePhotoFrameWithIntervention($sadImagePath, $happyImagePath)
    {
        try {
            // Initialize Intervention Image Manager
            $manager = new ImageManager(new Driver());

            // Load both images
            $sadImage = $manager->read(Storage::disk('public')->path($sadImagePath));
            $happyImage = $manager->read(Storage::disk('public')->path($happyImagePath));

            // Get dimensions
            $sadWidth = $sadImage->width();
            $sadHeight = $sadImage->height();
            $happyWidth = $happyImage->width();
            $happyHeight = $happyImage->height();

            // Calculate frame dimensions (vertical split)
            $frameWidth = max($sadWidth, $happyWidth);
            $frameHeight = $sadHeight + $happyHeight + 20; // 20px gap between images

            // Create the photo frame canvas with white background
            $photoFrame = $manager->create($frameWidth, $frameHeight, '#ffffff');

            // Add red border (Snickers red)
            $photoFrame->drawRectangle(0, 0, function ($draw) use ($frameWidth, $frameHeight) {
                $draw->size($frameWidth - 1, $frameHeight - 1);
                $draw->border(3, '#DF0100');
            });

            // Copy sad image to the top half
            $sadX = ($frameWidth - $sadWidth) / 2; // Center horizontally
            $photoFrame->place($sadImage, 'top-left', $sadX, 5);

            // Copy happy image to the bottom half
            $happyX = ($frameWidth - $happyWidth) / 2; // Center horizontally
            $happyY = $sadHeight + 15; // 15px gap from sad image
            $photoFrame->place($happyImage, 'top-left', $happyX, $happyY);

            // Add Snickers branding text
            $this->addSnickersBrandingIntervention($photoFrame, $frameWidth, $frameHeight);

            // Generate filename and save
            $frameFilename = 'photo_frame_' . time() . '_' . Str::random(10) . '.png';
            $framePath = 'generated/' . $frameFilename;

            // Save the result
            $photoFrame->save(Storage::disk('public')->path($framePath));

            \Log::info('Photo frame generated with Intervention Image: ' . $framePath);
            return $framePath;

        } catch (\Exception $e) {
            \Log::error('Error generating photo frame with Intervention Image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add Snickers branding elements to the photo frame using Intervention Image
     */
    private function addSnickersBrandingIntervention($image, $width, $height)
    {
        try {
            // Add Snickers logo area at the top
            $logoHeight = 60;

            // Create a red rectangle for the logo background
            $logoBackground = $image->newImage($width, $logoHeight, '#DF0100');
            $image->place($logoBackground, 'top-left', 0, 0);

            // Add blue border inside
            $image->drawRectangle(2, 2, function ($draw) use ($width, $logoHeight) {
                $draw->size($width - 3, $logoHeight - 3);
                $draw->border(2, '#0066CC');
            });

            // Add "SNICKERS" text (simplified - using basic text)
            $image->text('SNICKERS', $width / 2, $logoHeight / 2, function ($font) {
                $font->filename('Arial');
                $font->size(24);
                $font->color('#FFFFFF');
                $font->align('center');
                $font->valign('middle');
            });

            // Add slogan at the bottom
            $sloganY = $height - 40;

            // "YOU'RE NOT YOU"
            $image->text("YOU'RE NOT YOU", $width / 2, $sloganY, function ($font) {
                $font->filename('Arial');
                $font->size(16);
                $font->color('#FFFFFF');
                $font->align('center');
            });

            // "WHEN YOU ARE"
            $image->text("WHEN YOU ARE", $width / 2, $sloganY + 15, function ($font) {
                $font->filename('Arial');
                $font->size(16);
                $font->color('#FFFFFF');
                $font->align('center');
            });

            // "HUNGRY" in orange
            $image->text("HUNGRY", $width / 2, $sloganY + 30, function ($font) {
                $font->filename('Arial');
                $font->size(18);
                $font->color('#FF6600');
                $font->align('center');
            });

        } catch (\Exception $e) {
            \Log::error('Error adding Snickers branding: ' . $e->getMessage());
        }
    }

    /**
     * Generate HTML content for the photo frame
     */
    private function generatePhotoFrameHTML($sadImageUrl, $happyImageUrl)
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snickers Photo Frame</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #8B4513;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .photo-frame {
            background: white;
            border: 8px solid #DF0100;
            border-radius: 20px;
            padding: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .snickers-logo {
            background: #DF0100;
            color: white;
            text-align: center;
            padding: 15px;
            margin: -20px -20px 20px -20px;
            border-radius: 12px 12px 0 0;
            font-size: 24px;
            font-weight: bold;
            border: 3px solid #0066CC;
        }

        .image-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .image-section {
            text-align: center;
        }

        .image-section img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .slogan {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        .slogan-line {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }

        .hungry-text {
            color: #FF6600;
            font-size: 20px;
        }

        .snickers-bar {
            text-align: center;
            margin-top: 15px;
        }

        .snickers-bar img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="photo-frame">
        <div class="snickers-logo">SNICKERS</div>

        <div class="image-container">
            <div class="image-section">
                <h3 style="color: #666; margin-bottom: 10px;">SAD</h3>
                <img src="' . $sadImageUrl . '" alt="Sad Expression">
            </div>

            <div class="image-section">
                <h3 style="color: #666; margin-bottom: 10px;">HAPPY</h3>
                <img src="' . $happyImageUrl . '" alt="Happy Expression">
            </div>
        </div>

        <div class="slogan">
            <div class="slogan-line">YOU\'RE NOT YOU</div>
            <div class="slogan-line">WHEN YOU ARE</div>
            <div class="slogan-line hungry-text">HUNGRY</div>
        </div>

        <div class="snickers-bar">
            <img src="/07/SNICKERS BAR_1.png" alt="Snickers Bar">
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Add Snickers branding elements to the photo frame
     */
    private function addSnickersBranding($image, $width, $height)
    {
        if (!function_exists('imagecreate')) {
            return;
        }

        // Colors
        $red = imagecolorallocate($image, 223, 1, 0); // Snickers red
        $blue = imagecolorallocate($image, 0, 102, 204); // Snickers blue
        $white = imagecolorallocate($image, 255, 255, 255);
        $orange = imagecolorallocate($image, 255, 165, 0); // For "HUNGRY" text

        // Add Snickers logo area at the top
        $logoHeight = 60;
        imagefilledrectangle($image, 0, 0, $width, $logoHeight, $red);

        // Add blue border inside
        imagerectangle($image, 2, 2, $width - 3, $logoHeight - 3, $blue);

        // Add "SNICKERS" text (simplified - using built-in font)
        $font = 5; // Built-in font
        $text = "SNICKERS";
        $textWidth = imagefontwidth($font) * strlen($text);
        $textX = ($width - $textWidth) / 2;
        $textY = ($logoHeight - imagefontheight($font)) / 2;
        imagestring($image, $font, $textX, $textY, $text, $white);

        // Add slogan at the bottom
        $sloganY = $height - 40;
        $sloganFont = 3;

        // "YOU'RE NOT YOU"
        $slogan1 = "YOU'RE NOT YOU";
        $slogan1Width = imagefontwidth($sloganFont) * strlen($slogan1);
        $slogan1X = ($width - $slogan1Width) / 2;
        imagestring($image, $sloganFont, $slogan1X, $sloganY, $slogan1, $white);

        // "WHEN YOU ARE HUNGRY"
        $slogan2 = "WHEN YOU ARE";
        $slogan2Width = imagefontwidth($sloganFont) * strlen($slogan2);
        $slogan2X = ($width - $slogan2Width) / 2;
        imagestring($image, $sloganFont, $slogan2X, $sloganY + 15, $slogan2, $white);

        // "HUNGRY" in orange
        $hungryText = "HUNGRY";
        $hungryWidth = imagefontwidth($sloganFont) * strlen($hungryText);
        $hungryX = ($width - $hungryWidth) / 2;
        imagestring($image, $sloganFont, $hungryX, $sloganY + 30, $hungryText, $orange);
    }

    /**
     * Generate enhanced framed image using photo_frame.png as base template
     */
    private function generateFramedImage($sadImagePath, $happyImagePath)
    {
        try {
            // Check if GD functions are available
            if (!function_exists('imagecreate')) {
                \Log::warning('GD functions not available, using Intervention Image for framed image');
                return $this->generateFramedImageWithIntervention($sadImagePath, $happyImagePath);
            }

            // Load the base photo frame template
            $frameTemplatePath = public_path('05/photo_frame.png');
            if (!file_exists($frameTemplatePath)) {
                \Log::error('Photo frame template not found at: ' . $frameTemplatePath);
                return null;
            }

            $baseFrame = imagecreatefrompng($frameTemplatePath);
            if (!$baseFrame) {
                \Log::error('Failed to load base photo frame template: ' . $frameTemplatePath);
                return null;
            }

            $frameWidth = imagesx($baseFrame);
            $frameHeight = imagesy($baseFrame);

            // Load the sad and happy images (detect format)
            $sadImagePathFull = Storage::disk('public')->path($sadImagePath);
            $happyImagePathFull = Storage::disk('public')->path($happyImagePath);

            $sadImage = $this->loadImageByFormat($sadImagePathFull);
            $happyImage = $this->loadImageByFormat($happyImagePathFull);

            if (!$sadImage || !$happyImage) {
                \Log::error('Failed to load images for framed image generation');
                imagedestroy($baseFrame);
                return $this->generateFramedImageWithIntervention($sadImagePath, $happyImagePath);
            }

            // Define the target dimensions and positions for sad and happy images within the frame
            // Based on the photo_frame.png template structure
            $paddingX = 30; // Horizontal padding from the frame edges
            $paddingYTop = 70; // Top padding for the first image
            $paddingYBottom = 100; // Bottom padding for the second image
            $gapY = 5; // Gap between the two images

            $imageAreaWidth = $frameWidth - (2 * $paddingX);
            $imageAreaHeight = ($frameHeight - $paddingYTop - $paddingYBottom - $gapY) / 2;

            // Resize and place sad image (top)
            $sadImageResized = imagescale($sadImage, $imageAreaWidth, $imageAreaHeight);
            if ($sadImageResized === false) {
                \Log::error('Failed to resize sad image for framed image.');
                imagedestroy($baseFrame);
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                return null;
            }
            imagecopy($baseFrame, $sadImageResized, $paddingX, $paddingYTop, 0, 0, $imageAreaWidth, $imageAreaHeight);
            imagedestroy($sadImageResized);

            // Resize and place happy image (bottom)
            $happyImageResized = imagescale($happyImage, $imageAreaWidth, $imageAreaHeight);
            if ($happyImageResized === false) {
                \Log::error('Failed to resize happy image for framed image.');
                imagedestroy($baseFrame);
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                return null;
            }
            imagecopy($baseFrame, $happyImageResized, $paddingX, $paddingYTop + $imageAreaHeight + $gapY, 0, 0, $imageAreaWidth, $imageAreaHeight);
            imagedestroy($happyImageResized);

            // Generate filename and save
            $frameFilename = 'framed_image_' . time() . '_' . Str::random(10) . '.png';
            $framePath = 'generated/' . $frameFilename;

            // Save as PNG
            if (imagepng($baseFrame, Storage::disk('public')->path($framePath))) {
                // Clean up memory
                imagedestroy($baseFrame);
                imagedestroy($sadImage);
                imagedestroy($happyImage);

                \Log::info('Framed image generated successfully: ' . $framePath);
                return $framePath;
            } else {
                \Log::error('Failed to save framed image');
                imagedestroy($baseFrame);
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                return null;
            }

        } catch (\Exception $e) {
            \Log::error('Error generating framed image: ' . $e->getMessage());
            return $this->generateFramedImageWithIntervention($sadImagePath, $happyImagePath);
        }
    }

    /**
     * Generate framed image using Intervention Image with photo_frame.png as base template
     */
    private function generateFramedImageWithIntervention($sadImagePath, $happyImagePath)
    {
        try {
            // Initialize Intervention Image Manager
            $manager = new ImageManager(new Driver());

            // Load the base photo frame template
            $frameTemplatePath = public_path('05/photo_frame.png');
            if (!file_exists($frameTemplatePath)) {
                \Log::error('Photo frame template not found for Intervention Image at: ' . $frameTemplatePath);
                return null;
            }

            $baseFrame = $manager->read($frameTemplatePath);
            $frameWidth = $baseFrame->width();
            $frameHeight = $baseFrame->height();

            // Load sad and happy images
            $sadImage = $manager->read(Storage::disk('public')->path($sadImagePath));
            $happyImage = $manager->read(Storage::disk('public')->path($happyImagePath));

            // Define the target dimensions and positions for sad and happy images within the frame
            $paddingX = 30;
            $paddingYTop = 70;
            $paddingYBottom = 100;
            $gapY = 5;

            $imageAreaWidth = $frameWidth - (2 * $paddingX);
            $imageAreaHeight = ($frameHeight - $paddingYTop - $paddingYBottom - $gapY) / 2;

            // Resize and place sad image (top)
            $sadImage->resize($imageAreaWidth, $imageAreaHeight);
            $baseFrame->place($sadImage, 'top-left', $paddingX, $paddingYTop);

            // Resize and place happy image (bottom)
            $happyImage->resize($imageAreaWidth, $imageAreaHeight);
            $baseFrame->place($happyImage, 'top-left', $paddingX, $paddingYTop + $imageAreaHeight + $gapY);

            // Generate filename and save
            $frameFilename = 'framed_image_' . time() . '_' . Str::random(10) . '.png';
            $framePath = 'generated/' . $frameFilename;

            // Save the result
            $baseFrame->save(Storage::disk('public')->path($framePath));

            \Log::info('Framed image generated with Intervention Image: ' . $framePath);
            return $framePath;

        } catch (\Exception $e) {
            \Log::error('Error generating framed image with Intervention Image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add Snickers header to framed image using GD
     */
    private function addSnickersHeader($image, $width)
    {
        if (!function_exists('imagecreate')) {
            return;
        }

        // Colors
        $red = imagecolorallocate($image, 223, 1, 0); // Snickers red
        $blue = imagecolorallocate($image, 0, 102, 204); // Snickers blue
        $white = imagecolorallocate($image, 255, 255, 255);

        // Add Snickers logo area at the top
        $logoHeight = 60;
        imagefilledrectangle($image, 0, 0, $width, $logoHeight, $red);

        // Add blue border inside
        imagerectangle($image, 2, 2, $width - 3, $logoHeight - 3, $blue);

        // Add "SNICKERS" text
        $font = 5; // Built-in font
        $text = "SNICKERS";
        $textWidth = imagefontwidth($font) * strlen($text);
        $textX = ($width - $textWidth) / 2;
        $textY = ($logoHeight - imagefontheight($font)) / 2;
        imagestring($image, $font, $textX, $textY, $text, $white);
    }

    /**
     * Add image labels using GD
     */
    private function addImageLabels($image, $sadX, $sadY, $happyX, $happyY, $sadWidth, $happyWidth)
    {
        if (!function_exists('imagecreate')) {
            return;
        }

        $black = imagecolorallocate($image, 0, 0, 0);
        $orange = imagecolorallocate($image, 255, 165, 0);
        $font = 3;

        // Add "SAD" label
        $sadLabel = "SAD";
        $sadLabelWidth = imagefontwidth($font) * strlen($sadLabel);
        $sadLabelX = $sadX + ($sadWidth - $sadLabelWidth) / 2;
        imagestring($image, $font, $sadLabelX, $sadY - 20, $sadLabel, $black);

        // Add "HAPPY" label
        $happyLabel = "HAPPY";
        $happyLabelWidth = imagefontwidth($font) * strlen($happyLabel);
        $happyLabelX = $happyX + ($happyWidth - $happyLabelWidth) / 2;
        imagestring($image, $font, $happyLabelX, $happyY - 20, $happyLabel, $orange);
    }

    /**
     * Add Snickers slogan using GD
     */
    private function addSnickersSlogan($image, $width, $height)
    {
        if (!function_exists('imagecreate')) {
            return;
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $orange = imagecolorallocate($image, 255, 165, 0);
        $font = 3;

        $sloganY = $height - 40;

        // "YOU'RE NOT YOU"
        $slogan1 = "YOU'RE NOT YOU";
        $slogan1Width = imagefontwidth($font) * strlen($slogan1);
        $slogan1X = ($width - $slogan1Width) / 2;
        imagestring($image, $font, $slogan1X, $sloganY, $slogan1, $white);

        // "WHEN YOU ARE HUNGRY"
        $slogan2 = "WHEN YOU ARE";
        $slogan2Width = imagefontwidth($font) * strlen($slogan2);
        $slogan2X = ($width - $slogan2Width) / 2;
        imagestring($image, $font, $slogan2X, $sloganY + 15, $slogan2, $white);

        // "HUNGRY" in orange
        $hungryText = "HUNGRY";
        $hungryWidth = imagefontwidth($font) * strlen($hungryText);
        $hungryX = ($width - $hungryWidth) / 2;
        imagestring($image, $font, $hungryX, $sloganY + 30, $hungryText, $orange);
    }

    /**
     * Add Snickers header to framed image using Intervention Image
     */
    private function addSnickersHeaderIntervention($image, $width)
    {
        try {
            // Add Snickers logo area at the top
            $logoHeight = 60;

            // Create a red rectangle for the logo background
            $logoBackground = $image->newImage($width, $logoHeight, '#DF0100');
            $image->place($logoBackground, 'top-left', 0, 0);

            // Add blue border inside
            $image->drawRectangle(2, 2, function ($draw) use ($width, $logoHeight) {
                $draw->size($width - 3, $logoHeight - 3);
                $draw->border(2, '#0066CC');
            });

            // Add "SNICKERS" text
            $image->text('SNICKERS', $width / 2, $logoHeight / 2, function ($font) {
                $font->filename('Arial');
                $font->size(24);
                $font->color('#FFFFFF');
                $font->align('center');
                $font->valign('middle');
            });

        } catch (\Exception $e) {
            \Log::error('Error adding Snickers header: ' . $e->getMessage());
        }
    }

    /**
     * Add image labels using Intervention Image
     */
    private function addImageLabelsIntervention($image, $sadX, $sadY, $happyX, $happyY, $sadWidth, $happyWidth)
    {
        try {
            // Add "SAD" label
            $sadLabelX = $sadX + $sadWidth / 2;
            $image->text('SAD', $sadLabelX, $sadY - 20, function ($font) {
                $font->filename('Arial');
                $font->size(16);
                $font->color('#000000');
                $font->align('center');
            });

            // Add "HAPPY" label
            $happyLabelX = $happyX + $happyWidth / 2;
            $image->text('HAPPY', $happyLabelX, $happyY - 20, function ($font) {
                $font->filename('Arial');
                $font->size(16);
                $font->color('#FF6600');
                $font->align('center');
            });

        } catch (\Exception $e) {
            \Log::error('Error adding image labels: ' . $e->getMessage());
        }
    }

    /**
     * Add Snickers slogan using Intervention Image
     */
    private function addSnickersSloganIntervention($image, $width, $height)
    {
        try {
            $sloganY = $height - 40;

            // "YOU'RE NOT YOU"
            $image->text("YOU'RE NOT YOU", $width / 2, $sloganY, function ($font) {
                $font->filename('Arial');
                $font->size(16);
                $font->color('#FFFFFF');
                $font->align('center');
            });

            // "WHEN YOU ARE"
            $image->text("WHEN YOU ARE", $width / 2, $sloganY + 15, function ($font) {
                $font->filename('Arial');
                $font->size(16);
                $font->color('#FFFFFF');
                $font->align('center');
            });

            // "HUNGRY" in orange
            $image->text("HUNGRY", $width / 2, $sloganY + 30, function ($font) {
                $font->filename('Arial');
                $font->size(18);
                $font->color('#FF6600');
                $font->align('center');
            });

        } catch (\Exception $e) {
            \Log::error('Error adding Snickers slogan: ' . $e->getMessage());
        }
    }

    /**
     * Load image by detecting its format
     */
    private function loadImageByFormat($imagePath)
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }

        $mimeType = $imageInfo['mime'];

        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($imagePath);
            case 'image/png':
                return imagecreatefrompng($imagePath);
            case 'image/gif':
                return imagecreatefromgif($imagePath);
            case 'image/webp':
                return imagecreatefromwebp($imagePath);
            default:
                \Log::error('Unsupported image format: ' . $mimeType);
                return false;
        }
    }

    /**
     * Generate enhanced HTML content for the framed image
     */
    private function generateFramedImageHTML($sadImageUrl, $happyImageUrl)
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snickers Framed Image</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
            font-family: "Arial Black", Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .framed-container {
            background: white;
            border: 12px solid #DF0100;
            border-radius: 25px;
            padding: 0;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
        }

        .snickers-header {
            background: linear-gradient(45deg, #DF0100, #FF4444);
            color: white;
            text-align: center;
            padding: 20px;
            position: relative;
            border-bottom: 4px solid #0066CC;
        }

        .snickers-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            pointer-events: none;
        }

        .snickers-logo {
            font-size: 32px;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 2px;
            position: relative;
            z-index: 1;
        }

        .images-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 30px;
            background: #f8f8f8;
        }

        .image-card {
            text-align: center;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .image-card:hover {
            transform: translateY(-5px);
        }

        .emotion-label {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sad-label {
            color: #666;
        }

        .happy-label {
            color: #FF6600;
        }

        .image-card img {
            width: 100%;
            max-width: 250px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .slogan-section {
            background: linear-gradient(135deg, #333, #555);
            color: white;
            text-align: center;
            padding: 25px;
            position: relative;
        }

        .slogan-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #DF0100, #FF6600, #DF0100);
        }

        .slogan-text {
            font-size: 20px;
            font-weight: bold;
            line-height: 1.4;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .hungry-highlight {
            color: #FF6600;
            font-size: 24px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }

        .snickers-product {
            text-align: center;
            padding: 20px;
            background: #f0f0f0;
        }

        .snickers-product img {
            max-width: 120px;
            height: auto;
            filter: drop-shadow(0 5px 10px rgba(0,0,0,0.3));
        }

        @media (max-width: 768px) {
            .images-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 20px;
            }

            .snickers-logo {
                font-size: 24px;
            }

            .slogan-text {
                font-size: 16px;
            }

            .hungry-highlight {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="framed-container">
        <div class="snickers-header">
            <div class="snickers-logo">SNICKERS</div>
        </div>

        <div class="images-grid">
            <div class="image-card">
                <div class="emotion-label sad-label">SAD</div>
                <img src="' . $sadImageUrl . '" alt="Sad Expression">
            </div>

            <div class="image-card">
                <div class="emotion-label happy-label">HAPPY</div>
                <img src="' . $happyImageUrl . '" alt="Happy Expression">
            </div>
        </div>

        <div class="slogan-section">
            <div class="slogan-text">
                YOU\'RE NOT YOU<br>
                WHEN YOU ARE<br>
                <span class="hungry-highlight">HUNGRY</span>
            </div>
        </div>

        <div class="snickers-product">
            <img src="/07/SNICKERS BAR_1.png" alt="Snickers Bar">
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Overlay frame image on both sad and happy images using Intervention Image
     *
     * @param string $sadImagePath Path to the sad image
     * @param string $happyImagePath Path to the happy image
     * @return array Array containing paths to the framed images
     */
    private function overlayFrameOnImages($sadImagePath, $happyImagePath)
    {
            // Check if GD functions are available
            if (!function_exists('imagecreate')) {
                \Log::warning('GD functions not available, skipping frame overlay');
                return [
                    'sad_framed' => null,
                    'happy_framed' => null
                ];
            }

            // Initialize Intervention Image Manager with GD driver
            $manager = new ImageManager(new Driver());

            // Path to the frame image
            $framePath = public_path('05/photo_frame.png');

            // Check if frame image exists
            if (!file_exists($framePath)) {
                \Log::error('Frame image not found at: ' . $framePath);
                return [
                    'sad_framed' => null,
                    'happy_framed' => null
                ];
            }

            // Load the frame image
            $frameImage = $manager->read($framePath);

            // Process sad image
            $sadFramedPath = $this->overlayFrameOnSingleImage($manager, $sadImagePath, $frameImage, 'sad');

            // Process happy image
            $happyFramedPath = $this->overlayFrameOnSingleImage($manager, $happyImagePath, $frameImage, 'happy');

            return [
                'sad_framed' => $sadFramedPath,
                'happy_framed' => $happyFramedPath
            ];


    }

    /**
     * Overlay frame on a single image
     *
     * @param ImageManager $manager Intervention Image Manager
     * @param string $imagePath Path to the source image
     * @param \Intervention\Image\Image $frameImage Frame image object
     * @param string $emotion Emotion type (sad/happy) for filename
     * @return string|null Path to the framed image or null on failure
     */
    private function overlayFrameOnSingleImage($manager, $imagePath, $frameImage, $emotion)
    {
            // Load the source image
            $sourceImage = $manager->read(Storage::disk('public')->path($imagePath));

            // Get dimensions
            $sourceWidth = $sourceImage->width();
            $sourceHeight = $sourceImage->height();
            $frameWidth = $frameImage->width();
            $frameHeight = $frameImage->height();

            // Calculate scaling to fit the frame
            $scaleX = $sourceWidth / $frameWidth;
            $scaleY = $sourceHeight / $frameHeight;
            $scale = min($scaleX, $scaleY);

            // Resize the frame to match the source image size
            $resizedFrame = $frameImage->scale($scale);

            // If the frame is smaller than the source after scaling, center it
            $frameX = 0;
            $frameY = 0;

            if ($resizedFrame->width() < $sourceWidth) {
                $frameX = ($sourceWidth - $resizedFrame->width()) / 2;
            }

            if ($resizedFrame->height() < $sourceHeight) {
                $frameY = ($sourceHeight - $resizedFrame->height()) / 2;
            }

            // Create a new image with the same dimensions as the source
            $resultImage = $manager->create($sourceWidth, $sourceHeight);

            // Place the source image as background
            $resultImage->place($sourceImage, 'top-left', 0, 0);

            // Overlay the frame on top
            $resultImage->place($resizedFrame, 'top-left', $frameX, $frameY);

            // Generate filename and save
            $framedFilename = $emotion . '_framed_' . time() . '_' . Str::random(10) . '.png';
            $framedPath = 'generated/' . $framedFilename;

            // Save the result
            $resultImage->save(Storage::disk('public')->path($framedPath));

            \Log::info('Framed image saved: ' . $framedPath);

            return $framedPath;

    }

    /**
     * Create a combined image with both emotions and frame overlay as single JPG
     *
     * @param string $sadImagePath Path to the sad image
     * @param string $happyImagePath Path to the happy image
     * @return string|null Path to the combined framed image or null on failure
     */
    private function createCombinedFramedImage($sadImagePath, $happyImagePath)
    {
        try {
            // Check if GD functions are available
            if (!function_exists('imagecreate')) {
                \Log::warning('GD functions not available, using Intervention Image for combined frame');
                return $this->createCombinedFramedImageWithIntervention($sadImagePath, $happyImagePath);
            }

            // Load the sad and happy images
            $sadImagePathFull = Storage::disk('public')->path($sadImagePath);
            $happyImagePathFull = Storage::disk('public')->path($happyImagePath);

            $sadImage = $this->loadImageByFormat($sadImagePathFull);
            $happyImage = $this->loadImageByFormat($happyImagePathFull);

            if (!$sadImage || !$happyImage) {
                \Log::error('Failed to load images for combined framed image generation');
                return $this->createCombinedFramedImageWithIntervention($sadImagePath, $happyImagePath);
            }

            // Get dimensions
            $sadWidth = imagesx($sadImage);
            $sadHeight = imagesy($sadImage);
            $happyWidth = imagesx($happyImage);
            $happyHeight = imagesy($happyImage);

            // Calculate combined canvas size (side by side with gap)
            $gap = 20;
            $canvasWidth = $sadWidth + $happyWidth + $gap;
            $canvasHeight = max($sadHeight, $happyHeight);

            // Create canvas with white background
            $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);

            // Place sad image on the left
            imagecopy($canvas, $sadImage, 0, 0, 0, 0, $sadWidth, $sadHeight);

            // Place happy image on the right
            imagecopy($canvas, $happyImage, $sadWidth + $gap, 0, 0, 0, $happyWidth, $happyHeight);

            // Load the frame image
            $framePath = public_path('05/photo_frame.png');
            if (!file_exists($framePath)) {
                \Log::error('Frame image not found at: ' . $framePath);
                imagedestroy($canvas);
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                return null;
            }

            $frameImage = imagecreatefrompng($framePath);
            if (!$frameImage) {
                \Log::error('Failed to load frame image');
                imagedestroy($canvas);
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                return null;
            }

            $frameWidth = imagesx($frameImage);
            $frameHeight = imagesy($frameImage);

            // Scale frame to fit the canvas
            $frameScaleX = $canvasWidth / $frameWidth;
            $frameScaleY = $canvasHeight / $frameHeight;
            $frameScale = min($frameScaleX, $frameScaleY);

            $scaledFrameWidth = (int)($frameWidth * $frameScale);
            $scaledFrameHeight = (int)($frameHeight * $frameScale);

            // Create scaled frame
            $scaledFrame = imagecreatetruecolor($scaledFrameWidth, $scaledFrameHeight);
            imagealphablending($scaledFrame, false);
            imagesavealpha($scaledFrame, true);
            imagecopyresampled($scaledFrame, $frameImage, 0, 0, 0, 0, $scaledFrameWidth, $scaledFrameHeight, $frameWidth, $frameHeight);

            // Center the frame on the canvas
            $frameX = ($canvasWidth - $scaledFrameWidth) / 2;
            $frameY = ($canvasHeight - $scaledFrameHeight) / 2;

            // Overlay the frame with transparency
            imagealphablending($canvas, true);
            imagecopy($canvas, $scaledFrame, $frameX, $frameY, 0, 0, $scaledFrameWidth, $scaledFrameHeight);

            // Generate filename and save as JPG
            $combinedFilename = 'combined_framed_' . time() . '_' . Str::random(10) . '.jpg';
            $combinedPath = 'generated/' . $combinedFilename;

            // Save as JPG
            if (imagejpeg($canvas, Storage::disk('public')->path($combinedPath), 90)) {
                // Clean up memory
                imagedestroy($canvas);
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                imagedestroy($frameImage);
                imagedestroy($scaledFrame);

                \Log::info('Combined framed image saved as JPG: ' . $combinedPath);
                return $combinedPath;
            } else {
                \Log::error('Failed to save combined framed image');
                imagedestroy($canvas);
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                imagedestroy($frameImage);
                imagedestroy($scaledFrame);
                return null;
            }

        } catch (\Exception $e) {
            \Log::error('Error creating combined framed image: ' . $e->getMessage());
            return $this->createCombinedFramedImageWithIntervention($sadImagePath, $happyImagePath);
        }
    }

    /**
     * Create combined framed image using Intervention Image as fallback
     */
    private function createCombinedFramedImageWithIntervention($sadImagePath, $happyImagePath)
    {
        try {
            // Initialize Intervention Image Manager
            $manager = new ImageManager(new Driver());

            // Load both images
            $sadImage = $manager->read(Storage::disk('public')->path($sadImagePath));
            $happyImage = $manager->read(Storage::disk('public')->path($happyImagePath));

            // Get dimensions
            $sadWidth = $sadImage->width();
            $sadHeight = $sadImage->height();
            $happyWidth = $happyImage->width();
            $happyHeight = $happyImage->height();

            // Calculate combined canvas size (side by side with gap)
            $gap = 20;
            $canvasWidth = $sadWidth + $happyWidth + $gap;
            $canvasHeight = max($sadHeight, $happyHeight);

            // Create canvas with white background
            $canvas = $manager->create($canvasWidth, $canvasHeight, '#ffffff');

            // Place sad image on the left
            $canvas->place($sadImage, 'top-left', 0, 0);

            // Place happy image on the right
            $canvas->place($happyImage, 'top-left', $sadWidth + $gap, 0);

            // Load the frame image
            $framePath = public_path('05/photo_frame.png');
            if (!file_exists($framePath)) {
                \Log::error('Frame image not found for Intervention Image at: ' . $framePath);
                return null;
            }
            $frameImage = $manager->read($framePath);

            $frameWidth = $frameImage->width();
            $frameHeight = $frameImage->height();

            // Scale frame to fit the canvas
            $frameScaleX = $canvasWidth / $frameWidth;
            $frameScaleY = $canvasHeight / $frameHeight;
            $frameScale = min($frameScaleX, $frameScaleY);

            $scaledFrame = $frameImage->scale($frameScale);

            // Center the frame on the canvas
            $frameX = ($canvasWidth - $scaledFrame->width()) / 2;
            $frameY = ($canvasHeight - $scaledFrame->height()) / 2;

            // Overlay the frame
            $canvas->place($scaledFrame, 'top-left', $frameX, $frameY);

            // Generate filename and save as JPG
            $combinedFilename = 'combined_framed_' . time() . '_' . Str::random(10) . '.jpg';
            $combinedPath = 'generated/' . $combinedFilename;

            // Save as JPG with 90% quality
            $canvas->toJpeg(90)->save(Storage::disk('public')->path($combinedPath));

            \Log::info('Combined framed image saved with Intervention Image as JPG: ' . $combinedPath);
            return $combinedPath;

        } catch (\Exception $e) {
            \Log::error('Error creating combined framed image with Intervention Image: ' . $e->getMessage());
            return null;
        }
    }
}
