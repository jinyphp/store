<?php

namespace Jiny\Store\Http\Controllers\Admin\Shipping;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Store\Models\ShippingTracking;
use Jiny\Store\Models\StoreAssignment;
use App\Models\User;

/**
 * 배송 담당자 할당 관리 컨트롤러
 */
class AssignmentController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'title' => '배송 담당자 관리',
            'subtitle' => '배송 업무를 담당자별로 할당하고 관리합니다.',
            'module' => 'shipping',
        ];
    }

    /**
     * 배송 담당자 할당 대시보드
     */
    public function index(Request $request)
    {
        // 담당자별 배송 현황
        $assignmentStats = $this->getAssignmentStats();

        // 미할당 배송 목록
        $unassignedShippings = $this->getUnassignedShippings();

        // 담당자 목록 (배송 권한이 있는 사용자)
        $availableAssignees = $this->getAvailableAssignees();

        // 최근 할당 활동
        $recentActivities = $this->getRecentActivities();

        // 마감일 임박 배송
        $urgentShippings = $this->getUrgentShippings();

        return view('jiny-store::admin.shipping.assignments.index', [
            'assignmentStats' => $assignmentStats,
            'unassignedShippings' => $unassignedShippings,
            'availableAssignees' => $availableAssignees,
            'recentActivities' => $recentActivities,
            'urgentShippings' => $urgentShippings,
            'config' => $this->config,
        ]);
    }

    /**
     * 배송 할당
     */
    public function assign(Request $request)
    {
        $request->validate([
            'tracking_ids' => 'required|array',
            'tracking_ids.*' => 'exists:shipping_trackings,id',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $assignedCount = 0;

            foreach ($request->tracking_ids as $trackingId) {
                $tracking = ShippingTracking::find($trackingId);

                if ($tracking && !$tracking->isAssigned()) {
                    $tracking->assignTo(
                        $request->assigned_to,
                        auth()->id(),
                        [
                            'due_date' => $request->due_date,
                            'notes' => $request->notes,
                            'metadata' => [
                                'module' => 'shipping',
                                'tracking_number' => $tracking->tracking_number,
                                'current_status' => $tracking->status,
                            ]
                        ]
                    );

                    $assignedCount++;
                }
            }

            DB::commit();

            return redirect()->back()->with('success',
                "{$assignedCount}개의 배송이 담당자에게 할당되었습니다.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error',
                '배송 할당 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 배송 할당 해제
     */
    public function unassign(Request $request)
    {
        $request->validate([
            'tracking_ids' => 'required|array',
            'tracking_ids.*' => 'exists:shipping_trackings,id',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $unassignedCount = 0;

            foreach ($request->tracking_ids as $trackingId) {
                $tracking = ShippingTracking::find($trackingId);

                if ($tracking && $tracking->isAssigned()) {
                    $tracking->unassign(auth()->id(), $request->reason);
                    $unassignedCount++;
                }
            }

            DB::commit();

            return redirect()->back()->with('success',
                "{$unassignedCount}개의 배송 할당이 해제되었습니다.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error',
                '배송 할당 해제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 배송 담당자 변경
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:store_assignments,id',
            'new_assignee_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $assignment = StoreAssignment::find($request->assignment_id);

        if (!$assignment) {
            return redirect()->back()->with('error', '할당을 찾을 수 없습니다.');
        }

        $assignment->transferTo(
            $request->new_assignee_id,
            auth()->id(),
            $request->reason
        );

        return redirect()->back()->with('success', '배송 담당자가 변경되었습니다.');
    }

    /**
     * 배송 상태 업데이트
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:store_assignments,id',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'comment' => 'nullable|string|max:500',
        ]);

        $assignment = StoreAssignment::find($request->assignment_id);

        if (!$assignment) {
            return redirect()->back()->with('error', '할당을 찾을 수 없습니다.');
        }

        $assignment->changeStatus(
            $request->status,
            auth()->id(),
            $request->comment
        );

        return redirect()->back()->with('success', '배송 상태가 업데이트되었습니다.');
    }

    /**
     * 담당자별 배송 현황 통계
     */
    protected function getAssignmentStats()
    {
        return DB::table('store_assignments')
            ->select(
                'assigned_to',
                'users.name as assignee_name',
                DB::raw('COUNT(*) as total_assignments'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_count'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count'),
                DB::raw('SUM(CASE WHEN due_date < NOW() AND status NOT IN ("completed", "cancelled") THEN 1 ELSE 0 END) as overdue_count')
            )
            ->join('users', 'store_assignments.assigned_to', '=', 'users.id')
            ->where('assignable_type', 'LIKE', '%ShippingTracking%')
            ->whereIn('status', ['pending', 'in_progress', 'completed'])
            ->groupBy('assigned_to', 'users.name')
            ->get();
    }

    /**
     * 미할당 배송 목록
     */
    protected function getUnassignedShippings()
    {
        return ShippingTracking::select('shipping_trackings.*', 'shipping_methods.name as method_name')
            ->leftJoin('shipping_methods', 'shipping_trackings.method_id', '=', 'shipping_methods.id')
            ->whereDoesntHave('assignments', function($query) {
                $query->whereIn('status', ['pending', 'in_progress']);
            })
            ->whereIn('status', ['pending', 'picked_up', 'in_transit'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * 배송 권한이 있는 담당자 목록
     */
    protected function getAvailableAssignees()
    {
        return User::select('users.id', 'users.name', 'users.email')
            ->join('store_permissions', 'users.id', '=', 'store_permissions.user_id')
            ->where('store_permissions.module', 'shipping')
            ->where('users.isAdmin', true)
            ->where('users.is_blocked', false)
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
                'store_assignments.assignable_type',
                'store_assignments.assignable_id'
            )
            ->join('store_assignments', 'store_assignment_logs.assignment_id', '=', 'store_assignments.id')
            ->join('users', 'store_assignment_logs.user_id', '=', 'users.id')
            ->where('store_assignments.assignable_type', 'LIKE', '%ShippingTracking%')
            ->orderBy('store_assignment_logs.created_at', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * 마감일 임박 배송
     */
    protected function getUrgentShippings()
    {
        return StoreAssignment::with(['assignable', 'assignedTo'])
            ->where('assignable_type', 'LIKE', '%ShippingTracking%')
            ->where('due_date', '<=', now()->addDays(2))
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();
    }
}