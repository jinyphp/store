@extends($layout ?? 'jiny-store::layouts.admin.sidebar')

@section('title', $config['title'])

@section('content')
<div class="container-fluid px-4">
    <!-- 페이지 헤더 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $config['title'] }}</h1>
            <p class="mb-0 text-muted">{{ $config['subtitle'] }}</p>
        </div>
        <div class="btn-group" role="group">
            <a href="/admin/store/products" class="btn btn-primary">
                <i class="fe fe-package"></i> 상품 관리
            </a>
            <a href="/admin/store/orders" class="btn btn-outline-primary">
                <i class="fe fe-file-text"></i> 주문 관리
            </a>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                총 상품수
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_products']) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                활성: {{ number_format($stats['active_products']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fe fe-package fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                총 주문수
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_orders']) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                대기중: {{ number_format($stats['pending_orders']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fe fe-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                총 고객수
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_customers']) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                장바구니: {{ number_format($stats['cart_items']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fe fe-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                총 매출
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₩{{ number_format($stats['total_revenue']) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                이번 달: ₩{{ number_format($stats['month_revenue']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fe fe-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 오늘의 현황 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">오늘의 현황</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center">
                            <div class="h4 mb-0 text-primary">{{ number_format($stats['today_orders']) }}</div>
                            <div class="text-xs text-muted">오늘 주문</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h4 mb-0 text-success">{{ number_format($stats['month_orders']) }}</div>
                            <div class="text-xs text-muted">이번 달 주문</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">매출 추이 (최근 7일)</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" width="100" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- 최근 주문 & 인기 상품 -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">최근 주문</h6>
                    <a href="/admin/store/orders" class="btn btn-sm btn-primary">
                        전체 보기 <i class="fe fe-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>주문번호</th>
                                        <th>고객</th>
                                        <th>금액</th>
                                        <th>상태</th>
                                        <th>주문일</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="/admin/store/orders/{{ $order->id }}" class="text-decoration-none">
                                                #{{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="font-weight-bold">{{ $order->customer_name ?: '게스트' }}</div>
                                                    <div class="text-muted small">{{ $order->customer_email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>₩{{ number_format($order->total_amount) }}</td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">대기중</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge badge-success">완료</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-danger">취소</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ $order->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $order->created_at->format('m/d H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fe fe-shopping-cart fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">아직 주문이 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">인기 상품</h6>
                    <a href="/admin/store/products" class="btn btn-sm btn-primary">
                        전체 보기 <i class="fe fe-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if($topProducts->count() > 0)
                        @foreach($topProducts as $product)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="{{ $product->title }}"
                                         class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                        <i class="fe fe-image text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="font-weight-bold">{{ Str::limit($product->title, 20) }}</div>
                                <div class="text-muted small">
                                    조회: {{ number_format($product->view_count) }}회
                                </div>
                                <div class="text-primary small">
                                    @if($product->sale_price && $product->sale_price < $product->price)
                                        <span class="text-decoration-line-through text-muted">₩{{ number_format($product->price) }}</span>
                                        ₩{{ number_format($product->sale_price) }}
                                    @else
                                        ₩{{ number_format($product->price) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fe fe-package fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">상품이 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 빠른 작업 -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">빠른 작업</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <a href="/admin/store/products/create" class="btn btn-outline-primary btn-block">
                        <i class="fe fe-plus-circle"></i> 새 상품 추가
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="/admin/store/categories" class="btn btn-outline-success btn-block">
                        <i class="fe fe-grid"></i> 카테고리 관리
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="/admin/store/orders?status=pending" class="btn btn-outline-warning btn-block">
                        <i class="fe fe-clock"></i> 대기 주문
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="/admin/store/settings" class="btn btn-outline-info btn-block">
                        <i class="fe fe-settings"></i> 스토어 설정
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 매출 차트
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = @json($salesData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(item => item.label),
            datasets: [{
                label: '매출 (원)',
                data: salesData.map(item => item.amount),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₩' + value.toLocaleString();
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 3,
                    hoverRadius: 5
                }
            }
        }
    });
});
</script>

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.btn-block {
    display: block;
    width: 100%;
}
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
</style>
@endpush
