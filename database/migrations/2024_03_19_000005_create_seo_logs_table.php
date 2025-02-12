<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seo_logs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', [
                'seo_analytics_reporting',
                'technical_seo',
                'on_page_seo',
                'off_page_seo',
                'local_seo',
                'content_seo'
            ])->default('seo_analytics_reporting');
            $table->json('meta_data')->nullable(); // For storing additional data like keyword rankings
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // SEO provider who created the log
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('seo_logs');
    }
}; 