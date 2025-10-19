@extends('jiny-store::layouts.app')

@section('title', '찜목록')

@section('breadcrumb')
<li class="breadcrumb-item active">찜목록</li>
@endsection

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>찜목록</h2>
                @if($wishlistItems && $wishlistItems->count() > 0)
                <button type="button" class="btn btn-outline-danger" onclick="clearWishlist()">
                    <i data-feather="trash-2" class="me-1"></i>전체 삭제
                </button>
                @endif
            </div>
        </div>
    </div>

    @if($wishlistItems && $wishlistItems->count() > 0)
    <div class="row g-4">
        @foreach($wishlistItems as $item)
        <div class="col-lg-3 col-md-4 col-sm-6" data-wishlist-id="{{ $item->id }}">
            <div class="card product-card h-100">
                <div class="position-relative">
                    @if($item->image)
                    <img src="{{ $item->image }}" 
                         alt="{{ $item->title }}" 
                         class="card-img-top" 
                         style="height: 250px; object-fit: cover;">
                    @else
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                        <i data-feather="image" class="text-muted"></i>
                    </div>
                    @endif
                    
                    <!-- Remove from Wishlist -->
                    <button class="position-absolute top-0 end-0 m-2 btn btn-sm btn-danger" 
                            onclick="removeFromWishlist({{ $item->id }})"
                            title="찜목록에서 제거">
                        <i data-feather="x" style="width: 16px; height: 16px;"></i>
                    </button>
                    
                    <!-- Availability Status -->
                    @if(!$item->available)
                    <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white text-center py-2">
                        <small>현재 판매하지 않는 상품입니다</small>
                    </div>
                    @elseif(isset($item->stock_status))
                        @if($item->stock_status === 'out_of_stock')
                        <div class="position-absolute bottom-0 start-0 end-0 bg-danger text-white text-center py-2">
                            <small>품절</small>
                        </div>
                        @elseif($item->stock_status === 'low_stock')
                        <div class="position-absolute bottom-0 start-0 end-0 bg-warning text-dark text-center py-2">
                            <small>재고 부족</small>
                        </div>
                        @endif
                    @endif
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">
                        @if($item->available)
                        <a href="{{ route('store.' . $item->type . 's.show', $item->item_id) }}" class="text-decoration-none text-dark">
                            {{ $item->title }}
                        </a>
                        @else
                        {{ $item->title }}
                        @endif
                    </h6>
                    
                    <small class="text-muted">{{ $item->type === 'product' ? '상품' : '서비스' }}</small>
                    
                    @if($item->description)
                    <p class="card-text flex-grow-1 small">{{ Str::limit($item->description, 80) }}</p>
                    @endif
                    
                    <!-- Price -->
                    @if(isset($item->price_formatted))
                    <div class="mb-3">
                        <span class="fw-bold text-primary">{{ $item->price_formatted }}</span>
                    </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    <div class="mt-auto">
                        @if($item->available && ($item->stock_status !== 'out_of_stock'))
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-sm" onclick="addToCart('{{ $item->type }}', {{ $item->item_id }})">
                                <i data-feather="shopping-cart" class="me-1" style="width: 16px; height: 16px;"></i>
                                장바구니 담기
                            </button>
                            <a href="{{ route('store.' . $item->type . 's.show', $item->item_id) }}" class="btn btn-outline-secondary btn-sm">
                                상세보기
                            </a>
                        </div>
                        @else
                        <div class="d-grid">
                            @if($item->available)
                            <a href="{{ route('store.' . $item->type . 's.show', $item->item_id) }}" class="btn btn-outline-secondary btn-sm">
                                상세보기
                            </a>
                            @else
                            <button class="btn btn-secondary btn-sm" disabled>
                                현재 이용불가
                            </button>
                            @endif
                        </div>
                        @endif
                    </div>
                    
                    <!-- Added Date -->
                    <small class="text-muted mt-2">
                        <i data-feather="clock" style="width: 12px; height: 12px;" class="me-1"></i>
                        {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Recommendation Section -->
    <div class="mt-5">
        <hr>
        <div class="text-center">
            <h4>이런 상품은 어떠세요?</h4>
            <p class="text-muted">찜목록을 기반으로 추천하는 상품입니다</p>
            <div class="mt-3">
                <a href="{{ route('store.products.index') }}" class="btn btn-outline-primary me-2">
                    <i data-feather="shopping-bag" class="me-2"></i>더 많은 상품 보기
                </a>
                <a href="{{ route('store.services.index') }}" class="btn btn-outline-success">
                    <i data-feather="briefcase" class="me-2"></i>서비스 둘러보기
                </a>
            </div>
        </div>
    </div>
    @else
    <!-- Empty Wishlist -->
    <div class="text-center py-5">
        <i data-feather="heart" style="width: 64px; height: 64px;" class="text-muted mb-3"></i>
        <h4 class="text-muted">찜목록이 비어있습니다</h4>
        <p class="text-muted">마음에 드는 상품이나 서비스를 찜목록에 추가해보세요.</p>
        <div class="mt-4">
            <a href="{{ route('store.products.index') }}" class="btn btn-primary me-2">
                <i data-feather="shopping-bag" class="me-2"></i>상품 보기
            </a>
            <a href="{{ route('store.services.index') }}" class="btn btn-outline-primary">
                <i data-feather="briefcase" class="me-2"></i>서비스 보기
            </a>
        </div>
        
        <!-- Tips -->
        <div class="mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">💡 찜목록 활용 팁</h6>
                            <ul class="list-unstyled text-start small">
                                <li class="mb-2">
                                    <i data-feather="check" class="me-2 text-success" style="width: 16px; height: 16px;"></i>
                                    관심 있는 상품의 하트 아이콘을 클릭하여 찜목록에 추가하세요
                                </li>
                                <li class="mb-2">
                                    <i data-feather="check" class="me-2 text-success" style="width: 16px; height: 16px;"></i>
                                    나중에 구매하고 싶은 상품을 저장해두고 비교해보세요
                                </li>
                                <li class="mb-2">
                                    <i data-feather="check" class="me-2 text-success" style="width: 16px; height: 16px;"></i>
                                    찜한 상품의 가격 변동이나 할인 소식을 받아보세요
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// 찜목록에서 제거
function removeFromWishlist(wishlistId) {
    if (!confirm('이 상품을 찜목록에서 제거하시겠습니까?')) return;
    
    const formData = new FormData();
    formData.append('_method', 'DELETE');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`{{ route('store.wishlist.remove', '') }}/${wishlistId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 해당 아이템 제거
            const itemElement = document.querySelector(`[data-wishlist-id="${wishlistId}"]`);
            if (itemElement) {
                itemElement.remove();
            }
            
            // 위시리스트 카운트 업데이트
            loadWishlistCount();
            
            // 페이지가 비어있으면 새로고침
            const remainingItems = document.querySelectorAll('[data-wishlist-id]');
            if (remainingItems.length === 0) {
                location.reload();
            }
            
            showNotification('success', data.message);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Remove from wishlist error:', error);
        showNotification('error', '찜목록에서 제거하는데 실패했습니다.');
    });
}

// 찜목록 전체 삭제
function clearWishlist() {
    if (!confirm('찜목록을 모두 비우시겠습니까?')) return;
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch('{{ route("store.wishlist.clear") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
            loadWishlistCount();
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Clear wishlist error:', error);
        showNotification('error', '찜목록 비우기에 실패했습니다.');
    });
}
</script>
@endpush
