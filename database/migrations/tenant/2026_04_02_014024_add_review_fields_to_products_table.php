<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->table('products', function (Blueprint $table) {
            // Add review summary fields
            $table->decimal('average_rating', 2, 1)->default(0)->after('price');
            $table->integer('total_reviews')->default(0)->after('average_rating');
            $table->integer('total_ratings')->default(0)->after('total_reviews');
            
            // Rating distribution (JSON)
            $table->json('rating_distribution')->nullable()->after('total_ratings');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('products', function (Blueprint $table) {
            $table->dropColumn([
                'average_rating',
                'total_reviews',
                'total_ratings',
                'rating_distribution',
            ]);
        });
    }
};