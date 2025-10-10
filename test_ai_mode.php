<?php
/**
 * Test script to verify AI_MODE configuration
 * Run this script to test both dummy and real AI modes
 */

// Load Laravel environment
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== AI Mode Configuration Test ===\n\n";

// Test current AI_MODE setting
$aiMode = env('AI_MODE', true);
echo "Current AI_MODE setting: " . ($aiMode ? 'true (Real AI)' : 'false (Dummy AI)') . "\n";

// Test current DIRECT_API setting
$directApi = env('DIRECT_API', false);
echo "Current DIRECT_API setting: " . ($directApi ? 'true (Direct Processing)' : 'false (Queue Processing)') . "\n";

// Test environment variable loading
echo "Environment variables loaded: " . (env('APP_NAME') ? 'Yes' : 'No') . "\n";

// Test processing mode
if ($directApi) {
    echo "\n=== Direct API Mode ===\n";
    echo "✓ DIRECT_API is enabled\n";
    echo "✓ Images will be processed immediately\n";
    echo "✓ No queue jobs will be used\n";
    echo "✓ Results returned in real-time\n";
    echo "⚠ HTTP requests may block during processing\n";
} else {
    echo "\n=== Queue Processing Mode ===\n";
    echo "✓ DIRECT_API is disabled\n";
    echo "✓ Images will be processed via background jobs\n";
    echo "✓ Non-blocking user experience\n";
    echo "✓ Better for high-volume processing\n";
}

// Test dummy mode simulation
if (!$aiMode) {
    echo "\n=== Testing Dummy Mode ===\n";
    echo "✓ AI_MODE is disabled\n";
    echo "✓ No API calls will be made\n";
    echo "✓ Original images will be returned as 'processed'\n";
    echo "✓ 2-second delay will be simulated\n";
    echo "✓ Perfect for testing without billing charges\n";
} else {
    echo "\n=== Testing Real AI Mode ===\n";
    echo "✓ AI_MODE is enabled\n";
    echo "✓ Real API calls will be made\n";
    echo "✓ Actual emotion processing will occur\n";
    echo "⚠ Billing charges will apply\n";
}

// Check which AI services are configured
echo "\n=== AI Service Configuration ===\n";
$services = [
    'AILabTools (Primary)' => env('USE_AILABTOOLS_API', true),
    'Google Gemini' => env('USE_GOOGLE_GEMINI_API', false),
    'Replicate' => env('USE_REPLICATE_API', false),
    'Hugging Face' => env('USE_HUGGINGFACE_API', false),
    'Google Vision' => env('USE_GOOGLE_VISION_API', false),
];

foreach ($services as $service => $enabled) {
    $status = $enabled ? '✓ Enabled' : '✗ Disabled';
    echo "{$service}: {$status}\n";
}

echo "\n=== Instructions ===\n";
echo "To switch to dummy mode (no API charges):\n";
echo "1. Add 'AI_MODE=false' to your .env file\n";
echo "2. Restart your Laravel application\n";
echo "3. Run the campaign - no API calls will be made\n\n";

echo "To switch to direct API mode (immediate processing):\n";
echo "1. Add 'DIRECT_API=true' to your .env file\n";
echo "2. Restart your Laravel application\n";
echo "3. Run the campaign - images will be processed immediately\n\n";

echo "To switch to queue mode (background processing):\n";
echo "1. Add 'DIRECT_API=false' to your .env file\n";
echo "2. Restart your Laravel application\n";
echo "3. Run the campaign - images will be processed via background jobs\n\n";

echo "To switch to real AI mode:\n";
echo "1. Add 'AI_MODE=true' to your .env file\n";
echo "2. Configure your preferred AI service credentials\n";
echo "3. Restart your Laravel application\n";
echo "4. Run the campaign - real API calls will be made\n\n";

echo "=== Test Complete ===\n";
?>

