@extends('jiny-store::layouts.app')

@section('title', '주문하기')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('store.cart.index') }}">장바구니</a></li>
<li class="breadcrumb-item active">주문하기</li>
@endsection

@section('content')
<div class="container py-4">
    <!-- Progress Steps -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                <div class="checkout-steps d-flex align-items-center">
                    <div class="step active">
                        <div class="step-number">1</div>
                        <div class="step-title">주문확인</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-title">배송정보</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-title">결제정보</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-title">주문완료</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Items -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">주문 상품 확인</h5>
                </div>
                <div class="card-body">
                    @if($cartItems && $cartItems->count() > 0)
                    @foreach($cartItems as $item)
                    <div class="d-flex align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <!-- Product Image -->
                        <div class="me-3">
                            @if($item->image)
                            <img src="{{ $item->image }}" alt="{{ $item->title }}" 
                                 class="img-fluid rounded" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i data-feather="image" class="text-muted"></i>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Product Info -->
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $item->title }}</h6>
                            <small class="text-muted">
                                {{ $item->type === 'product' ? '상품' : '서비스' }}
                                @if($item->pricing_name && $item->pricing_name !== 'Standard')
                                • {{ $item->pricing_name }}
                                @endif
                            </small>
                        </div>
                        
                        <!-- Quantity & Price -->
                        <div class="text-end">
                            <div class="fw-bold">{{ $item->total_price_formatted }}</div>
                            <small class="text-muted">{{ $item->quantity }}개 × {{ $item->final_price_formatted }}</small>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-4">
                        <i data-feather="shopping-cart" class="text-muted mb-2" style="width: 48px; height: 48px;"></i>
                        <p class="text-muted">장바구니가 비어있습니다.</p>
                        <a href="{{ route('store.products.index') }}" class="btn btn-primary">상품 보기</a>
                    </div>
                    @endif
                </div>
                
                @if($cartItems && $cartItems->count() > 0)
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('store.cart.index') }}" class="btn btn-outline-secondary">
                            <i data-feather="arrow-left" class="me-1"></i>장바구니로 돌아가기
                        </a>
                        <a href="{{ route('store.checkout.shipping') }}" class="btn btn-primary">
                            배송정보 입력 <i data-feather="arrow-right" class="ms-1"></i>
                        </a>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Login/Register Notice for Guests -->
            @guest
            <div class="card mt-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-1">
                                <i data-feather="user" class="me-2"></i>회원가입 또는 로그인
                            </h6>
                            <p class="text-muted mb-0">회원가입하시면 주문내역 관리, 적립금 혜택 등을 받을 수 있습니다.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="/login" class="btn btn-outline-primary me-2">로그인</a>
                            <a href="/register" class="btn btn-primary">회원가입</a>
                        </div>
                    </div>
                </div>
            </div>
            @endguest
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            @if($cartItems && $cartItems->count() > 0)
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-header">
                    <h5 class="mb-0">주문 요약</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>상품 {{ $summary['item_count'] }}개</span>
                        <span>{{ $summary['subtotal_formatted'] }}</span>
                    </div>
                    
                    @if($summary['tax_amount'] > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ $summary['tax_name'] }} ({{ number_format($summary['tax_rate_percent'], 1) }}%)</span>
                        <span>{{ $summary['tax_amount_formatted'] }}</span>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>배송비</span>
                        <span class="text-success">무료</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>총 결제금액</strong>
                        <strong class="text-primary h5">{{ $summary['total_formatted'] }}</strong>
                    </div>
                    
                    <!-- Benefits -->
                    <div class="bg-light rounded p-3 mb-3">
                        <h6 class="mb-2">
                            <i data-feather="gift" class="me-2 text-primary"></i>혜택
                        </h6>
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-1">
                                <i data-feather="check" class="me-1 text-success" style="width: 14px; height: 14px;"></i>
                                무료배송 (5만원 이상)
                            </li>
                            <li class="mb-1">
                                <i data-feather="check" class="me-1 text-success" style="width: 14px; height: 14px;"></i>
                                적립금 {{ number_format($summary['subtotal'] * 0.01) }}원 적립
                            </li>
                            <li>
                                <i data-feather="check" class="me-1 text-success" style="width: 14px; height: 14px;"></i>
                                30일 무료 교환/반품
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Security -->
                    <div class="text-center">
                        <small class="text-muted">
                            <i data-feather="shield" class="me-1"></i>
                            SSL 보안 결제로 안전하게 보호됩니다
                        </small>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.checkout-steps {
    max-width: 600px;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #6c757d;
}

.step.active {
    color: #0d6efd;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
}

.step.active .step-number {
    background: #0d6efd;
    color: white;
}

.step-title {
    font-size: 0.875rem;
    font-weight: 500;
}

.step-line {
    width: 60px;
    height: 2px;
    background: #e9ecef;
    margin: 0 20px;
    margin-top: 20px;
}

.step.active + .step-line {
    background: #0d6efd;
}
</style>
@endpush
