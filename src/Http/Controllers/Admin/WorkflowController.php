<?php

namespace Jiny\Store\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Store\Models\StoreAssignment;
use Jiny\Store\Models\StoreAssignmentLog;
use App\Models\User;

/**
 * 통합 업무 분업화 관리 컨트롤러
 */
class WorkflowController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'title' => '업무 분업화 관리',
            'subtitle' => '스토어 모든 업무의 담당자 할당과 워크플로우를 통합 관리합니다.',
        ];
    }

    /**
     * 통합 대시보드
     */
    public function dashboard(Request $request)
    {
        // 모듈별 현황 통계
        $moduleStats = $this->getModuleStats();

        // 담당자별 업무 부하
        $workloadStats = $this->getWorkloadStats();

        // 긴급/지연 업무
        $urgentTasks = $this->getUrgentTasks();

        // 최근 활동
        $recentActivities = $this->getRecentActivities();

        // 성과 지표
        $performanceMetrics = $this->getPerformanceMetrics();

        // 워크플로우 상태 분포
        $workflowDistribution = $this->getWorkflowDistribution();

        return view('jiny-store::admin.workflow.dashboard', [
            'moduleStats' => $moduleStats,
            'workloadStats' => $workloadStats,
            'urgentTasks' => $urgentTasks,
            'recentActivities' => $recentActivities,
            'performanceMetrics' => $performanceMetrics,
            'workflowDistribution' => $workflowDistribution,
            'config' => $this->config,
        ]);
    }

    /**
     * 자동 업무 할당
     */
    public function autoAssign(Request $request)
    {
        $request->validate([
            'module' => 'required|in:shipping,coupon,promotion,testimonial',
            'assignment_strategy' => 'required|in:round_robin,least_busy,skill_based,random',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_hours' => 'nullable|integer|min:1|max:168', // 최대 1주일
        ]);

        DB::beginTransaction();
        try {
            $assignedCount = 0;

            // 미할당 업무 조회
            $unassignedTasks = $this->getUnassignedTasks($request->module);

            // 할당 가능한 담당자 목록
            $availableAssignees = $this->getAvailableAssignees($request->module);

            if ($availableAssignees->isEmpty()) {
                return redirect()->back()->with('error', '할당 가능한 담당자가 없습니다.');
            }

            foreach ($unassignedTasks as $task) {
                $assignee = $this->selectAssignee(
                    $availableAssignees,
                    $request->assignment_strategy,
                    $request->module
                );

                if ($assignee) {
                    $task->assignTo(
                        $assignee->id,
                        auth()->id(),
                        [
                            'due_date' => $request->due_hours ? now()->addHours($request->due_hours) : null,
                            'notes' => "자동 할당 ({$request->assignment_strategy})",
                            'metadata' => [
                                'module' => $request->module,
                                'auto_assigned' => true,
                                'strategy' => $request->assignment_strategy,
                                'priority' => $request->priority,
                            ]
                        ]
                    );

                    $assignedCount++;
                }
            }

            DB::commit();

            return redirect()->back()->with('success',
                "{$request->module} 모듈의 {$assignedCount}개 업무가 자동 할당되었습니다.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error',
                '자동 할당 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 업무 재분배
     */
    public function redistribute(Request $request)
    {
        $request->validate([
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id|different:from_user_id',
            'assignment_ids' => 'nullable|array',
            'assignment_ids.*' => 'exists:store_assignments,id',
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $redistributedCount = 0;

            // 특정 할당이 지정된 경우
            if ($request->assignment_ids) {
                $assignments = StoreAssignment::whereIn('id', $request->assignment_ids)
                                             ->where('assigned_to', $request->from_user_id)
                                             ->whereIn('status', ['pending', 'in_progress'])
                                             ->get();
            } else {
                // 모든 진행 중인 업무 재분배
                $assignments = StoreAssignment::where('assigned_to', $request->from_user_id)
                                             ->whereIn('status', ['pending', 'in_progress'])
                                             ->get();
            }

            foreach ($assignments as $assignment) {
                $assignment->transferTo(
                    $request->to_user_id,
                    auth()->id(),
                    $request->reason
                );

                $redistributedCount++;
            }

            DB::commit();

            return redirect()->back()->with('success',
                "{$redistributedCount}개의 업무가 재분배되었습니다.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error',
                '업무 재분배 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 업무 에스컬레이션
     */
    public function escalate(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:store_assignments,id',
            'escalation_reason' => 'required|in:overdue,urgent,resource_needed,complex',
            'notes' => 'nullable|string|max:500',
        ]);

        $assignment = StoreAssignment::find($request->assignment_id);

        if (!$assignment) {
            return redirect()->back()->with('error', '할당을 찾을 수 없습니다.');
        }

        // 관리자에게 에스컬레이션
        $manager = $this->getModuleManager($assignment->metadata['module'] ?? 'general');

        if ($manager) {
            $assignment->transferTo(
                $manager->id,
                auth()->id(),
                "에스컬레이션: {$request->escalation_reason} - {$request->notes}"
            );

            // 에스컬레이션 메타데이터 추가
            $metadata = $assignment->metadata ?? [];
            $metadata['escalated'] = true;
            $metadata['escalation_reason'] = $request->escalation_reason;
            $metadata['escalated_at'] = now()->toISOString();
            $metadata['escalated_by'] = auth()->id();

            $assignment->update(['metadata' => $metadata]);

            return redirect()->back()->with('success', '업무가 관리자에게 에스컬레이션되었습니다.');
        }

        return redirect()->back()->with('error', '해당 모듈의 관리자를 찾을 수 없습니다.');
    }

    /**
     * 일괄 상태 업데이트
     */
    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'assignment_ids' => 'required|array|min:1',
            'assignment_ids.*' => 'exists:store_assignments,id',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'comment' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $updatedCount = 0;

            foreach ($request->assignment_ids as $assignmentId) {
                $assignment = StoreAssignment::find($assignmentId);
                if ($assignment) {
                    $assignment->changeStatus(
                        $request->status,
                        auth()->id(),
                        $request->comment
                    );
                    $updatedCount++;
                }
            }

            DB::commit();

            return redirect()->back()->with('success',
                "{$updatedCount}개의 업무 상태가 업데이트되었습니다.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error',
                '상태 업데이트 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 모듈별 현황 통계
     */
    protected function getModuleStats()
    {
        return DB::table('store_assignments')
            ->select(
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.module")) as module'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN due_date < NOW() AND status NOT IN ("completed", "cancelled") THEN 1 ELSE 0 END) as overdue'),
                DB::raw('AVG(CASE WHEN status = "completed" THEN TIMESTAMPDIFF(HOUR, assigned_at, updated_at) END) as avg_completion_hours')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('module')
            ->get();
    }

    /**
     * 담당자별 업무 부하 통계
     */
    protected function getWorkloadStats()
    {
        return DB::table('store_assignments')
            ->select(
                'assigned_to',
                'users.name as assignee_name',
                DB::raw('COUNT(*) as total_assignments'),
                DB::raw('SUM(CASE WHEN status IN ("pending", "in_progress") THEN 1 ELSE 0 END) as active_assignments'),
                DB::raw('SUM(CASE WHEN due_date < NOW() AND status NOT IN ("completed", "cancelled") THEN 1 ELSE 0 END) as overdue_assignments'),
                DB::raw('AVG(CASE WHEN status = "completed" THEN TIMESTAMPDIFF(HOUR, assigned_at, updated_at) END) as avg_completion_hours')
            )
            ->join('users', 'store_assignments.assigned_to', '=', 'users.id')
            ->where('store_assignments.assigned_at', '>=', now()->subDays(30))
            ->groupBy('assigned_to', 'users.name')
            ->orderBy('active_assignments', 'desc')
            ->get();
    }

    /**
     * 긴급/지연 업무 조회
     */
    protected function getUrgentTasks()
    {
        return StoreAssignment::with(['assignedTo', 'assignable'])
            ->where(function($query) {
                $query->where('due_date', '<=', now()->addHours(4)) // 4시간 이내 마감
                      ->orWhere('due_date', '<', now()) // 이미 지연
                      ->orWhere('metadata->priority', 'urgent');
            })
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_date')
            ->limit(20)
            ->get();
    }

    /**
     * 최근 활동 조회
     */
    protected function getRecentActivities()
    {
        return DB::table('store_assignment_logs')
            ->select(
                'store_assignment_logs.*',
                'users.name as user_name',
                'assignees.name as assignee_name',
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(store_assignments.metadata, "$.module")) as module')
            )
            ->join('store_assignments', 'store_assignment_logs.assignment_id', '=', 'store_assignments.id')
            ->join('users', 'store_assignment_logs.user_id', '=', 'users.id')
            ->join('users as assignees', 'store_assignments.assigned_to', '=', 'assignees.id')
            ->orderBy('store_assignment_logs.created_at', 'desc')
            ->limit(30)
            ->get();
    }

    /**
     * 성과 지표 계산
     */
    protected function getPerformanceMetrics()
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'total_assignments' => StoreAssignment::where('assigned_at', '>=', $thirtyDaysAgo)->count(),
            'completion_rate' => $this->getCompletionRate($thirtyDaysAgo),
            'avg_completion_time' => $this->getAvgCompletionTime($thirtyDaysAgo),
            'on_time_rate' => $this->getOnTimeRate($thirtyDaysAgo),
            'escalation_rate' => $this->getEscalationRate($thirtyDaysAgo),
        ];
    }

    /**
     * 워크플로우 상태 분포
     */
    protected function getWorkflowDistribution()
    {
        return StoreAssignment::select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.module")) as module')
            )
            ->where('assigned_at', '>=', now()->subDays(30))
            ->groupBy('status', 'module')
            ->get()
            ->groupBy('module');
    }

    /**
     * 할당 전략에 따른 담당자 선택
     */
    protected function selectAssignee($assignees, $strategy, $module)
    {
        switch ($strategy) {
            case 'round_robin':
                return $this->roundRobinAssign($assignees, $module);

            case 'least_busy':
                return $this->leastBusyAssign($assignees);

            case 'skill_based':
                return $this->skillBasedAssign($assignees, $module);

            case 'random':
                return $assignees->random();

            default:
                return $assignees->first();
        }
    }

    /**
     * 라운드 로빈 할당
     */
    protected function roundRobinAssign($assignees, $module)
    {
        $lastAssigned = cache()->get("last_assigned_{$module}", 0);
        $nextIndex = ($lastAssigned + 1) % $assignees->count();
        cache()->put("last_assigned_{$module}", $nextIndex, now()->addHour());

        return $assignees->values()->get($nextIndex);
    }

    /**
     * 최소 업무량 기준 할당
     */
    protected function leastBusyAssign($assignees)
    {
        $workloads = [];

        foreach ($assignees as $assignee) {
            $workloads[$assignee->id] = StoreAssignment::where('assigned_to', $assignee->id)
                                                      ->whereIn('status', ['pending', 'in_progress'])
                                                      ->count();
        }

        $leastBusyId = array_keys($workloads, min($workloads))[0];
        return $assignees->where('id', $leastBusyId)->first();
    }

    /**
     * 스킬 기반 할당
     */
    protected function skillBasedAssign($assignees, $module)
    {
        // 모듈별 전문성이 높은 담당자 우선 할당
        $expertAssignees = $assignees->where('is_manager', true);

        if ($expertAssignees->isNotEmpty()) {
            return $this->leastBusyAssign($expertAssignees);
        }

        return $this->leastBusyAssign($assignees);
    }

    /**
     * 완료율 계산
     */
    protected function getCompletionRate($fromDate): float
    {
        $total = StoreAssignment::where('assigned_at', '>=', $fromDate)->count();
        $completed = StoreAssignment::where('assigned_at', '>=', $fromDate)
                                   ->where('status', 'completed')
                                   ->count();

        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    /**
     * 평균 완료 시간 계산
     */
    protected function getAvgCompletionTime($fromDate): float
    {
        return StoreAssignment::where('assigned_at', '>=', $fromDate)
                             ->where('status', 'completed')
                             ->avg(DB::raw('TIMESTAMPDIFF(HOUR, assigned_at, updated_at)')) ?? 0;
    }

    /**
     * 정시 완료율 계산
     */
    protected function getOnTimeRate($fromDate): float
    {
        $total = StoreAssignment::where('assigned_at', '>=', $fromDate)
                               ->where('status', 'completed')
                               ->whereNotNull('due_date')
                               ->count();

        $onTime = StoreAssignment::where('assigned_at', '>=', $fromDate)
                                ->where('status', 'completed')
                                ->whereNotNull('due_date')
                                ->whereRaw('updated_at <= due_date')
                                ->count();

        return $total > 0 ? ($onTime / $total) * 100 : 0;
    }

    /**
     * 에스컬레이션율 계산
     */
    protected function getEscalationRate($fromDate): float
    {
        $total = StoreAssignment::where('assigned_at', '>=', $fromDate)->count();
        $escalated = StoreAssignment::where('assigned_at', '>=', $fromDate)
                                   ->where('metadata->escalated', true)
                                   ->count();

        return $total > 0 ? ($escalated / $total) * 100 : 0;
    }

    /**
     * 미할당 업무 조회
     */
    protected function getUnassignedTasks($module)
    {
        $modelMap = [
            'shipping' => \Jiny\Store\Models\ShippingTracking::class,
            'coupon' => \Jiny\Store\Models\Coupon::class,
            'promotion' => \Jiny\Store\Models\Promotion::class,
            'testimonial' => \Jiny\Store\Models\Testimonial::class,
        ];

        if (!isset($modelMap[$module])) {
            return collect([]);
        }

        $model = $modelMap[$module];

        return $model::whereDoesntHave('assignments', function($query) {
            $query->whereIn('status', ['pending', 'in_progress']);
        })->limit(50)->get();
    }

    /**
     * 할당 가능한 담당자 조회
     */
    protected function getAvailableAssignees($module)
    {
        return User::select('users.id', 'users.name', 'users.email', 'store_permissions.is_manager')
            ->join('store_permissions', 'users.id', '=', 'store_permissions.user_id')
            ->where('store_permissions.module', $module)
            ->where('users.isAdmin', true)
            ->where('users.is_blocked', false)
            ->get();
    }

    /**
     * 모듈 관리자 조회
     */
    protected function getModuleManager($module)
    {
        return User::join('store_permissions', 'users.id', '=', 'store_permissions.user_id')
            ->where('store_permissions.module', $module)
            ->where('store_permissions.is_manager', true)
            ->where('users.isAdmin', true)
            ->where('users.is_blocked', false)
            ->first();
    }
}