<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->create('product_reviews', function (Blueprint $table) {
            $table->id();
            
            // Product reference
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Customer reference (central user ID)
            $table->unsignedBigInteger('user_id');
            
            // Review content
            $table->integer('rating')->unsigned();
            $table->text('title')->nullable();
            $table->text('comment')->nullable();
            
            // Media
            $table->json('images')->nullable();
            
            // Status flags
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_featured')->default(false);
            
            // Helpfulness votes
            $table->integer('helpful_count')->default(0);
            
            // Admin response
            $table->text('admin_response')->nullable();
            $table->timestamp('admin_response_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['product_id', 'rating']);
            $table->index('user_id');
            $table->index('is_approved');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('product_reviews');
    }
};