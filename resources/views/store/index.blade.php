@extends('jiny-store::layouts.store')

@section('title', $config['title'])

@section('content')
<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">{{ $config['title'] }}</h1>
                <p class="lead mb-4">{{ $config['subtitle'] }}</p>
                <div class="d-flex gap-3">
                    <a href="{{ route('store.products.index') }}" class="btn btn-light btn-lg">
                        <i data-feather="shopping-bag" class="me-2"></i>상품 보기
                    </a>
                    <a href="{{ route('store.services.index') }}" class="btn btn-outline-light btn-lg">
                        <i data-feather="briefcase" class="me-2"></i>서비스 보기
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="/images/hero-shopping.jpg" alt="Shopping" class="img-fluid rounded" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Category Section -->
@if($categories && $categories->count() > 0)
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">인기 카테고리</h2>
            <p class="text-muted">다양한 카테고리의 상품을 만나보세요</p>
        </div>
        <div class="row g-4">
            @foreach($categories as $category)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <a href="{{ route('store.category', $category->slug) }}" class="text-decoration-none">
                    <div class="card h-100 category-card">
                        @if($category->image)
                        <img src="{{ $category->image }}" 
                             alt="{{ $category->title }}" 
                             class="card-img-top" 
                             style="height: 200px; object-fit: cover;">
                        @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i data-feather="{{ $category->icon ?? 'package' }}" class="text-muted" style="width: 48px; height: 48px;"></i>
                        </div>
                        @endif
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ $category->title }}</h5>
                            @if($category->description)
                            <p class="card-text text-muted small">{{ Str::limit($category->description, 60) }}</p>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Featured Products -->
@if($featuredProducts && $featuredProducts->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">추천 상품</h2>
            <p class="text-muted">엄선된 추천 상품을 만나보세요</p>
        </div>
        <div class="row g-4">
            @foreach($featuredProducts->take(8) as $product)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        @if($product->image)
                        <img src="{{ $product->image }}" 
                             alt="{{ $product->title }}" 
                             class="card-img-top" 
                             style="height: 250px; object-fit: cover;">
                        @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                            <i data-feather="image" class="text-muted"></i>
                        </div>
                        @endif
                        <button class="wishlist-btn" onclick="addToWishlist('product', {{ $product->id }})">
                            <i data-feather="heart"></i>
                        </button>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">{{ $product->title }}</h6>
                        @if($product->category_name)
                        <small class="text-muted">{{ $product->category_name }}</small>
                        @endif
                        <p class="card-text flex-grow-1">{{ Str::limit($product->description, 100) }}</p>
                        <div class="mt-auto">
                            @if($product->sale_price && $product->sale_price < $product->price)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="fw-bold text-danger">{{ number_format($product->sale_price) }}원</span>
                                <small class="text-muted text-decoration-line-through">{{ number_format($product->price) }}원</small>
                            </div>
                            @else
                            <div class="fw-bold mb-2">{{ number_format($product->price) }}원</div>
                            @endif
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-sm" onclick="addToCart('product', {{ $product->id }})">
                                    <i data-feather="shopping-cart" class="me-1" style="width: 16px; height: 16px;"></i>
                                    장바구니
                                </button>
                                <a href="{{ route('store.products.show', $product->id) }}" class="btn btn-outline-secondary btn-sm">
                                    상세보기
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('store.products.index') }}" class="btn btn-outline-primary">
                모든 상품 보기 <i data-feather="arrow-right" class="ms-1"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Popular Products -->
@if($popularProducts && $popularProducts->count() > 0)
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">인기 상품</h2>
            <p class="text-muted">많은 고객들이 선택한 인기 상품</p>
        </div>
        <div class="row g-4">
            @foreach($popularProducts->take(4) as $product)
            <div class="col-lg-3 col-md-6">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        @if($product->image)
                        <img src="{{ $product->image }}" 
                             alt="{{ $product->title }}" 
                             class="card-img-top" 
                             style="height: 200px; object-fit: cover;">
                        @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i data-feather="image" class="text-muted"></i>
                        </div>
                        @endif
                        <span class="badge bg-warning position-absolute top-0 start-0 m-2">
                            <i data-feather="star" style="width: 12px; height: 12px;" class="me-1"></i>인기
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">{{ $product->title }}</h6>
                        <div class="fw-bold text-primary">{{ number_format($product->price) }}원</div>
                        <small class="text-muted">주문 {{ $product->order_count }}회</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Featured Services -->
@if($featuredServices && $featuredServices->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">추천 서비스</h2>
            <p class="text-muted">전문적인 서비스를 경험해보세요</p>
        </div>
        <div class="row g-4">
            @foreach($featuredServices->take(6) as $service)
            <div class="col-lg-4 col-md-6">
                <div class="card service-card h-100">
                    @if($service->image)
                    <img src="{{ $service->image }}" 
                         alt="{{ $service->title }}" 
                         class="card-img-top" 
                         style="height: 200px; object-fit: cover;">
                    @else
                    <div class="card-img-top bg-primary bg-gradient d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i data-feather="briefcase" class="text-white" style="width: 48px; height: 48px;"></i>
                    </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $service->title }}</h5>
                        @if($service->category_name)
                        <small class="text-muted">{{ $service->category_name }}</small>
                        @endif
                        <p class="card-text">{{ Str::limit($service->description, 120) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">{{ $service->duration }}</span>
                            <a href="{{ route('store.services.show', $service->id) }}" class="btn btn-outline-primary btn-sm">
                                자세히 보기
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('store.services.index') }}" class="btn btn-outline-primary">
                모든 서비스 보기 <i data-feather="arrow-right" class="ms-1"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Testimonials -->
@if($testimonials && $testimonials->count() > 0)
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">고객 후기</h2>
            <p class="text-muted">고객들의 생생한 후기를 확인하세요</p>
        </div>
        <div class="row g-4">
            @foreach($testimonials->take(3) as $testimonial)
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            @if($testimonial->avatar)
                            <img src="{{ $testimonial->avatar }}" 
                                 alt="{{ $testimonial->customer_name ?: 'Customer' }}" 
                                 class="rounded-circle me-3" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px;">
                                <i data-feather="user"></i>
                            </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $testimonial->customer_name ?: 'Anonymous' }}</h6>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $testimonial->rating)
                                        <i data-feather="star" style="width: 14px; height: 14px; fill: currentColor;"></i>
                                        @else
                                        <i data-feather="star" style="width: 14px; height: 14px;"></i>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <h6 class="card-title">{{ $testimonial->title }}</h6>
                        <p class="card-text">{{ Str::limit($testimonial->content, 150) }}</p>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($testimonial->created_at)->format('Y.m.d') }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Newsletter -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h3 class="fw-bold mb-3">뉴스레터 구독</h3>
                <p class="mb-4">최신 상품 정보와 특별 할인 혜택을 받아보세요</p>
                <form class="d-flex gap-2">
                    <input type="email" class="form-control" placeholder="이메일 주소를 입력하세요">
                    <button type="submit" class="btn btn-light">구독하기</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.category-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.service-card {
    transition: transform 0.2s;
}
.service-card:hover {
    transform: translateY(-2px);
}
</style>
@endpush
