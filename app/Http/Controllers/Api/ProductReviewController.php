<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductReviewController extends Controller
{
    public function index(Request $request, $storeId, $productId)
{
    $perPage = $request->get('per_page', 10);
    $rating = $request->get('rating');
    $sort = $request->get('sort', 'newest');
    $user = auth()->user();
    
    $tenant = \App\Models\Tenant::find($storeId);
    
    if (!$tenant) {
        return response()->json(['error' => 'Store not found'], 404);
    }
    
    $reviews = $tenant->run(function () use ($productId, $perPage, $rating, $sort, $user) {
        $query = \App\Models\ProductReview::where('product_id', $productId)
            ->with('user');
        
        // Show approved reviews OR the current user's own reviews (even if not approved)
        $query->where(function($q) use ($user) {
            $q->where('is_approved', true);
            if ($user) {
                $q->orWhere('user_id', $user->id);
            }
        });
        
        if ($rating) {
            $query->where('rating', $rating);
        }
        
        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'helpful':
                $query->orderBy('helpful_count', 'desc');
                break;
            case 'highest':
                $query->orderBy('rating', 'desc');
                break;
            case 'lowest':
                $query->orderBy('rating', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        return $query->paginate($perPage);
    });
    
    return response()->json([
        'success' => true,
        'data' => $reviews,
    ]);
}
    
    public function stats($storeId, $productId)
    {
        $tenant = \App\Models\Tenant::find($storeId);
        
        if (!$tenant) {
            return response()->json(['error' => 'Store not found'], 404);
        }
        
        $stats = $tenant->run(function () use ($productId) {
            $product = Product::find($productId);
            
            if (!$product) {
                return null;
            }
            
            $distribution = $product->rating_distribution ?? [1=>0,2=>0,3=>0,4=>0,5=>0];
            $total = $product->total_reviews;
            
            $percentages = [];
            for ($i = 5; $i >= 1; $i--) {
                $count = $distribution[$i] ?? 0;
                $percentages[$i] = $total > 0 ? round(($count / $total) * 100) : 0;
            }
            
            return [
                'average_rating' => $product->formatted_rating,
                'total_reviews' => $product->total_reviews,
                'rating_distribution' => $distribution,
                'rating_percentages' => $percentages,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
    
    public function store(Request $request, $storeId, $productId)
{
    // Log at the very beginning
    \Log::info('=== REVIEW SUBMISSION START ===');
    \Log::info('Store ID: ' . $storeId);
    \Log::info('Product ID: ' . $productId);
    \Log::info('User ID: ' . (auth()->id() ?? 'not logged in'));
    \Log::info('Request data: ', $request->all());
    
    try {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|min:3|max:5000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        
        \Log::info('Validation passed', ['validated' => $validated]);
        
        $user = auth()->user();
        
        if (!$user) {
            \Log::warning('User not logged in');
            return response()->json([
                'success' => false,
                'message' => 'Please login to leave a review'
            ], 401);
        }
        
        $tenant = \App\Models\Tenant::find($storeId);
        
        if (!$tenant) {
            \Log::warning('Store not found', ['store_id' => $storeId]);
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }
        
        \Log::info('Tenant found', ['tenant_id' => $tenant->id, 'tenant_name' => $tenant->name]);
        
        $result = $tenant->run(function () use ($productId, $user, $validated, $request) {
            \Log::info('Inside tenant context, looking for product', ['product_id' => $productId]);
            
            $product = \App\Models\Product::find($productId);
            
            if (!$product) {
                \Log::error('Product not found in tenant database', ['product_id' => $productId]);
                throw new \Exception('Product not found');
            }
            
            \Log::info('Product found', ['product_id' => $product->id, 'product_name' => $product->name]);
            
            // Check if user already reviewed this product
            $existingReview = \App\Models\ProductReview::where('product_id', $productId)
                ->where('user_id', $user->id)
                ->first();
            
            if ($existingReview) {
                \Log::info('Existing review found', ['review_id' => $existingReview->id]);
                throw new \Exception('You have already reviewed this product');
            }
            
            // Check if user has purchased this product
            $hasPurchased = \App\Models\OrderItem::where('product_id', $productId)
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'completed');
                })
                ->exists();
            
            \Log::info('Purchase check', ['has_purchased' => $hasPurchased]);
            
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product-reviews', 'public');
                    $imagePaths[] = \Illuminate\Support\Facades\Storage::url($path);
                }
                \Log::info('Images uploaded', ['count' => count($imagePaths)]);
            }
            
            $review = \App\Models\ProductReview::create([
                'product_id' => $productId,
                'user_id' => $user->id,
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'comment' => $validated['comment'],
                'images' => !empty($imagePaths) ? $imagePaths : null,
                'is_verified_purchase' => $hasPurchased,
                'is_approved' => true,
            ]);
            
            \Log::info('Review created', ['review_id' => $review->id]);
            
            // Update product rating stats
            $product->updateRatingStats();
            \Log::info('Product stats updated');
            
            // Load user data for the response
            $review->load('user');
            
            return $review;
        });
        
        \Log::info('=== REVIEW SUBMISSION SUCCESS ===');
        
        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully!',
            'data' => $result,
        ], 200);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation failed', ['errors' => $e->errors()]);
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Review submission error: ' . $e->getMessage(), [
            'store_id' => $storeId,
            'product_id' => $productId,
            'user_id' => auth()->id(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 422);
    }
}
    public function helpful(Request $request, $reviewId)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'Please login'], 401);
        }
        
        $review = ProductReview::findOrFail($reviewId);
        
        $existingVote = \App\Models\ReviewHelpfulnessVote::where('review_id', $reviewId)
            ->where('user_id', $user->id)
            ->first();
        
        if ($existingVote) {
            return response()->json(['message' => 'You already voted on this review'], 422);
        }
        
        \App\Models\ReviewHelpfulnessVote::create([
            'review_id' => $reviewId,
            'user_id' => $user->id,
        ]);
        
        $review->increment('helpful_count');
        
        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
            'helpful_count' => $review->fresh()->helpful_count,
        ]);
    }
}