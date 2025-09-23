<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\GeneratedImage;
use Illuminate\Support\Str;

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

        try {
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

            if ($sadImage && $happyImage) {
                // Save processed images
                $sadFilename = 'sad_' . time() . '_' . Str::random(10) . '.jpg';
                $happyFilename = 'happy_' . time() . '_' . Str::random(10) . '.jpg';
                $sadPath = 'generated/' . $sadFilename;
                $happyPath = 'generated/' . $happyFilename;

                Storage::disk('public')->put($sadPath, base64_decode($sadImage));
                Storage::disk('public')->put($happyPath, base64_decode($happyImage));

                // Save to database
                $generatedImage = GeneratedImage::create([
                    'phone_number' => $request->phone_number,
                    'original_image' => $tempPath,
                    'processed_image' => $sadPath, // Store sad as primary
                    'emotion_data' => json_encode([
                        'sad_image' => $sadPath,
                        'happy_image' => $happyPath,
                        'both_processed' => true
                    ]),
                ]);

                return response()->json([
                    'success' => true,
                    'phone_number' => $request->phone_number,
                    'original_image_url' => Storage::url($tempPath),
                    'sad_image_url' => Storage::url($sadPath),
                    'happy_image_url' => Storage::url($happyPath),
                    'generated_image_id' => $generatedImage->id,
                    'message' => 'Both emotions processed successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to process images with AI'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing selfie: ' . $e->getMessage()
            ], 500);
        }
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

            // Generate unique filename
            $filename = 'first_selfie_' . time() . '_' . Str::random(10) . '.jpg';
            $tempPath = 'temp/' . $filename;

            // Save temporary image
            Storage::disk('public')->put($tempPath, $imageData);

            // Process with AI emotion editor for SAD emotion only
            $sadImage = $this->processWithAI($tempPath, 'sad');

            if ($sadImage) {
                // Save processed image
                $sadFilename = 'first_sad_' . time() . '_' . Str::random(10) . '.jpg';
                $sadPath = 'generated/' . $sadFilename;

                Storage::disk('public')->put($sadPath, base64_decode($sadImage));

                return response()->json([
                    'success' => true,
                    'phone_number' => $request->phone_number,
                    'original_image_url' => Storage::url($tempPath),
                    'sad_image_url' => Storage::url($sadPath),
                    'message' => 'Sad emotion processed successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to process sad emotion with AI'
            ], 500);


    }

    private function processWithAI($imagePath, $emotion = 'happy')
    {
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
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image-preview:generateContent?key={'.env('GOOGLE_GEMINI_API_KEY').'}', [
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

                // Extract the generated image
                if (isset($data['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
                    return $data['candidates'][0]['content']['parts'][0]['inlineData']['data'];
                }
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

    public function getImage($filename)
    {
        $path = 'generated/' . $filename;
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }
        return response()->json(['error' => 'Image not found'], 404);
    }
}
