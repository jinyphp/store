<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreAssignment extends Model
{
    protected $fillable = [
        'assignable_type',
        'assignable_id',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'due_date',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_date' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * 할당 가능한 엔티티 관계
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 담당자 관계
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * 할당한 사람 관계
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_by');
    }

    /**
     * 할당 로그 관계
     */
    public function logs(): HasMany
    {
        return $this->hasMany(StoreAssignmentLog::class, 'assignment_id');
    }

    /**
     * 상태별 스코프
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * 담당자별 스코프
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * 모듈별 스코프
     */
    public function scopeForModule($query, $module)
    {
        return $query->where('assignable_type', 'LIKE', '%' . ucfirst($module) . '%');
    }

    /**
     * 상태 변경
     */
    public function changeStatus($newStatus, $userId, $comment = null)
    {
        $oldStatus = $this->status;
        $this->update(['status' => $newStatus]);

        // 로그 기록
        $this->logs()->create([
            'user_id' => $userId,
            'action' => 'status_changed',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'comment' => $comment,
            'changes' => ['status' => ['from' => $oldStatus, 'to' => $newStatus]]
        ]);

        return $this;
    }

    /**
     * 담당자 변경
     */
    public function transferTo($newAssigneeId, $userId, $comment = null)
    {
        $oldAssigneeId = $this->assigned_to;
        $this->update([
            'assigned_to' => $newAssigneeId,
            'assigned_by' => $userId,
            'assigned_at' => now()
        ]);

        // 로그 기록
        $this->logs()->create([
            'user_id' => $userId,
            'action' => 'transferred',
            'comment' => $comment,
            'changes' => [
                'assigned_to' => ['from' => $oldAssigneeId, 'to' => $newAssigneeId]
            ]
        ]);

        return $this;
    }

    /**
     * 마감일 확인
     */
    public function isOverdue(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               in_array($this->status, ['pending', 'in_progress']);
    }

    /**
     * 진행률 계산
     */
    public function getProgressAttribute(): int
    {
        return match($this->status) {
            'pending' => 0,
            'in_progress' => 50,
            'completed' => 100,
            'cancelled' => 0,
            default => 0
        };
    }
}