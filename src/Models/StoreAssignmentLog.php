<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreAssignmentLog extends Model
{
    protected $fillable = [
        'assignment_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'comment',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * 할당 관계
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(StoreAssignment::class);
    }

    /**
     * 사용자 관계
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * 액션별 스코프
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * 최근 로그 스코프
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}