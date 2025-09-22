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

    private function processWithAI($imagePath, $emotion = 'happy')
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            
            // Different service choices for different emotions
            $serviceChoice = $emotion === 'sad' ? '15' : '12'; // Adjust based on API documentation
            
            $response = Http::withHeaders([
                'ailabapi-api-key' => env('AILABTOOLS_API_KEY', 'imff7TwAtdh9xZku1PWRCMjN9CJqLFvr5BevQyKI3ZzEy6DTOrXVI8S4hWgo146U')
            ])->attach('image_target', file_get_contents($fullPath), basename($fullPath))
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
        } catch (\Exception $e) {
            \Log::error('AI processing error for ' . $emotion . ': ' . $e->getMessage());
            return null;
        }
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
