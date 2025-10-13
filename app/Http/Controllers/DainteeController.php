<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\GeneratedImage;
use App\Jobs\ProcessEmotionJob;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Services\TokenTrackingService;

class DainteeController extends Controller
{
    public function index()
    {
        return view('daintee.campaign');
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

            // Create initial database record
            $generatedImage = GeneratedImage::create([
                'phone_number' => $request->phone_number,
                'original_image' => $originalPath,
                'emotion_data' => json_encode([
                    'original_processed' => true,
                    'sad_processed' => false,
                    'happy_processed' => false,
                    'job_status' => 'queued',
                    'job_updated_at' => now()->toISOString(),
                    'campaign_completed' => false
                ]),
            ]);

            // Check if direct API mode is enabled
            if (env('DIRECT_API', false)) {
                \Log::info('Direct API mode enabled, processing sad emotion immediately...');
                
                // Process sad emotion directly
                $sadImage = $this->processWithAI($originalPath, 'sad');
                
                if ($sadImage) {
                    // Save processed sad image
                    $sadFilename = 'sad_' . time() . '_' . Str::random(10) . '.jpg';
                    $sadPath = 'generated/' . $sadFilename;
                    Storage::disk('public')->put($sadPath, base64_decode($sadImage));
                    
                    // Update database record
                    $generatedImage->update(['sad_image' => $sadPath]);
                    
                    // Update emotion data
                    $emotionData = json_decode($generatedImage->emotion_data, true);
                    $emotionData['sad_processed'] = true;
                    $emotionData['sad_image_path'] = $sadPath;
                    $emotionData['job_status'] = 'completed';
                    $emotionData['job_updated_at'] = now()->toISOString();
                    $generatedImage->update(['emotion_data' => json_encode($emotionData)]);
                    
                    return response()->json([
                        'success' => true,
                        'phone_number' => $request->phone_number,
                        'original_image_url' => Storage::url($originalPath),
                        'sad_image_url' => Storage::url($sadPath),
                        'generated_image_id' => $generatedImage->id,
                        'job_status' => 'completed',
                        'message' => 'Sad emotion processed successfully using direct API.'
                    ]);
                } else {
                    \Log::warning('Direct API processing failed for sad emotion');
                    return response()->json([
                        'success' => false,
                        'message' => 'Direct API processing failed. Please try again.'
                    ], 500);
                }
            } else {
                // Dispatch AI processing job for sad emotion (queue mode)
                \Log::info('Queue mode enabled, dispatching AI processing job for sad emotion...');
                ProcessEmotionJob::dispatch($generatedImage->id, $originalPath, 'sad', $request->phone_number);

                return response()->json([
                    'success' => true,
                    'phone_number' => $request->phone_number,
                    'original_image_url' => Storage::url($originalPath),
                    'generated_image_id' => $generatedImage->id,
                    'job_status' => 'queued',
                    'message' => 'Sad emotion processing job has been queued. Please check status later.'
                ]);
            }


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

            // Find the existing generated image record
            $generatedImage = GeneratedImage::where('phone_number', $request->phone_number)
                ->whereNotNull('sad_image')
                ->latest()
                ->first();

            if (!$generatedImage) {
                return response()->json([
                    'success' => false,
                    'message' => 'No previous selfie found. Please process first selfie first.'
                ], 400);
            }

            // Save the second selfie temporarily
            $secondSelfieFilename = 'second_selfie_' . time() . '_' . Str::random(10) . '.jpg';
            $secondSelfiePath = 'temp/' . $secondSelfieFilename;
            Storage::disk('public')->put($secondSelfiePath, $imageData);

            // Check if direct API mode is enabled
            if (env('DIRECT_API', false)) {
                \Log::info('Direct API mode enabled, processing happy emotion immediately...');
                
                // Process happy emotion directly
                $happyImage = $this->processWithAI($secondSelfiePath, 'happy');
                
                if ($happyImage) {
                    // Save processed happy image
                    $happyFilename = 'happy_' . time() . '_' . Str::random(10) . '.jpg';
                    $happyPath = 'generated/' . $happyFilename;
                    Storage::disk('public')->put($happyPath, base64_decode($happyImage));
                    
                    // Update database record
                    $generatedImage->update(['happy_image' => $happyPath]);
                    
                    // Update emotion data
                    $emotionData = json_decode($generatedImage->emotion_data, true);
                    $emotionData['happy_processed'] = true;
                    $emotionData['happy_image_path'] = $happyPath;
                    $emotionData['job_status'] = 'completed';
                    $emotionData['job_updated_at'] = now()->toISOString();
                    $emotionData['campaign_completed'] = true;
                    $generatedImage->update(['emotion_data' => json_encode($emotionData)]);
                    
                    return response()->json([
                        'success' => true,
                        'phone_number' => $request->phone_number,
                        'original_image_url' => Storage::url($secondSelfiePath),
                        'happy_image_url' => Storage::url($happyPath),
                        'generated_image_id' => $generatedImage->id,
                        'job_status' => 'completed',
                        'campaign_completed' => true,
                        'message' => 'Happy emotion processed successfully using direct API. Campaign completed!'
                    ]);
                } else {
                    \Log::warning('Direct API processing failed for happy emotion');
                    return response()->json([
                        'success' => false,
                        'message' => 'Direct API processing failed. Please try again.'
                    ], 500);
                }
            } else {
                // Dispatch AI processing job for happy emotion (queue mode)
                \Log::info('Queue mode enabled, dispatching AI processing job for happy emotion...');
                ProcessEmotionJob::dispatch($generatedImage->id, $secondSelfiePath, 'happy', $request->phone_number);

                return response()->json([
                    'success' => true,
                    'phone_number' => $request->phone_number,
                    'original_image_url' => Storage::url($secondSelfiePath),
                    'generated_image_id' => $generatedImage->id,
                    'job_status' => 'queued',
                    'message' => 'Happy emotion processing job has been queued. Please check status later.'
                ]);
            }

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

        // Option 1: Use AILabTools API (primary for Daintee campaign)
        if (env('USE_AILABTOOLS_API', true)) {
            return $this->processWithOriginalAPI($fullPath, $emotion);
        }

        // Option 2: Use Google Gemini Imagen API (alternative)
        if (env('USE_GOOGLE_GEMINI_API', false)) {
            return $this->processWithGoogleGemini($fullPath, $emotion);
        }

        // Fallback to AILabTools API
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

            // Log token usage
            TokenTrackingService::logApiCall([
                'api_service' => 'google_gemini',
                'operation_type' => 'emotion_processing',
                'emotion' => $emotion,
                'model_used' => 'gemini-2.5-flash-image-preview',
                'request_data' => [
                    'prompt' => $prompt,
                    'image_size' => strlen($imageData),
                ],
                'response_data' => $response->json(),
                'success' => $response->successful(),
                'error_message' => $response->successful() ? null : $response->body(),
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

        // Log token usage
        TokenTrackingService::logApiCall([
            'api_service' => 'ailabtools',
            'operation_type' => 'emotion_processing',
            'emotion' => $emotion,
            'model_used' => 'emotion-editor',
            'request_data' => [
                'service_choice' => $serviceChoice,
                'image_path' => basename($imagePath),
            ],
            'response_data' => $response->json(),
            'success' => $response->successful(),
            'error_message' => $response->successful() ? null : $response->body(),
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
            'sad_image_url' => $latestImage->sad_image ? Storage::url($latestImage->sad_image) : null,
            'happy_image_url' => $latestImage->happy_image ? Storage::url($latestImage->happy_image) : null,
            'photo_frame_url' => $latestImage->photo_frame_path ? Storage::url($latestImage->photo_frame_path) : null,
            'framed_image_url' => $latestImage->framed_image ? Storage::url($latestImage->framed_image) : null,
            'generated_image_id' => $latestImage->id,
            'emotion_data' => $latestImage->emotion_data,
            'message' => 'Test data retrieved successfully'
        ]);
    }

    /**
     * Check the status of AI processing jobs
     */
    public function checkJobStatus(Request $request)
    {
        $request->validate([
            'generated_image_id' => 'required|integer|exists:generated_images,id'
        ]);

        $generatedImage = GeneratedImage::find($request->generated_image_id);

        if (!$generatedImage) {
            return response()->json([
                'success' => false,
                'message' => 'Generated image not found'
            ], 404);
        }

        $emotionData = json_decode($generatedImage->emotion_data, true) ?? [];
        $jobStatus = $emotionData['job_status'] ?? 'unknown';
        $jobUpdatedAt = $emotionData['job_updated_at'] ?? null;

        $response = [
            'success' => true,
            'generated_image_id' => $generatedImage->id,
            'phone_number' => $generatedImage->phone_number,
            'job_status' => $jobStatus,
            'job_updated_at' => $jobUpdatedAt,
            'original_image_url' => Storage::url($generatedImage->original_image),
            'sad_image_url' => $generatedImage->sad_image ? Storage::url($generatedImage->sad_image) : null,
            'happy_image_url' => $generatedImage->happy_image ? Storage::url($generatedImage->happy_image) : null,
            'emotion_data' => $emotionData
        ];

        // Add completion status if both emotions are processed
        if ($emotionData['sad_processed'] ?? false && $emotionData['happy_processed'] ?? false) {
            $response['campaign_completed'] = true;
            $response['message'] = 'Campaign completed successfully!';
        } elseif ($jobStatus === 'completed') {
            $response['message'] = 'Processing completed for current emotion';
        } elseif ($jobStatus === 'processing') {
            $response['message'] = 'AI processing in progress...';
        } elseif ($jobStatus === 'queued') {
            $response['message'] = 'Job queued, waiting to start processing...';
        } elseif ($jobStatus === 'failed') {
            $response['message'] = 'Processing failed, using fallback images';
        } else {
            $response['message'] = 'Unknown status';
        }

        return response()->json($response);
    }

    /**
     * Get all processing jobs for a phone number
     */
    public function getProcessingJobs(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20'
        ]);

        $generatedImages = GeneratedImage::where('phone_number', $request->phone_number)
            ->orderBy('created_at', 'desc')
            ->get();

        $jobs = $generatedImages->map(function ($image) {
            $emotionData = json_decode($image->emotion_data, true) ?? [];
            
            return [
                'id' => $image->id,
                'phone_number' => $image->phone_number,
                'job_status' => $emotionData['job_status'] ?? 'unknown',
                'job_updated_at' => $emotionData['job_updated_at'] ?? null,
                'sad_processed' => $emotionData['sad_processed'] ?? false,
                'happy_processed' => $emotionData['happy_processed'] ?? false,
                'campaign_completed' => $emotionData['campaign_completed'] ?? false,
                'original_image_url' => Storage::url($image->original_image),
                'sad_image_url' => $image->sad_image ? Storage::url($image->sad_image) : null,
                'happy_image_url' => $image->happy_image ? Storage::url($image->happy_image) : null,
                'created_at' => $image->created_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'phone_number' => $request->phone_number,
            'jobs' => $jobs,
            'total_jobs' => $jobs->count()
        ]);
    }

    /**
     * Generate photo frame with split-screen layout: sad on top, happy on bottom
     * Following the Daintee promotional design with branding elements
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

            // Add purple border (Daintee brand color)
            $purple = imagecolorallocate($photoFrame, 128, 0, 128); // Daintee purple
            imagerectangle($photoFrame, 0, 0, $frameWidth - 1, $frameHeight - 1, $purple);

            // Copy sad image to the top half
            $sadX = ($frameWidth - $sadWidth) / 2; // Center horizontally
            imagecopy($photoFrame, $sadImage, $sadX, 5, 0, 0, $sadWidth, $sadHeight);

            // Copy happy image to the bottom half
            $happyX = ($frameWidth - $happyWidth) / 2; // Center horizontally
            $happyY = $sadHeight + 15; // 15px gap from sad image
            imagecopy($photoFrame, $happyImage, $happyX, $happyY, 0, 0, $happyWidth, $happyHeight);

            // Add Daintee branding text
            $this->addDainteeBranding($photoFrame, $frameWidth, $frameHeight);

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

            // Add purple border (Daintee purple)
            $photoFrame->drawRectangle(0, 0, function ($draw) use ($frameWidth, $frameHeight) {
                $draw->size($frameWidth - 1, $frameHeight - 1);
                $draw->border(3, '#800080');
            });

            // Copy sad image to the top half
            $sadX = ($frameWidth - $sadWidth) / 2; // Center horizontally
            $photoFrame->place($sadImage, 'top-left', $sadX, 5);

            // Copy happy image to the bottom half
            $happyX = ($frameWidth - $happyWidth) / 2; // Center horizontally
            $happyY = $sadHeight + 15; // 15px gap from sad image
            $photoFrame->place($happyImage, 'top-left', $happyX, $happyY);

            // Add Daintee branding text
            $this->addDainteeBrandingIntervention($photoFrame, $frameWidth, $frameHeight);

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
     * Add Daintee branding elements to the photo frame using Intervention Image
     */
    private function addDainteeBrandingIntervention($image, $width, $height)
    {
        try {
            // Add Daintee logo area at the top
            $logoHeight = 60;

            // Create a purple rectangle for the logo background
            $logoBackground = $image->newImage($width, $logoHeight, '#800080');
            $image->place($logoBackground, 'top-left', 0, 0);

            // Add silver border inside
            $image->drawRectangle(2, 2, function ($draw) use ($width, $logoHeight) {
                $draw->size($width - 3, $logoHeight - 3);
                $draw->border(2, '#C0C0C0');
            });

            // Add "DAINTEE" text (simplified - using basic text)
            $image->text('DAINTEE', $width / 2, $logoHeight / 2, function ($font) {
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

            // "HUNGRY" in gold
            $image->text("HUNGRY", $width / 2, $sloganY + 30, function ($font) {
                $font->filename('Arial');
                $font->size(18);
                $font->color('#FFD700');
                $font->align('center');
            });

        } catch (\Exception $e) {
            \Log::error('Error adding Daintee branding: ' . $e->getMessage());
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
    <title>Daintee Photo Frame</title>
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
            border: 8px solid #800080;
            border-radius: 20px;
            padding: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .daintee-logo {
            background: #800080;
            color: white;
            text-align: center;
            padding: 15px;
            margin: -20px -20px 20px -20px;
            border-radius: 12px 12px 0 0;
            font-size: 24px;
            font-weight: bold;
            border: 3px solid #C0C0C0;
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
            color: #FFD700;
            font-size: 20px;
        }

        .daintee-bar {
            text-align: center;
            margin-top: 15px;
        }

        .daintee-bar img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="photo-frame">
        <div class="daintee-logo">DAINTEE</div>

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

        <div class="daintee-bar">
            <img src="/07/DAINTEE_BAR.png" alt="Daintee Bar">
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Add Daintee branding elements to the photo frame
     */
    private function addDainteeBranding($image, $width, $height)
    {
        if (!function_exists('imagecreate')) {
            return;
        }

        // Colors
        $purple = imagecolorallocate($image, 128, 0, 128); // Daintee purple
        $silver = imagecolorallocate($image, 192, 192, 192); // Daintee silver
        $white = imagecolorallocate($image, 255, 255, 255);
        $gold = imagecolorallocate($image, 255, 215, 0); // For "HUNGRY" text

        // Add Daintee logo area at the top
        $logoHeight = 60;
        imagefilledrectangle($image, 0, 0, $width, $logoHeight, $purple);

        // Add silver border inside
        imagerectangle($image, 2, 2, $width - 3, $logoHeight - 3, $silver);

        // Add "DAINTEE" text (simplified - using built-in font)
        $font = 5; // Built-in font
        $text = "DAINTEE";
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

        // "HUNGRY" in gold
        $hungryText = "HUNGRY";
        $hungryWidth = imagefontwidth($sloganFont) * strlen($hungryText);
        $hungryX = ($width - $hungryWidth) / 2;
        imagestring($image, $sloganFont, $hungryX, $sloganY + 30, $hungryText, $gold);
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

            // Add Daintee branding text
            $this->addDainteeBrandingIntervention($baseFrame, $frameWidth, $frameHeight);

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
     * Add Daintee header to framed image using GD
     */
    private function addDainteeHeader($image, $width)
    {
        if (!function_exists('imagecreate')) {
            return;
        }

        // Colors
        $purple = imagecolorallocate($image, 128, 0, 128); // Daintee purple
        $silver = imagecolorallocate($image, 192, 192, 192); // Daintee silver
        $white = imagecolorallocate($image, 255, 255, 255);

        // Add Daintee logo area at the top
        $logoHeight = 60;
        imagefilledrectangle($image, 0, 0, $width, $logoHeight, $purple);

        // Add silver border inside
        imagerectangle($image, 2, 2, $width - 3, $logoHeight - 3, $silver);

        // Add "DAINTEE" text
        $font = 5; // Built-in font
        $text = "DAINTEE";
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
        $gold = imagecolorallocate($image, 255, 215, 0);
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
        imagestring($image, $font, $happyLabelX, $happyY - 20, $happyLabel, $gold);
    }

    /**
     * Add Daintee slogan using GD
     */
    private function addDainteeSlogan($image, $width, $height)
    {
        if (!function_exists('imagecreate')) {
            return;
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $gold = imagecolorallocate($image, 255, 215, 0);
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

        // "HUNGRY" in gold
        $hungryText = "HUNGRY";
        $hungryWidth = imagefontwidth($font) * strlen($hungryText);
        $hungryX = ($width - $hungryWidth) / 2;
        imagestring($image, $font, $hungryX, $sloganY + 30, $hungryText, $gold);
    }

    /**
     * Add Daintee header to framed image using Intervention Image
     */
    private function addDainteeHeaderIntervention($image, $width)
    {
        try {
            // Add Daintee logo area at the top
            $logoHeight = 60;

            // Create a purple rectangle for the logo background
            $logoBackground = $image->newImage($width, $logoHeight, '#800080');
            $image->place($logoBackground, 'top-left', 0, 0);

            // Add silver border inside
            $image->drawRectangle(2, 2, function ($draw) use ($width, $logoHeight) {
                $draw->size($width - 3, $logoHeight - 3);
                $draw->border(2, '#C0C0C0');
            });

            // Add "DAINTEE" text
            $image->text('DAINTEE', $width / 2, $logoHeight / 2, function ($font) {
                $font->filename('Arial');
                $font->size(24);
                $font->color('#FFFFFF');
                $font->align('center');
                $font->valign('middle');
            });

        } catch (\Exception $e) {
            \Log::error('Error adding Daintee header: ' . $e->getMessage());
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
                $font->color('#FFD700');
                $font->align('center');
            });

        } catch (\Exception $e) {
            \Log::error('Error adding image labels: ' . $e->getMessage());
        }
    }

    /**
     * Add Daintee slogan using Intervention Image
     */
    private function addDainteeSloganIntervention($image, $width, $height)
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

            // "HUNGRY" in gold
            $image->text("HUNGRY", $width / 2, $sloganY + 30, function ($font) {
                $font->filename('Arial');
                $font->size(18);
                $font->color('#FFD700');
                $font->align('center');
            });

        } catch (\Exception $e) {
            \Log::error('Error adding Daintee slogan: ' . $e->getMessage());
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
    <title>Daintee Framed Image</title>
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
            border: 12px solid #800080;
            border-radius: 25px;
            padding: 0;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
        }

        .daintee-header {
            background: linear-gradient(45deg, #800080, #9932CC);
            color: white;
            text-align: center;
            padding: 20px;
            position: relative;
            border-bottom: 4px solid #C0C0C0;
        }

        .daintee-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            pointer-events: none;
        }

        .daintee-logo {
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
            color: #FFD700;
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
            background: linear-gradient(90deg, #800080, #FFD700, #800080);
        }

        .slogan-text {
            font-size: 20px;
            font-weight: bold;
            line-height: 1.4;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .hungry-highlight {
            color: #FFD700;
            font-size: 24px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }

        .daintee-product {
            text-align: center;
            padding: 20px;
            background: #f0f0f0;
        }

        .daintee-product img {
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

            .daintee-logo {
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
        <div class="daintee-header">
            <div class="daintee-logo">DAINTEE</div>
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

        <div class="daintee-product">
            <img src="/07/DAINTEE_BAR.png" alt="Daintee Bar">
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

            // Load the Daintee branded frame template as base canvas
            $framePath = public_path('daintee_frame.png');
            if (!file_exists($framePath)) {
                \Log::error('Daintee frame template not found at: ' . $framePath);
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                return null;
            }

            $frameImage = imagecreatefrompng($framePath);
            if (!$frameImage) {
                \Log::error('Failed to load Daintee frame template');
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                return null;
            }

            // Use frame template dimensions as canvas size
            $canvasWidth = imagesx($frameImage);   // 1184px
            $canvasHeight = imagesy($frameImage);  // 2092px

            // Define image areas in the template (estimated positions)
            $sadAreaY = 100;    // Top area for sad image (moved down a bit)
            $happyAreaY = 1030;  // Bottom area for happy image (adjusted accordingly)

            // Scale images to fit template areas
            $templateImageWidth = 800;  // Approximate width for template image areas
            $templateImageHeight = 600; // Approximate height for template image areas

            // Resize sad image to fit template area
            $sadResized = imagecreatetruecolor($templateImageWidth, $templateImageHeight);
            imagecopyresampled($sadResized, $sadImage, 0, 0, 0, 0, $templateImageWidth, $templateImageHeight, $sadWidth, $sadHeight);

            // Resize happy image to fit template area
            $happyResized = imagecreatetruecolor($templateImageWidth, $templateImageHeight);
            imagecopyresampled($happyResized, $happyImage, 0, 0, 0, 0, $templateImageWidth, $templateImageHeight, $happyWidth, $happyHeight);

            // Center images horizontally in template areas
            $imageX = ($canvasWidth - $templateImageWidth) / 2;

            // Create final canvas from Daintee frame template
            $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);

            // Place sad image in template sad area (behind frame)
            imagecopy($canvas, $sadResized, $imageX, $sadAreaY, 0, 0, $templateImageWidth, $templateImageHeight);

            // Place happy image in template happy area (behind frame)
            imagecopy($canvas, $happyResized, $imageX, $happyAreaY, 0, 0, $templateImageWidth, $templateImageHeight);

            // Overlay Daintee frame template on top (highest z-index)
            imagealphablending($canvas, true);
            imagecopy($canvas, $frameImage, 0, 0, 0, 0, $canvasWidth, $canvasHeight);

            // Clean up temporary images
            imagedestroy($sadResized);
            imagedestroy($happyResized);

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

                \Log::info('Combined framed image saved as JPG: ' . $combinedPath);
                return $combinedPath;
            } else {
                \Log::error('Failed to save combined framed image');
                imagedestroy($canvas);
                imagedestroy($sadImage);
                imagedestroy($happyImage);
                imagedestroy($frameImage);
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

            // Load the Daintee branded frame template as base canvas
            $framePath = public_path('daintee_frame.png');
            if (!file_exists($framePath)) {
                \Log::error('Daintee frame template not found for Intervention Image at: ' . $framePath);
                return null;
            }

            $frameTemplate = $manager->read($framePath);
            $canvasWidth = $frameTemplate->width();   // 1184px
            $canvasHeight = $frameTemplate->height();  // 2092px

            // Define image areas in the template (estimated positions)
            $sadAreaY = 250;    // Top area for sad image (moved down a bit)
            $happyAreaY = 1030;  // Bottom area for happy image (adjusted accordingly)

            // Scale images to fit template areas
            $templateImageWidth = 800;  // Approximate width for template image areas
            $templateImageHeight = 600; // Approximate height for template image areas

            // Resize sad image to fit template area
            $sadResized = $sadImage->resize($templateImageWidth, $templateImageHeight);

            // Resize happy image to fit template area
            $happyResized = $happyImage->resize($templateImageWidth, $templateImageHeight);

            // Center images horizontally in template areas
            $imageX = ($canvasWidth - $templateImageWidth) / 2;

            // Create canvas and place images first (behind frame)
            $canvas = $manager->create($canvasWidth, $canvasHeight, '#ffffff');

            // Place sad image in template sad area (behind frame)
            $canvas->place($sadResized, 'top-left', $imageX, $sadAreaY);

            // Place happy image in template happy area (behind frame)
            $canvas->place($happyResized, 'top-left', $imageX, $happyAreaY);

            // Overlay Daintee frame template on top (highest z-index)
            $canvas->place($frameTemplate, 'top-left', 0, 0);

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
