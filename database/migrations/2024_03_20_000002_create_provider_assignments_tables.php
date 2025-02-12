<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create pivot table for provider-customer assignments
        Schema::create('provider_customer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['user_id', 'customer_id']);
        });

        // Create pivot table for provider-project assignments
        Schema::create('provider_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->boolean('auto_assigned')->default(false); // To track if assignment was automatic
            $table->timestamps();
            
            // Prevent duplicate assignments
            $table->unique(['user_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_project');
        Schema::dropIfExists('provider_customer');
    }
}; 