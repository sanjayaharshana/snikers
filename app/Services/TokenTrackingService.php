<?php

namespace App\Services;

use App\Models\TokenUsage;
use Illuminate\Support\Facades\Log;

class TokenTrackingService
{
    /**
     * Log token usage for AI API calls
     */
    public static function logApiCall($data)
    {
        try {
            // Calculate estimated tokens and cost based on API service
            $data = self::calculateTokenUsage($data);
            
            return TokenUsage::logUsage($data);
        } catch (\Exception $e) {
            Log::error('Failed to log token usage: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Calculate estimated tokens and cost based on API service
     */
    private static function calculateTokenUsage($data)
    {
        $apiService = $data['api_service'] ?? 'unknown';
        $operationType = $data['operation_type'] ?? 'unknown';
        
        // Default values
        $data['tokens_used'] = $data['tokens_used'] ?? 0;
        $data['cost_usd'] = $data['cost_usd'] ?? 0;
        
        // Calculate based on API service and operation
        switch ($apiService) {
            case 'ailabtools':
                $data = self::calculateAILabToolsUsage($data);
                break;
            case 'google_gemini':
                $data = self::calculateGoogleGeminiUsage($data);
                break;
            case 'replicate':
                $data = self::calculateReplicateUsage($data);
                break;
            case 'huggingface':
                $data = self::calculateHuggingFaceUsage($data);
                break;
            default:
                // Default estimation for unknown services
                $data['tokens_used'] = 1000; // Estimated tokens
                $data['cost_usd'] = 0.01; // Estimated cost
                break;
        }
        
        return $data;
    }
    
    /**
     * Calculate usage for Google Gemini API
     */
    private static function calculateGoogleGeminiUsage($data)
    {
        // Google Gemini pricing (as of 2024)
        // Image generation: ~$0.02 per image
        // Text processing: ~$0.00025 per 1K tokens
        
        if ($data['operation_type'] === 'emotion_processing') {
            $data['tokens_used'] = 2000; // Estimated tokens for image processing
            $data['cost_usd'] = 0.02; // Estimated cost per image
        } else {
            $data['tokens_used'] = 1000;
            $data['cost_usd'] = 0.01;
        }
        
        return $data;
    }
    
    /**
     * Calculate usage for Replicate API
     */
    private static function calculateReplicateUsage($data)
    {
        // Replicate pricing varies by model
        // Typical emotion editing models: $0.01-0.10 per generation
        
        if ($data['operation_type'] === 'emotion_processing') {
            $data['tokens_used'] = 1500; // Estimated tokens
            $data['cost_usd'] = 0.05; // Estimated cost per generation
        } else {
            $data['tokens_used'] = 1000;
            $data['cost_usd'] = 0.03;
        }
        
        return $data;
    }
    
    /**
     * Calculate usage for Hugging Face API
     */
    private static function calculateHuggingFaceUsage($data)
    {
        // Hugging Face pricing
        // Free tier: 1000 requests/month
        // Paid: $0.001-0.01 per request
        
        if ($data['operation_type'] === 'emotion_processing') {
            $data['tokens_used'] = 1200; // Estimated tokens
            $data['cost_usd'] = 0.005; // Estimated cost per request
        } else {
            $data['tokens_used'] = 800;
            $data['cost_usd'] = 0.003;
        }
        
        return $data;
    }
    
    /**
     * Calculate usage for AILabTools API
     */
    private static function calculateAILabToolsUsage($data)
    {
        // AILabTools pricing (estimated)
        // Emotion editing: ~$0.01-0.05 per image
        
        if ($data['operation_type'] === 'emotion_processing') {
            $data['tokens_used'] = 1000; // Estimated tokens
            $data['cost_usd'] = 0.02; // Estimated cost per image
        } else {
            $data['tokens_used'] = 500;
            $data['cost_usd'] = 0.01;
        }
        
        return $data;
    }
    
    /**
     * Get token usage statistics for dashboard
     */
    public static function getDashboardStats()
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();
        
        return [
            'today' => [
                'tokens' => TokenUsage::getTotalTokens($today),
                'cost' => TokenUsage::getTotalCost($today),
                'api_calls' => TokenUsage::where('success', true)->where('created_at', '>=', $today)->count(),
            ],
            'this_week' => [
                'tokens' => TokenUsage::getTotalTokens($thisWeek),
                'cost' => TokenUsage::getTotalCost($thisWeek),
                'api_calls' => TokenUsage::where('success', true)->where('created_at', '>=', $thisWeek)->count(),
            ],
            'this_month' => [
                'tokens' => TokenUsage::getTotalTokens($thisMonth),
                'cost' => TokenUsage::getTotalCost($thisMonth),
                'api_calls' => TokenUsage::where('success', true)->where('created_at', '>=', $thisMonth)->count(),
            ],
            'all_time' => [
                'tokens' => TokenUsage::getTotalTokens(),
                'cost' => TokenUsage::getTotalCost(),
                'api_calls' => TokenUsage::where('success', true)->count(),
            ],
            'by_service' => TokenUsage::getUsageByService($thisMonth),
            'by_emotion' => TokenUsage::getUsageByEmotion($thisMonth),
        ];
    }
}
