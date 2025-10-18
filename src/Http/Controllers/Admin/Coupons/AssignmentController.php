<?php

namespace Jiny\Store\Http\Controllers\Admin\Coupons;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Store\Models\Coupon;
use Jiny\Store\Models\StoreAssignment;
use App\Models\User;

/**
 * 쿠폰 담당자 할당 관리 컨트롤러
 */
class AssignmentController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'title' => '쿠폰 담당자 관리',
            'subtitle' => '쿠폰 생성, 관리, 배포 업무를 담당자별로 할당합니다.',
            'module' => 'coupon',
        ];
    }

    /**
     * 쿠폰 담당자 할당 대시보드
     */
    public function index(Request $request)
    {
        // 담당자별 쿠폰 현황
        $assignmentStats = $this->getAssignmentStats();

        // 미할당 쿠폰 목록
        $unassignedCoupons = $this->getUnassignedCoupons();

        // 담당자 목록 (쿠폰 권한이 있는 사용자)
        $availableAssignees = $this->getAvailableAssignees();

        // 최근 할당 활동
        $recentActivities = $this->getRecentActivities();

        // 만료 예정 쿠폰
        $expiringCoupons = $this->getExpiringCoupons();

        // 성과 지표
        $performanceMetrics = $this->getPerformanceMetrics();

        return view('jiny-store::admin.coupons.assignments.index', [
            'assignmentStats' => $assignmentStats,
            'unassignedCoupons' => $unassignedCoupons,
            'availableAssignees' => $availableAssignees,
            'recentActivities' => $recentActivities,
            'expiringCoupons' => $expiringCoupons,
            'performanceMetrics' => $performanceMetrics,
            'config' => $this->config,
        ]);
    }

    /**
     * 쿠폰 할당
     */
    public function assign(Request $request)
    {
        $request->validate([
            'coupon_ids' => 'required|array',
            'coupon_ids.*' => 'exists:coupons,id',
            'assigned_to' => 'required|exists:users,id',
            'assignment_type' => 'required|in:creation,management,distribution,analysis',
            'due_date' => 'nullable|date|after:today',
            'priority' => 'required|in:low,medium,high,urgent',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $assignedCount = 0;

            foreach ($request->coupon_ids as $couponId) {
                $coupon = Coupon::find($couponId);

                if ($coupon) {
                    // 기존 동일 타입 할당 확인
                    $existingAssignment = $coupon->assignments()
                        ->where('assigned_to', $request->assigned_to)
                        ->whereIn('status', ['pending', 'in_progress'])
                        ->where('metadata->assignment_type', $request->assignment_type)
                        ->first();

                    if (!$existingAssignment) {
                        $coupon->assignTo(
                            $request->assigned_to,
                            auth()->id(),
                            [
                                'due_date' => $request->due_date,
                                'notes' => $request->notes,
                                'metadata' => [
                                    'module' => 'coupon',
                                    'assignment_type' => $request->assignment_type,
                                    'priority' => $request->priority,
                                    'coupon_code' => $coupon->code,
                                    'coupon_type' => $coupon->type,
                                    'expires_at' => $coupon->expires_at?->format('Y-m-d H:i:s'),
                                ]
                            ]
                        );

                        $assignedCount++;
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success',
                "{$assignedCount}개의 쿠폰이 담당자에게 할당되었습니다.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error',
                '쿠폰 할당 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 벌크 할당 (조건별 자동 할당)
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'assignment_rules' => 'required|array',
            'assignment_rules.*.condition' => 'required|in:type,expiry,usage_rate,status',
            'assignment_rules.*.value' => 'required',
            'assignment_rules.*.assigned_to' => 'required|exists:users,id',
            'assignment_rules.*.assignment_type' => 'required|in:creation,management,distribution,analysis',
        ]);

        DB::beginTransaction();
        try {
            $totalAssigned = 0;

            foreach ($request->assignment_rules as $rule) {
                $query = Coupon::query();

                // 조건별 쿼리 빌드
                switch ($rule['condition']) {
                    case 'type':
                        $query->where('type', $rule['value']);
                        break;
                    case 'expiry':
                        $query->where('expires_at', '<=', now()->addDays($rule['value']));
                        break;
                    case 'usage_rate':
                        $query->whereRaw('(used_count / NULLIF(usage_limit, 0)) * 100 >= ?', [$rule['value']]);
                        break;
                    case 'status':
                        $query->where('is_active', $rule['value'] === 'active');
                        break;
                }

                // 미할당 쿠폰만 선택
                $coupons = $query->whereDoesntHave('assignments', function($q) use ($rule) {
                    $q->whereIn('status', ['pending', 'in_progress'])
                      ->where('metadata->assignment_type', $rule['assignment_type']);
                })->get();

                foreach ($coupons as $coupon) {
                    $coupon->assignTo(
                        $rule['assigned_to'],
                        auth()->id(),
                        [
                            'notes' => "자동 할당 ({$rule['condition']}: {$rule['value']})",
                            'metadata' => [
                                'module' => 'coupon',
                                'assignment_type' => $rule['assignment_type'],
                                'priority' => 'medium',
                                'auto_assigned' => true,
                                'rule_condition' => $rule['condition'],
                                'rule_value' => $rule['value'],
                            ]
                        ]
                    );

                    $totalAssigned++;
                }
            }

            DB::commit();

            return redirect()->back()->with('success',
                "조건에 따라 {$totalAssigned}개의 쿠폰이 자동 할당되었습니다.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error',
                '벌크 할당 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 할당 워크플로우 업데이트
     */
    public function updateWorkflow(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:store_assignments,id',
            'action' => 'required|in:start,pause,complete,escalate,transfer',
            'target_user_id' => 'nullable|exists:users,id',
            'comment' => 'nullable|string|max:500',
        ]);

        $assignment = StoreAssignment::find($request->assignment_id);

        if (!$assignment) {
            return redirect()->back()->with('error', '할당을 찾을 수 없습니다.');
        }

        switch ($request->action) {
            case 'start':
                $assignment->changeStatus('in_progress', auth()->id(), $request->comment);
                break;

            case 'pause':
                $assignment->changeStatus('pending', auth()->id(), $request->comment);
                break;

            case 'complete':
                $assignment->changeStatus('completed', auth()->id(), $request->comment);
                break;

            case 'escalate':
                // 우선순위 상승 및 관리자에게 알림
                $metadata = $assignment->metadata;
                $metadata['escalated'] = true;
                $metadata['escalated_at'] = now()->toISOString();
                $assignment->update(['metadata' => $metadata]);
                break;

            case 'transfer':
                if ($request->target_user_id) {
                    $assignment->transferTo($request->target_user_id, auth()->id(), $request->comment);
                }
                break;
        }

        return redirect()->back()->with('success', '워크플로우가 업데이트되었습니다.');
    }

    /**
     * 담당자별 쿠폰 현황 통계
     */
    protected function getAssignmentStats()
    {
        return DB::table('store_assignments')
            ->select(
                'assigned_to',
                'users.name as assignee_name',
                'metadata->assignment_type as assignment_type',
                DB::raw('COUNT(*) as total_assignments'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_count'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count'),
                DB::raw('AVG(CASE WHEN status = "completed" THEN TIMESTAMPDIFF(HOUR, assigned_at, updated_at) END) as avg_completion_hours')
            )
            ->join('users', 'store_assignments.assigned_to', '=', 'users.id')
            ->where('assignable_type', 'LIKE', '%Coupon%')
            ->groupBy('assigned_to', 'users.name', 'metadata->assignment_type')
            ->get();
    }

    /**
     * 미할당 쿠폰 목록
     */
    protected function getUnassignedCoupons()
    {
        return Coupon::select('coupons.*')
            ->whereDoesntHave('assignments', function($query) {
                $query->whereIn('status', ['pending', 'in_progress']);
            })
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * 쿠폰 권한이 있는 담당자 목록
     */
    protected function getAvailableAssignees()
    {
        return User::select('users.id', 'users.name', 'users.email', 'store_permissions.is_manager')
            ->join('store_permissions', 'users.id', '=', 'store_permissions.user_id')
            ->where('store_permissions.module', 'coupon')
            ->where('users.isAdmin', true)
            ->where('users.is_blocked', false)
            ->orderBy('store_permissions.is_manager', 'desc')
            ->orderBy('users.name')
            ->get();
    }

    /**
     * 최근 할당 활동
     */
    protected function getRecentActivities()
    {
        return DB::table('store_assignment_logs')
            ->select(
                'store_assignment_logs.*',
                'users.name as user_name',
                'coupons.name as coupon_name',
                'coupons.code as coupon_code'
            )
            ->join('store_assignments', 'store_assignment_logs.assignment_id', '=', 'store_assignments.id')
            ->join('users', 'store_assignment_logs.user_id', '=', 'users.id')
            ->join('coupons', 'store_assignments.assignable_id', '=', 'coupons.id')
            ->where('store_assignments.assignable_type', 'LIKE', '%Coupon%')
            ->orderBy('store_assignment_logs.created_at', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * 만료 예정 쿠폰
     */
    protected function getExpiringCoupons()
    {
        return Coupon::with(['assignments' => function($query) {
                $query->whereIn('status', ['pending', 'in_progress']);
            }])
            ->where('expires_at', '<=', now()->addDays(7))
            ->where('expires_at', '>=', now())
            ->where('is_active', true)
            ->orderBy('expires_at')
            ->limit(10)
            ->get();
    }

    /**
     * 성과 지표
     */
    protected function getPerformanceMetrics()
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'total_coupons_created' => Coupon::where('created_at', '>=', $thirtyDaysAgo)->count(),
            'total_coupon_usage' => DB::table('coupon_usages')->where('used_at', '>=', $thirtyDaysAgo)->count(),
            'total_discount_amount' => DB::table('coupon_usages')->where('used_at', '>=', $thirtyDaysAgo)->sum('discount_amount'),
            'avg_assignment_completion_time' => DB::table('store_assignments')
                ->where('assignable_type', 'LIKE', '%Coupon%')
                ->where('status', 'completed')
                ->where('updated_at', '>=', $thirtyDaysAgo)
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, assigned_at, updated_at)')),
            'assignment_completion_rate' => $this->getAssignmentCompletionRate($thirtyDaysAgo),
            'top_performers' => $this->getTopPerformers($thirtyDaysAgo),
        ];
    }

    /**
     * 할당 완료율 계산
     */
    protected function getAssignmentCompletionRate($fromDate): float
    {
        $total = StoreAssignment::where('assignable_type', 'LIKE', '%Coupon%')
                                ->where('assigned_at', '>=', $fromDate)
                                ->count();

        $completed = StoreAssignment::where('assignable_type', 'LIKE', '%Coupon%')
                                   ->where('assigned_at', '>=', $fromDate)
                                   ->where('status', 'completed')
                                   ->count();

        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    /**
     * 최고 성과자 목록
     */
    protected function getTopPerformers($fromDate)
    {
        return DB::table('store_assignments')
            ->select(
                'users.name',
                DB::raw('COUNT(*) as completed_assignments'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, assigned_at, updated_at)) as avg_completion_hours')
            )
            ->join('users', 'store_assignments.assigned_to', '=', 'users.id')
            ->where('store_assignments.assignable_type', 'LIKE', '%Coupon%')
            ->where('store_assignments.status', 'completed')
            ->where('store_assignments.updated_at', '>=', $fromDate)
            ->groupBy('users.id', 'users.name')
            ->orderBy('completed_assignments', 'desc')
            ->limit(5)
            ->get();
    }
}