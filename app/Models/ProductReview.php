<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model
{
    use HasFactory, SoftDeletes;
    
    // Remove the hardcoded connection line
    // protected $connection = 'tenant';
    
    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'title',
        'comment',
        'images',
        'is_verified_purchase',
        'is_approved',
        'is_featured',
        'helpful_count',
        'admin_response',
        'admin_response_at',
    ];
    
    protected $casts = [
        'images' => 'array',
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
        'admin_response_at' => 'datetime',
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function helpfulnessVotes()
    {
        return $this->hasMany(ReviewHelpfulnessVote::class);
    }
    
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
    
    public function scopeRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
}