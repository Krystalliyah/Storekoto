<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductReviewController extends Controller
{
    public function index(Product $product)
    {
        $reviews = $product->allReviews()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'total' => $product->allReviews()->count(),
            'average_rating' => $product->average_rating,
            'rating_distribution' => $product->rating_distribution ?? [1=>0,2=>0,3=>0,4=>0,5=>0],
            'approved' => $product->reviews()->count(),
            'pending' => $product->allReviews()->where('is_approved', false)->count(),
        ];
        
        return Inertia::render('vendor/ProductReviews', [
            'product' => $product,
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }
    
    public function approve(Product $product, ProductReview $review)
    {
        $review->update(['is_approved' => true]);
        $product->updateRatingStats();
        
        return back()->with('success', 'Review approved successfully.');
    }
    
    public function destroy(Product $product, ProductReview $review)
    {
        $review->delete();
        $product->updateRatingStats();
        
        return back()->with('success', 'Review deleted successfully.');
    }
    
    public function feature(Product $product, ProductReview $review)
    {
        $review->update(['is_featured' => !$review->is_featured]);
        
        return back()->with('success', $review->is_featured ? 'Review featured.' : 'Review unfeatured.');
    }
    
    public function respond(Request $request, Product $product, ProductReview $review)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string|max:2000',
        ]);
        
        $review->update([
            'admin_response' => $validated['admin_response'],
            'admin_response_at' => now(),
        ]);
        
        return back()->with('success', 'Response added successfully.');
    }
}