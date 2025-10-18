<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreTestimonial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'store_testimonials';

    protected $fillable = [
        'enable',
        'featured',
        'author_name',
        'author_title',
        'author_company',
        'author_image',
        'author_email',
        'rating',
        'title',
        'content',
        'testimonial_date',
        'product_id',
        'order_id',
        'verified_purchase',
        'helpful_count',
        'not_helpful_count',
        'status',
        'admin_reply',
        'admin_replied_at',
        'tags',
        'meta_data',
        'manager',
    ];

    protected $casts = [
        'enable' => 'boolean',
        'featured' => 'boolean',
        'rating' => 'integer',
        'verified_purchase' => 'boolean',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
        'testimonial_date' => 'date',
        'admin_replied_at' => 'datetime',
        'tags' => 'array',
        'meta_data' => 'array',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * 상품 관계
     */
    public function product()
    {
        return $this->belongsTo(StoreProduct::class, 'product_id');
    }

    /**
     * 주문 관계
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * 활성화된 후기만 조회
     */
    public function scopeEnabled($query)
    {
        return $query->where('enable', true);
    }

    /**
     * 추천 후기만 조회
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * 승인된 후기만 조회
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * 검증된 구매만 조회
     */
    public function scopeVerified($query)
    {
        return $query->where('verified_purchase', true);
    }

    /**
     * 별점별 조회
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * 최소 별점 이상 조회
     */
    public function scopeMinRating($query, $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

    /**
     * 상품별 조회
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * 검색
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
              ->orWhere('content', 'like', '%' . $keyword . '%')
              ->orWhere('author_name', 'like', '%' . $keyword . '%')
              ->orWhere('author_company', 'like', '%' . $keyword . '%');
        });
    }

    /**
     * 최근 후기 조회
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('testimonial_date', '>=', now()->subDays($days));
    }

    /**
     * 도움됨 비율 계산
     */
    public function getHelpfulRateAttribute()
    {
        $total = $this->helpful_count + $this->not_helpful_count;

        if ($total === 0) {
            return 0;
        }

        return ($this->helpful_count / $total) * 100;
    }

    /**
     * 별점을 별 아이콘으로 표시
     */
    public function getStarsAttribute()
    {
        $fullStars = floor($this->rating);
        $halfStar = ($this->rating - $fullStars) >= 0.5;
        $emptyStars = 5 - ceil($this->rating);

        return [
            'full' => $fullStars,
            'half' => $halfStar ? 1 : 0,
            'empty' => $emptyStars,
        ];
    }

    /**
     * 짧은 내용 (요약)
     */
    public function getExcerptAttribute($length = 150)
    {
        return \Str::limit(strip_tags($this->content), $length);
    }

    /**
     * 관리자 답변 여부
     */
    public function getHasAdminReplyAttribute()
    {
        return !empty($this->admin_reply);
    }

    /**
     * 태그 배열
     */
    public function getTagListAttribute()
    {
        return is_array($this->tags) ? $this->tags : [];
    }
}
