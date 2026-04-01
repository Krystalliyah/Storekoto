<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReviewHelpfulnessVote extends Model
{
    use HasFactory;
    
    // Remove the hardcoded connection line
    // protected $connection = 'tenant';
    
    protected $fillable = [
        'review_id',
        'user_id',
    ];
    
    public function review()
    {
        return $this->belongsTo(ProductReview::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}