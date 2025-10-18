<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Jiny\Store\Traits\HasAssignments;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasAssignments;

    protected $table = 'store_promotions';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'terms_conditions',
        'type',
        'status',
        'starts_at',
        'ends_at',
        'participant_limit',
        'participant_count',
        'budget',
        'spent_amount',
        'target_audience',
        'rules',
        'rewards',
        'metadata',
        'banner_image',
        'marketing_materials',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'budget' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'target_audience' => 'array',
        'rules' => 'array',
        'rewards' => 'array',
        'metadata' => 'array',
        'marketing_materials' => 'array',
        'is_featured' => 'boolean',
    ];

    /**
     * 활성 상태 스코프
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('starts_at', '<=', now())
                     ->where('ends_at', '>=', now());
    }

    /**
     * 예약된 프로모션 스코프
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('starts_at', '>', now());
    }

    /**
     * 종료된 프로모션 스코프
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')
                     ->orWhere('ends_at', '<', now());
    }

    /**
     * 추천 프로모션 스코프
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * 타입별 스코프
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 프로모션이 현재 활성인지 확인
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               $this->starts_at <= now() &&
               $this->ends_at >= now();
    }

    /**
     * 프로모션이 시작 가능한지 확인
     */
    public function canStart(): bool
    {
        return $this->status === 'scheduled' &&
               $this->starts_at <= now();
    }

    /**
     * 프로모션이 종료되어야 하는지 확인
     */
    public function shouldEnd(): bool
    {
        return $this->status === 'active' &&
               $this->ends_at < now();
    }

    /**
     * 사용자가 참여 가능한지 확인
     */
    public function canUserParticipate($userId): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // 참여자 수 제한 확인
        if ($this->participant_limit && $this->participant_count >= $this->participant_limit) {
            return false;
        }

        return true;
    }

    /**
     * 프로모션 상태 자동 업데이트
     */
    public function updateStatus()
    {
        if ($this->canStart()) {
            $this->update(['status' => 'active']);
        } elseif ($this->shouldEnd()) {
            $this->update(['status' => 'completed']);
        }
    }

    /**
     * 남은 시간 계산
     */
    public function getTimeRemaining(): ?string
    {
        if ($this->status !== 'active' || $this->ends_at->isPast()) {
            return null;
        }

        return $this->ends_at->diffForHumans();
    }

    /**
     * 진행률 계산
     */
    public function getProgress(): float
    {
        $totalDuration = $this->starts_at->diffInSeconds($this->ends_at);
        $elapsed = $this->starts_at->diffInSeconds(now());

        return min(100, ($elapsed / $totalDuration) * 100);
    }
}