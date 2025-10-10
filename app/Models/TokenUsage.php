<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenUsage extends Model
{
    protected $table = 'token_usage';
    
    protected $fillable = [
        'api_service',
        'operation_type',
        'emotion',
        'tokens_used',
        'cost_usd',
        'model_used',
        'request_data',
        'response_data',
        'success',
        'error_message',
        'generated_image_id',
        'phone_number',
    ];
    
    protected $casts = [
        'tokens_used' => 'integer',
        'cost_usd' => 'decimal:6',
        'success' => 'boolean',
        'request_data' => 'array',
        'response_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Relationship to GeneratedImage
     */
    public function generatedImage(): BelongsTo
    {
        return $this->belongsTo(GeneratedImage::class);
    }
    
    /**
     * Get total tokens used for a specific period
     */
    public static function getTotalTokens($startDate = null, $endDate = null)
    {
        $query = self::where('success', true);
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->sum('tokens_used');
    }
    
    /**
     * Get total cost for a specific period
     */
    public static function getTotalCost($startDate = null, $endDate = null)
    {
        $query = self::where('success', true);
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->sum('cost_usd');
    }
    
    /**
     * Get usage statistics by API service
     */
    public static function getUsageByService($startDate = null, $endDate = null)
    {
        $query = self::where('success', true);
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->selectRaw('api_service, COUNT(*) as api_calls, SUM(tokens_used) as total_tokens, SUM(cost_usd) as total_cost')
                    ->groupBy('api_service')
                    ->get();
    }
    
    /**
     * Get usage statistics by emotion
     */
    public static function getUsageByEmotion($startDate = null, $endDate = null)
    {
        $query = self::where('success', true)->whereNotNull('emotion');
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->selectRaw('emotion, COUNT(*) as api_calls, SUM(tokens_used) as total_tokens, SUM(cost_usd) as total_cost')
                    ->groupBy('emotion')
                    ->get();
    }
    
    /**
     * Log a new token usage entry
     */
    public static function logUsage($data)
    {
        return self::create($data);
    }
}
