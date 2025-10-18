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
        Schema::table('generated_images', function (Blueprint $table) {
            $table->string('sad_image')->nullable()->change();
            $table->string('happy_image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_images', function (Blueprint $table) {
            $table->string('sad_image')->nullable(false)->change();
            $table->string('happy_image')->nullable(false)->change();
        });
    }
};
