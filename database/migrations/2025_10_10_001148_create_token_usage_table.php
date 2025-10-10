<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('token_usage', function (Blueprint $table) {
            $table->id();
            $table->string('api_service'); // 'google_gemini', 'replicate', 'huggingface', 'ailabtools', etc.
            $table->string('operation_type'); // 'emotion_processing', 'image_generation', etc.
            $table->string('emotion')->nullable(); // 'sad', 'happy', null for other operations
            $table->integer('tokens_used')->default(0); // Number of tokens consumed
            $table->decimal('cost_usd', 10, 6)->default(0); // Cost in USD
            $table->string('model_used')->nullable(); // Model version or name
            $table->text('request_data')->nullable(); // JSON of request details
            $table->text('response_data')->nullable(); // JSON of response details
            $table->boolean('success')->default(true); // Whether the API call was successful
            $table->string('error_message')->nullable(); // Error message if failed
            $table->unsignedBigInteger('generated_image_id')->nullable(); // Link to generated image
            $table->string('phone_number')->nullable(); // User phone number for tracking
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['api_service', 'created_at']);
            $table->index(['operation_type', 'created_at']);
            $table->index(['emotion', 'created_at']);
            $table->index('generated_image_id');
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_usage');
    }
};
