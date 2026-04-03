<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TenantDataController extends Controller
{
    /**
     * Switch to tenant database context
     */
    private function switchToTenant($tenantId)
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            throw new \Exception("Store not found: {$tenantId}");
        }
        
        // Initialize tenancy for this tenant
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
        
        return $tenant;
    }
    
    /**
     * Get product details from tenant database
     */
    public function getProduct($tenantId, $productId)
    {
        try {
            $this->switchToTenant($tenantId);
            
            $product = \App\Models\Product::with(['store', 'category'])
                ->findOrFail($productId);
            
            return response()->json([
                'success' => true,
                'data' => $product
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch product: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to load product'
            ], 500);
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }
    
    /**
     * Get product reviews from tenant database
     */
    public function getProductReviews(Request $request, $tenantId, $productId)
    {
        try {
            $this->switchToTenant($tenantId);
            
            $perPage = $request->get('per_page', 10);
            $rating = $request->get('rating');
            $sort = $request->get('sort', 'newest');
            
            $query = \App\Models\ProductReview::where('product_id', $productId)
                ->with('user')
                ->where('is_approved', true);
            
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
            
            $reviews = $query->paginate($perPage);
            
            // Get user names from central database for display
            $reviews->getCollection()->transform(function ($review) {
                $centralUser = \App\Models\User::find($review->user_id);
                $review->user_name = $centralUser ? $centralUser->name : 'Anonymous';
                return $review;
            });
            
            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch reviews: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reviews'
            ], 500);
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }
    
    /**
     * Get review statistics from tenant database
     */
    public function getReviewStats($tenantId, $productId)
    {
        try {
            $this->switchToTenant($tenantId);
            
            $product = \App\Models\Product::findOrFail($productId);
            
            $distribution = $product->rating_distribution ?? [1=>0,2=>0,3=>0,4=>0,5=>0];
            $total = $product->total_reviews ?? 0;
            
            $percentages = [];
            for ($i = 5; $i >= 1; $i--) {
                $count = $distribution[$i] ?? 0;
                $percentages[$i] = $total > 0 ? round(($count / $total) * 100) : 0;
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'average_rating' => number_format($product->average_rating ?? 0, 1),
                    'total_reviews' => $total,
                    'rating_distribution' => $distribution,
                    'rating_percentages' => $percentages,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch review stats: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to load review stats'
            ], 500);
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }
    
    /**
     * Submit a review to tenant database
     */
    public function submitReview(Request $request, $tenantId, $productId)
    {
        try {
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'title' => 'nullable|string|max:255',
                'comment' => 'required|string|min:3|max:5000',
                'images' => 'nullable|array|max:5',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048'
            ]);
            
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to leave a review'
                ], 401);
            }
            
            $this->switchToTenant($tenantId);
            
            // Check if user already reviewed this product
            $existingReview = \App\Models\ProductReview::where('product_id', $productId)
                ->where('user_id', $user->id)
                ->first();
            
            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this product'
                ], 422);
            }
            
            // Check if user has purchased this product
            $hasPurchased = \App\Models\OrderItem::where('product_id', $productId)
                ->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'completed');
                })
                ->exists();
            
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store("tenant-{$tenantId}/product-reviews", 'public');
                    $imagePaths[] = Storage::url($path);
                }
            }
            
            $review = \App\Models\ProductReview::create([
                'product_id' => $productId,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'images' => !empty($imagePaths) ? $imagePaths : null,
                'is_verified_purchase' => $hasPurchased,
                'is_approved' => true, // Set to false if you want manual approval
                'helpful_count' => 0,
            ]);
            
            // Update product rating stats
            $product = \App\Models\Product::find($productId);
            if ($product && method_exists($product, 'updateRatingStats')) {
                $product->updateRatingStats();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully!',
                'data' => $review
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to submit review: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }
    
    /**
     * Mark a review as helpful
     */
    public function markHelpful(Request $request, $tenantId, $reviewId)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }
            
            $this->switchToTenant($tenantId);
            
            $review = \App\Models\ProductReview::findOrFail($reviewId);
            
            $existingVote = \App\Models\ReviewHelpfulnessVote::where('review_id', $reviewId)
                ->where('user_id', $user->id)
                ->first();
            
            if ($existingVote) {
                return response()->json(['success' => false, 'message' => 'You already voted on this review'], 422);
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
            
        } catch (\Exception $e) {
            Log::error("Failed to mark helpful: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to register vote'
            ], 500);
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }
}