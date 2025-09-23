<?php

// Test script for Google Gemini API
// Run this with: php test_gemini_api.php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function testGoogleGeminiAPI() {
    $apiKey = $_ENV['GOOGLE_GEMINI_API_KEY'] ?? null;
    
    if (!$apiKey) {
        echo "❌ GOOGLE_GEMINI_API_KEY not found in .env file\n";
        return;
    }
    
    echo "🔑 API Key found: " . substr($apiKey, 0, 10) . "...\n";
    
    // Test with a simple text prompt first
    $response = Http::withHeaders([
        'X-goog-api-key' => $apiKey,
        'Content-Type' => 'application/json'
    ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image-preview:generateContent?key=' . $apiKey, [
        'contents' => [[
            'parts' => [
                ['text' => 'Generate a simple test image of a smiley face']
            ]
        ]],
        'generationConfig' => [
            'responseModalities' => ["IMAGE"]
        ]
    ]);
    
    echo "📡 API Response Status: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "✅ API call successful!\n";
        echo "📊 Response structure:\n";
        print_r(array_keys($data));
        
        if (isset($data['candidates'][0]['content']['parts'])) {
            echo "🎯 Found " . count($data['candidates'][0]['content']['parts']) . " parts in response\n";
        }
    } else {
        echo "❌ API call failed!\n";
        echo "Error: " . $response->body() . "\n";
    }
}

echo "🧪 Testing Google Gemini API...\n";
testGoogleGeminiAPI();
echo "🏁 Test completed!\n";
