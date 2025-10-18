<?php

namespace Jiny\Store\Traits;

use Jiny\Store\Models\StoreAssignment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAssignments
{
    /**
     * 할당 관계
     */
    public function assignments(): MorphMany
    {
        return $this->morphMany(StoreAssignment::class, 'assignable');
    }

    /**
     * 현재 활성 할당
     */
    public function currentAssignment()
    {
        return $this->assignments()
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->latest('assigned_at')
                    ->first();
    }

    /**
     * 담당자에게 할당
     */
    public function assignTo($userId, $assignedBy, $options = [])
    {
        // 기존 활성 할당이 있으면 완료 처리
        $existingAssignment = $this->currentAssignment();
        if ($existingAssignment) {
            $existingAssignment->changeStatus('cancelled', $assignedBy, '새로운 담당자 할당으로 인한 취소');
        }

        $assignment = $this->assignments()->create([
            'assigned_to' => $userId,
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
            'due_date' => $options['due_date'] ?? null,
            'status' => 'pending',
            'notes' => $options['notes'] ?? null,
            'metadata' => $options['metadata'] ?? null,
        ]);

        // 로그 기록
        $assignment->logs()->create([
            'user_id' => $assignedBy,
            'action' => 'assigned',
            'new_status' => 'pending',
            'comment' => $options['notes'] ?? null,
            'changes' => [
                'assigned_to' => $userId,
                'assigned_by' => $assignedBy
            ]
        ]);

        return $assignment;
    }

    /**
     * 할당 해제
     */
    public function unassign($userId, $reason = null)
    {
        $assignment = $this->currentAssignment();
        if ($assignment) {
            $assignment->changeStatus('cancelled', $userId, $reason);
        }

        return $this;
    }

    /**
     * 할당 여부 확인
     */
    public function isAssigned(): bool
    {
        return $this->currentAssignment() !== null;
    }

    /**
     * 특정 사용자에게 할당되었는지 확인
     */
    public function isAssignedTo($userId): bool
    {
        $assignment = $this->currentAssignment();
        return $assignment && $assignment->assigned_to == $userId;
    }

    /**
     * 현재 담당자 가져오기
     */
    public function getCurrentAssignee()
    {
        $assignment = $this->currentAssignment();
        return $assignment ? $assignment->assignedTo : null;
    }

    /**
     * 할당 상태 가져오기
     */
    public function getAssignmentStatus()
    {
        $assignment = $this->currentAssignment();
        return $assignment ? $assignment->status : null;
    }

    /**
     * 마감일 가져오기
     */
    public function getAssignmentDueDate()
    {
        $assignment = $this->currentAssignment();
        return $assignment ? $assignment->due_date : null;
    }

    /**
     * 할당 진행률 가져오기
     */
    public function getAssignmentProgress(): int
    {
        $assignment = $this->currentAssignment();
        return $assignment ? $assignment->progress : 0;
    }

    /**
     * 할당이 마감일을 지났는지 확인
     */
    public function isAssignmentOverdue(): bool
    {
        $assignment = $this->currentAssignment();
        return $assignment ? $assignment->isOverdue() : false;
    }
}