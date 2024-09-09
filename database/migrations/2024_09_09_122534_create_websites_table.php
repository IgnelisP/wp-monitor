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
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->string('scheme', 10);
            $table->string('domain', 255);
            $table->string('path', 255);
            $table->string('api_key', 255)->unique();
            $table->timestamps();

            // Ensure global uniqueness across all users based on domain and path
            $table->unique(['domain', 'path']);

            // User-specific index for querying purposes
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
