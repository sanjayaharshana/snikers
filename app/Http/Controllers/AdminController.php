<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\GeneratedImage;
use Illuminate\Support\Facades\Hash;
use App\Jobs\ProcessEmotionJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Services\TokenTrackingService;

class AdminController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // Simple admin authentication (you can enhance this later)
        if ($credentials['email'] === 'admin@snickers.com' && $credentials['password'] === 'admin123') {
            session(['admin_logged_in' => true]);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout()
    {
        session()->forget('admin_logged_in');
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $images = GeneratedImage::orderBy('created_at', 'desc')->paginate(10);
        $tokenStats = TokenTrackingService::getDashboardStats();

        return view('admin.dashboard', compact('images', 'tokenStats'));
    }

    public function show($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);
        return view('admin.show', compact('image'));
    }

    public function edit($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);
        return view('admin.edit', compact('image'));
    }

    public function update(Request $request, $id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'phone_number' => 'required|string|max:20',
        ]);

        $image = GeneratedImage::findOrFail($id);
        $image->update([
            'phone_number' => $request->phone_number,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Image updated successfully!');
    }

    public function destroy($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);

        // Delete files from storage
        if (Storage::disk('public')->exists($image->original_image)) {
            Storage::disk('public')->delete($image->original_image);
        }
        if (Storage::disk('public')->exists($image->sad_image)) {
            Storage::disk('public')->delete($image->sad_image);
        }
        if (Storage::disk('public')->exists($image->happy_image)) {
            Storage::disk('public')->delete($image->happy_image);
        }

        $image->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Image deleted successfully!');
    }

    public function download($id, $type)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);

        $filePath = match($type) {
            'original' => $image->original_image,
            'sad' => $image->sad_image,
            'happy' => $image->happy_image,
            default => null
        };

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        }

        return back()->with('error', 'File not found');
    }

    public function generateHappy($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);

        // Update emotion_data to reflect queued job status
        $emotionData = json_decode($image->emotion_data, true) ?? [];
        $emotionData['job_status'] = 'queued';
        $emotionData['job_updated_at'] = now()->toISOString();
        $emotionData['happy_processed'] = $emotionData['happy_processed'] ?? false;

        $image->update(['emotion_data' => json_encode($emotionData)]);

        if(env('DIRECT_API', false)){

            $generatedImage = GeneratedImage::find($id);
            $fullPath = Storage::disk('public')->path($generatedImage->original_image);

            if (env('USE_AILABTOOLS_API', true)) {
                $processedImage =  $this->processWithOriginalAPI($fullPath, 'happy',$generatedImage->id, $generatedImage->phone_number);
            }

            // Option 2: Use Google Gemini Imagen API (alternative)
            if (env('USE_GOOGLE_GEMINI_API', false)) {
                $processedImage =  $this->processWithGoogleGemini($fullPath, 'happy',$generatedImage->id, $generatedImage->phone_number);
            }

            if ($processedImage) {
                // Save processed image
                $filename = 'happy' . '_' . time() . '_' . uniqid() . '.jpg';
                $processedPath = 'generated/' . $filename;

                Storage::disk('public')->put($processedPath, base64_decode($processedImage));

                // Update the database record
                $this->updateGeneratedImage($generatedImage, $processedPath, 'happy');

            } else {
                // Use original image as fallback
               return 'null';
            }
        }

        // Dispatch the happy emotion processing job using the original image
        ProcessEmotionJob::dispatch($image->id, $image->original_image, 'happy', $image->phone_number);

        return redirect()->route('admin.dashboard')->with('success', 'Happy photo generation has been queued for Image ID '.$image->id);
    }




    private function processWithOriginalAPI($imagePath, $emotion, $imageId, $phoneNumber)
    {
        try {
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
                'generated_image_id' => $imageId,
                'phone_number' => $phoneNumber,
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
        } catch (\Exception $e) {
            Log::error('AILabTools API error: ' . $e->getMessage());
            return null;
        }
    }




    public function queueJobs()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        // Get jobs from the jobs table
        $jobs = DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get failed jobs
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(20);

        // Get queue statistics
        $stats = [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'total_processed' => DB::table('jobs')->whereNotNull('reserved_at')->count(),
        ];

        // Process jobs to handle timestamp conversion
        $jobs->getCollection()->transform(function ($job) {
            // Convert Unix timestamps to Carbon instances
            if (is_numeric($job->created_at)) {
                $job->created_at = \Carbon\Carbon::createFromTimestamp($job->created_at);
            } else {
                $job->created_at = \Carbon\Carbon::parse($job->created_at);
            }

            if (is_numeric($job->available_at)) {
                $job->available_at = \Carbon\Carbon::createFromTimestamp($job->available_at);
            } else {
                $job->available_at = \Carbon\Carbon::parse($job->available_at);
            }

            return $job;
        });

        // Process failed jobs to handle timestamp conversion
        $failedJobs->getCollection()->transform(function ($job) {
            // Convert Unix timestamps to Carbon instances
            if (is_numeric($job->failed_at)) {
                $job->failed_at = \Carbon\Carbon::createFromTimestamp($job->failed_at);
            } else {
                $job->failed_at = \Carbon\Carbon::parse($job->failed_at);
            }

            return $job;
        });

        return view('admin.queue-jobs', compact('jobs', 'failedJobs', 'stats'));
    }

    public function retryJob($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        try {
            // Retry a failed job
            \Artisan::call('queue:retry', ['id' => $id]);
            return redirect()->route('admin.queue-jobs')->with('success', 'Job retried successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.queue-jobs')->with('error', 'Failed to retry job: ' . $e->getMessage());
        }
    }

    public function deleteJob($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        try {
            // Delete a failed job
            DB::table('failed_jobs')->where('id', $id)->delete();
            return redirect()->route('admin.queue-jobs')->with('success', 'Job deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.queue-jobs')->with('error', 'Failed to delete job: ' . $e->getMessage());
        }
    }

    public function clearQueue()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        try {
            // Clear all pending jobs
            DB::table('jobs')->truncate();
            return redirect()->route('admin.queue-jobs')->with('success', 'Queue cleared successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.queue-jobs')->with('error', 'Failed to clear queue: ' . $e->getMessage());
        }
    }


    public function framedImage($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);

        return view('admin.frame_generate_image', compact('image'));
    }


    private function processWithGoogleGemini($imagePath, $emotion, $imageId, $phoneNumber)
    {
        try {
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
                'generated_image_id' => $imageId,
                'phone_number' => $phoneNumber,
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

        } catch (\Exception $e) {
            Log::error('Google Gemini API error: ' . $e->getMessage());
            return null;
        }
    }

    private function updateGeneratedImage($generatedImage, $processedPath, $emotion)
    {
        // Update the appropriate field based on emotion
        if ($emotion === 'sad') {
            $generatedImage->update(['sad_image' => $processedPath]);
        } elseif ($emotion === 'happy') {
            $generatedImage->update(['happy_image' => $processedPath]);
        }

        // Update emotion data
        $emotionData = json_decode($generatedImage->emotion_data, true) ?? [];
        $emotionData[$emotion . '_processed'] = true;
        $emotionData[$emotion . '_image_path'] = $processedPath;

        $generatedImage->update(['emotion_data' => json_encode($emotionData)]);
    }
}
