<?php

use App\Http\Controllers\TenantDataController;

Route::middleware(['web', 'auth'])->prefix('api/tenant/{tenantId}')->group(function () {
    // Product reviews
    Route::get('/products/{productId}/reviews', [TenantDataController::class, 'getProductReviews']);
    Route::get('/products/{productId}/reviews/stats', [TenantDataController::class, 'getReviewStats']);
    Route::post('/products/{productId}/reviews', [TenantDataController::class, 'submitReview']);
    Route::post('/reviews/{reviewId}/helpful', [TenantDataController::class, 'markHelpful']);
    
    // Product data
    Route::get('/products/{productId}', [TenantDataController::class, 'getProduct']);
});