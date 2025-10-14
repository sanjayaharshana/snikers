<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\GeneratedImage;
use App\Services\TokenTrackingService;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessEmotionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $generatedImageId;
    protected $imagePath;
    protected $emotion;
    protected $phoneNumber;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct($generatedImageId, $imagePath, $emotion, $phoneNumber)
    {
        $this->generatedImageId = $generatedImageId;
        $this->imagePath = $imagePath;
        $this->emotion = $emotion;
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

            // Check if direct API mode is enabled (DB setting overrides env)
            if (\App\Models\Setting::getBool('direct_api', env('DIRECT_API', false))) {
                Log::info("Direct API mode is enabled, skipping queue job processing for emotion: {$this->emotion}");
                return;
            }

            // Find the generated image record
            $generatedImage = GeneratedImage::find($this->generatedImageId);


            if (!$generatedImage) {
                Log::error("GeneratedImage not found with ID: {$this->generatedImageId}");
                return;
            }

            // Update status to processing
            $this->updateJobStatus($generatedImage, 'processing');

            // Process with AI
            $processedImage = $this->processWithAI($this->imagePath, $this->emotion);

            if ($processedImage) {
                // Save processed image
                $filename = $this->emotion . '_' . time() . '_' . uniqid() . '.jpg';
                $processedPath = 'generated/' . $filename;

                Storage::disk('public')->put($processedPath, base64_decode($processedImage));

                // Update the database record
                $this->updateGeneratedImage($generatedImage, $processedPath);

                // Update status to completed
                $this->updateJobStatus($generatedImage, 'completed');

                Log::info("AI processing completed successfully for emotion: {$this->emotion}, Image ID: {$this->generatedImageId}");
            } else {
                // Use original image as fallback
                $this->handleProcessingFailure($generatedImage);
            }

    }

    /**
     * Process image with AI emotion editor
     */
    private function processWithAI($imagePath, $emotion = 'happy')
    {
        // Check if AI mode is disabled for testing (DB setting overrides env)
        if (\App\Models\Setting::getBool('ai_mode', env('AI_MODE', true)) === false) {
            Log::info('AI_MODE is disabled, using dummy processing for emotion: ' . $emotion);
            return $this->processWithDummyAI($imagePath, $emotion);
        }

        $fullPath = Storage::disk('public')->path($imagePath);

        // Option 1: Use AILabTools API (primary for Snickers campaign)
        if (\App\Models\Setting::getBool('use_ailabtools', env('USE_AILABTOOLS_API', true))) {
            return $this->processWithOriginalAPI($fullPath, $emotion);
        }

        // Option 2: Use Google Gemini Imagen API (alternative)
        if (\App\Models\Setting::getBool('use_gemini', env('USE_GOOGLE_GEMINI_API', false))) {
            return $this->processWithGoogleGemini($fullPath, $emotion);
        }

        // Fallback to AILabTools API
        return $this->processWithOriginalAPI($fullPath, $emotion);
    }

    /**
     * Process with Google Gemini API
     */
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
                    'X-goog-api-key' => env('GOOGLE_GEMINI_API_KEY'),
                    'Content-Type' => 'application/json'
                ])->timeout(400)->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image-preview:generateContent?key=' . env('GOOGLE_GEMINI_API_KEY'), [
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
                'generated_image_id' => $this->generatedImageId,
                'phone_number' => $this->phoneNumber,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Google Gemini API Response received');

                // Extract the generated image
                if (isset($data['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
                    return $data['candidates'][0]['content']['parts'][0]['inlineData']['data'];
                }

                // Check for alternative response format
                if (isset($data['candidates'][0]['content']['parts'][1]['inlineData']['data'])) {
                    return $data['candidates'][0]['content']['parts'][1]['inlineData']['data'];
                }
            } else {
                Log::error('Google Gemini API Error: ' . $response->status() . ' - ' . $response->body());
            }

            return null;


    }

    /**
     * Process with original API (AILabTools)
     */
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
            } else {
                Log::error('AILabTools API Error: ' . $response->status() . ' - ' . $response->body());
            }

            return null;
    }

    /**
     * Update the generated image record with processed image
     */
    private function updateGeneratedImage($generatedImage, $processedPath)
    {
        // Update the appropriate field based on emotion
        if ($this->emotion === 'sad') {
            $generatedImage->update(['sad_image' => $processedPath]);
        } elseif ($this->emotion === 'happy') {
            $generatedImage->update(['happy_image' => $processedPath]);
        }

        // Update emotion data
        $emotionData = json_decode($generatedImage->emotion_data, true) ?? [];
        $emotionData[$this->emotion . '_processed'] = true;
        $emotionData[$this->emotion . '_image_path'] = $processedPath;

        $generatedImage->update(['emotion_data' => json_encode($emotionData)]);
    }

    /**
     * Update job status in the database
     */
    private function updateJobStatus($generatedImage, $status)
    {
        $emotionData = json_decode($generatedImage->emotion_data, true) ?? [];
        $emotionData['job_status'] = $status;
        $emotionData['job_updated_at'] = now()->toISOString();

        $generatedImage->update(['emotion_data' => json_encode($emotionData)]);
    }

    /**
     * Handle processing failure by using original image
     */
    private function handleProcessingFailure($generatedImage)
    {
        if (!$generatedImage) {
            return;
        }

        Log::warning("AI processing failed for emotion: {$this->emotion}, using original image as fallback");

        // Use original image as fallback
        $originalImageData = Storage::disk('public')->get($this->imagePath);
        $fallbackImage = base64_encode($originalImageData);

        // Save fallback image
        $filename = $this->emotion . '_fallback_' . time() . '_' . uniqid() . '.jpg';
        $fallbackPath = 'generated/' . $filename;

        Storage::disk('public')->put($fallbackPath, base64_decode($fallbackImage));

        // Update the database record
        $this->updateGeneratedImage($generatedImage, $fallbackPath);

        // Update status to failed
        $this->updateJobStatus($generatedImage, 'failed');
    }

    /**
     * Process with dummy AI for testing (no API calls)
     */
    private function processWithDummyAI($imagePath, $emotion = 'happy')
    {
        Log::info("Using dummy AI processing for emotion: {$emotion}");

        // Simulate processing delay
        sleep(2);

        // Return original image as base64
        $originalImageData = file_get_contents($imagePath);
        return base64_encode($originalImageData);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessEmotionJob failed permanently: " . $exception->getMessage());

        $generatedImage = GeneratedImage::find($this->generatedImageId);
        if ($generatedImage) {
            $this->updateJobStatus($generatedImage, 'failed');
        }
    }
}
