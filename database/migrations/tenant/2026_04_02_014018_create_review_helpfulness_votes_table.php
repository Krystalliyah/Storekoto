<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->create('review_helpfulness_votes', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('review_id')->constrained('product_reviews')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            
            $table->timestamps();
            
            $table->unique(['review_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('review_helpfulness_votes');
    }
};