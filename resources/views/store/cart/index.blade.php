@extends('jiny-store::layouts.store')

@section('title', '장바구니')

@section('breadcrumb')
<li class="breadcrumb-item active">장바구니</li>
@endsection

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">장바구니</h2>
        </div>
    </div>

    @if($cartItems && $cartItems->count() > 0)
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">상품 목록</h5>
                    <span class="text-muted">{{ $summary['item_count'] }}개 상품</span>
                </div>
                <div class="card-body p-0">
                    @foreach($cartItems as $index => $item)
                    <div class="cart-item py-4 {{ $index > 0 ? 'border-top' : '' }}" data-cart-id="{{ $item->id }}">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <!-- Product Image -->
                                <div class="col-md-2">
                                    @if($item->image)
                                        <img src="{{ $item->image }}" alt="{{ $item->title }}"
                                             class="img-fluid rounded" style="max-height: 80px;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 80px; height: 80px;">
                                            <i data-feather="image" class="text-muted"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Info -->
                                <div class="col-md-4">
                                    <div>
                                        <a href="/{{ $item->item_type }}/{{ $item->item_id }}" class="text-decoration-none">
                                            {{ $item->title }}
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            {{ $item->item_type === 'product' ? '상품' : '서비스' }}
                                        </small>
                                        @if($item->pricing_name && $item->pricing_name !== 'Standard')
                                        <br>
                                        <small class="text-info">
                                            <i class="fe fe-tag me-1"></i>{{ $item->pricing_name }}
                                        </small>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quantity -->
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                                {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                            <i data-feather="minus" style="width: 14px; height: 14px;"></i>
                                        </button>
                                        <input type="number" class="form-control text-center" 
                                               value="{{ $item->quantity }}" 
                                               min="1" max="99"
                                               onchange="updateQuantity({{ $item->id }}, this.value)">
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                                {{ $item->quantity >= 99 ? 'disabled' : '' }}>
                                            <i data-feather="plus" style="width: 14px; height: 14px;"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Unit Price -->
                                <div class="col-md-2 text-center">
                                    <p class="mb-0 fw-bold">{{ $item->final_price_formatted ?? '0원' }}</p>
                                    @if($item->quantity > 1)
                                        <small class="text-muted">단가: {{ $item->final_price_formatted ?? '0원' }}</small>
                                    @endif
                                </div>

                                <!-- Total Price -->
                                <div class="col-md-1 text-center">
                                    <p class="mb-1 fw-bold h6">{{ $item->total_price_formatted ?? '0원' }}</p>
                                </div>

                                <!-- Remove -->
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="removeFromCart({{ $item->id }})"
                                            title="삭제">
                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('store.products.index') }}" class="btn btn-outline-primary">
                            <i data-feather="arrow-left" class="me-1"></i>계속 쇼핑하기
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="clearCart()">
                            <i data-feather="trash-2" class="me-1"></i>장바구니 비우기
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">주문 요약</h5>
                </div>
                <div class="card-body">
                    <!-- Currency Info -->
                    <div class="mb-3 text-center">
                        <small class="text-muted">
                            <i data-feather="globe" class="me-1"></i>
                            {{ $currency['currency_symbol'] ?? '₩' }} {{ $currency['user_currency'] ?? 'KRW' }}
                        </small>
                    </div>

                    <!-- Summary -->
                    <div class="d-flex justify-content-between mb-2">
                        <span>상품 {{ $summary['item_count'] }}개</span>
                        <span>{{ $summary['subtotal_formatted'] ?? '0원' }}</span>
                    </div>

                    @if($summary['tax_amount'] > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <span>{{ $summary['tax_name'] ?? 'VAT' }} ({{ number_format($summary['tax_rate_percent'] ?? 0, 1) }}%)</span>
                            <br>
                            <small class="text-muted">{{ $summary['country_name'] ?? 'Unknown' }} 적용</small>
                        </div>
                        <span>{{ $summary['tax_amount_formatted'] ?? '0원' }}</span>
                    </div>
                    @endif

                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <strong>총 주문금액</strong>
                        <strong class="text-primary h5">{{ $summary['total_formatted'] ?? '0원' }}</strong>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('store.checkout.index') }}" class="btn btn-primary btn-lg">
                            <i data-feather="credit-card" class="me-2"></i>주문하기
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                            <i data-feather="printer" class="me-2"></i>견적서 출력
                        </button>
                    </div>

                    <!-- Payment Methods -->
                    <div class="mt-4">
                        <h6 class="mb-2">결제 방법</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <img src="/images/payment/visa.png" alt="Visa" class="img-fluid" style="height: 24px;">
                            <img src="/images/payment/mastercard.png" alt="MasterCard" class="img-fluid" style="height: 24px;">
                            <img src="/images/payment/paypal.png" alt="PayPal" class="img-fluid" style="height: 24px;">
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="mt-3">
                        <small class="text-muted">
                            <i data-feather="shield" class="me-1"></i>
                            SSL 보안 결제로 안전하게 보호됩니다
                        </small>
                    </div>
                </div>
            </div>

            <!-- Recently Viewed -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">최근 본 상품</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small text-center">최근 본 상품이 없습니다</p>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Empty Cart -->
    <div class="text-center py-5">
        <i data-feather="shopping-cart" style="width: 64px; height: 64px;" class="text-muted mb-3"></i>
        <h4 class="text-muted">장바구니가 비어있습니다</h4>
        <p class="text-muted">원하는 상품을 장바구니에 담아보세요.</p>
        <div class="mt-4">
            <a href="{{ route('store.products.index') }}" class="btn btn-primary me-2">
                <i data-feather="shopping-bag" class="me-2"></i>상품 보기
            </a>
            <a href="{{ route('store.services.index') }}" class="btn btn-outline-primary">
                <i data-feather="briefcase" class="me-2"></i>서비스 보기
            </a>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// 수량 업데이트
function updateQuantity(cartId, quantity) {
    if (quantity < 1) return;
    
    const formData = new FormData();
    formData.append('quantity', quantity);
    formData.append('_method', 'PUT');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`{{ route('store.cart.update', '') }}/${cartId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // 페이지 새로고침으로 업데이트된 내용 표시
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Update quantity error:', error);
        showNotification('error', '수량 업데이트에 실패했습니다.');
    });
}

// 장바구니에서 제거
function removeFromCart(cartId) {
    if (!confirm('이 상품을 장바구니에서 제거하시겠습니까?')) return;
    
    const formData = new FormData();
    formData.append('_method', 'DELETE');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`{{ route('store.cart.remove', '') }}/${cartId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
            loadCartCount();
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Remove from cart error:', error);
        showNotification('error', '상품 제거에 실패했습니다.');
    });
}

// 장바구니 비우기
function clearCart() {
    if (!confirm('장바구니를 모두 비우시겠습니까?')) return;
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch('{{ route("store.cart.clear") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
            loadCartCount();
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Clear cart error:', error);
        showNotification('error', '장바구니 비우기에 실패했습니다.');
    });
}
</script>
@endpush
