@extends($layout ?? 'jiny-site::layouts.app')

@section('title', $product->title)
@section('description', Str::limit(strip_tags($product->description), 160))

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">홈</a></li>
            <li class="breadcrumb-item"><a href="{{ route('store.products.index') }}">상품</a></li>
            @if($product->category_name)
                <li class="breadcrumb-item">{{ $product->category_name }}</li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $product->title }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="product-images">
                @if($product->image)
                    <div class="main-image mb-3">
                        <img src="{{ $product->image }}"
                             alt="{{ $product->title }}"
                             class="img-fluid rounded shadow-sm w-100"
                             style="max-height: 500px; object-fit: cover;">
                    </div>
                @else
                    <div class="main-image mb-3 bg-light rounded d-flex align-items-center justify-content-center"
                         style="height: 500px;">
                        <div class="text-center text-muted">
                            <i class="fe fe-package" style="font-size: 5rem;"></i>
                            <p class="mt-2">이미지 없음</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info">
                <!-- Category Badge -->
                @if($product->category_name)
                    <span class="badge bg-primary mb-2">{{ $product->category_name }}</span>
                @endif

                <!-- Product Title -->
                <h1 class="h2 mb-3">{{ $product->title }}</h1>

                <!-- Price -->
                <div class="price-section mb-4">
                    @if($product->price)
                        <div class="price-info">
                            @if($product->sale_price && $product->sale_price < $product->price)
                                <span class="text-muted text-decoration-line-through fs-5 me-2">
                                    ₩{{ number_format($product->price) }}
                                </span>
                                <span class="text-danger fw-bold fs-3">
                                    ₩{{ number_format($product->sale_price) }}
                                </span>
                                <span class="badge bg-danger ms-2">
                                    {{ round((($product->price - $product->sale_price) / $product->price) * 100) }}% 할인
                                </span>
                            @else
                                <span class="fw-bold fs-3">₩{{ number_format($product->price) }}</span>
                            @endif
                        </div>
                    @else
                        <span class="text-muted fs-4">가격 문의</span>
                    @endif
                </div>

                <!-- Product Options -->
                @if($pricing && $pricing->count() > 1)
                    <div class="pricing-options mb-4">
                        <h6>옵션 선택</h6>
                        <div class="list-group">
                            @foreach($pricing as $option)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $option->name ?? '기본 옵션' }}</strong>
                                        @if($option->description)
                                            <br><small class="text-muted">{{ $option->description }}</small>
                                        @endif
                                    </div>
                                    <span class="badge bg-primary">
                                        ₩{{ number_format($option->price) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Add to Cart / Buy Now -->
                <div class="action-buttons mb-4">
                    <div class="row">
                        <div class="col-6">
                            <button class="btn btn-outline-primary btn-lg w-100" onclick="addToCart({{ $product->id }})">
                                <i class="fe fe-shopping-cart me-2"></i>장바구니
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-primary btn-lg w-100" onclick="buyNow({{ $product->id }})">
                                <i class="fe fe-credit-card me-2"></i>바로 구매
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Meta -->
                <div class="product-meta">
                    <div class="row text-sm">
                        <div class="col-6 mb-2">
                            <strong>상품 ID:</strong> {{ $product->id }}
                        </div>
                        <div class="col-6 mb-2">
                            <strong>조회수:</strong> {{ number_format($product->view_count) }}
                        </div>
                        @if($product->tags)
                            <div class="col-12 mb-2">
                                <strong>태그:</strong>
                                @php
                                    $tags = explode(',', $product->tags);
                                @endphp
                                @foreach($tags as $tag)
                                    <span class="badge bg-light text-dark me-1">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    @if($product->description)
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">상품 상세 정보</h5>
                    </div>
                    <div class="card-body">
                        <div class="product-description">
                            {!! $product->description !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reviews Section -->
    @if($reviews && $reviews->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">고객 리뷰</h5>
                        <a href="{{ route('store.products.reviews', $product->id) }}" class="btn btn-sm btn-outline-primary">
                            모든 리뷰 보기
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Review Stats -->
                        @if($reviewStats)
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="display-4 text-warning">{{ number_format($reviewStats->average_rating, 1) }}</div>
                                        <div class="text-muted">
                                            <small>{{ $reviewStats->total_reviews }}개 리뷰</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="rating-breakdown">
                                        @for($i = 5; $i >= 1; $i--)
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="me-2">{{ $i }}점</span>
                                                <div class="progress flex-fill me-2" style="height: 8px;">
                                                    @php
                                                        $fieldName = match($i) {
                                                            5 => 'five_star',
                                                            4 => 'four_star',
                                                            3 => 'three_star',
                                                            2 => 'two_star',
                                                            1 => 'one_star'
                                                        };
                                                        $count = $reviewStats->{$fieldName} ?? 0;
                                                        $percentage = $reviewStats->total_reviews > 0 ? ($count / $reviewStats->total_reviews) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <span class="text-muted small">{{ $count }}</span>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Recent Reviews -->
                        <div class="reviews-list">
                            @foreach($reviews->take(3) as $review)
                                <div class="review-item mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong>{{ $review->customer_name ?? '익명' }}</strong>
                                            <div class="text-warning">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fe fe-star{{ $i <= $review->rating ? ' text-warning' : ' text-muted' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($review->created_at)->format('Y-m-d') }}
                                        </small>
                                    </div>
                                    @if($review->title)
                                        <h6>{{ $review->title }}</h6>
                                    @endif
                                    <p class="mb-0">{{ $review->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Related Products -->
    @if($relatedProducts && $relatedProducts->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">관련 상품</h4>
                <div class="row">
                    @foreach($relatedProducts->take(4) as $relatedProduct)
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card h-100 product-card">
                                @if($relatedProduct->image)
                                    <img src="{{ $relatedProduct->image }}"
                                         class="card-img-top"
                                         alt="{{ $relatedProduct->title }}"
                                         style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                         style="height: 200px;">
                                        <i class="fe fe-package text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ $relatedProduct->title }}</h6>
                                    <div class="mt-auto">
                                        @if($relatedProduct->price)
                                            <div class="price mb-2">
                                                @if($relatedProduct->sale_price && $relatedProduct->sale_price < $relatedProduct->price)
                                                    <small class="text-muted text-decoration-line-through me-2">
                                                        ₩{{ number_format($relatedProduct->price) }}
                                                    </small>
                                                    <span class="text-danger fw-bold">
                                                        ₩{{ number_format($relatedProduct->sale_price) }}
                                                    </span>
                                                @else
                                                    <span class="fw-bold">₩{{ number_format($relatedProduct->price) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        <a href="{{ route('store.products.show', $relatedProduct->id) }}"
                                           class="btn btn-outline-primary btn-sm w-100">
                                            자세히 보기
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.product-card {
    transition: transform 0.2s;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.rating-breakdown .progress {
    background-color: #e9ecef;
}
.review-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
</style>
@endpush

@push('scripts')
<script>
function addToCart(productId) {
    // TODO: Implement add to cart functionality
    alert('장바구니 기능은 준비 중입니다.');
}

function buyNow(productId) {
    // TODO: Implement buy now functionality
    alert('바로 구매 기능은 준비 중입니다.');
}

// 상품 조회수 증가 (AJAX)
fetch(`/api/products/${{{ $product->id }}}/view`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
}).catch(error => {
    console.log('View count update failed:', error);
});
</script>
@endpush