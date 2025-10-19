@extends('jiny-store::layouts.app')

@section('title', $config['title'])

@section('content')
<!-- Hero Section -->
<section class="py-xl-8 py-6 bg-primary">
    <div class="container py-xl-6">
        <div class="row align-items-center gy-6 gy-xl-0">
            <div class="col-lg-5 col-xxl-5 col-12">
                <div class="d-flex flex-column gap-5">
                    <div class="d-flex flex-row gap-2 align-items-center">
                        <span>🛒</span>
                        <span class="text-white fw-semibold">
                            <span>다양한 상품을 만나보세요</span>
                        </span>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex flex-column gap-2">
                            <h1 class="mb-0 display-2 fw-bolder text-white">{{ $config['title'] }}</h1>
                            <p class="mb-0 text-white">{{ $config['subtitle'] }}</p>
                        </div>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                            <li class="d-flex flex-row gap-2">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                    </svg>
                                </span>
                                <span class="text-white">다양한 카테고리</span>
                            </li>
                            <li class="d-flex flex-row gap-2">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                    </svg>
                                </span>
                                <span class="text-white">안전한 쇼핑</span>
                            </li>
                            <li class="d-flex flex-row gap-2">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                    </svg>
                                </span>
                                <span class="text-white">빠른 배송</span>
                            </li>
                        </ul>
                    </div>
                    <div class="d-grid d-md-flex flex-row gap-2">
                        <a href="{{ route('store.products.index') }}" class="btn btn-warning btn-lg">상품 둘러보기</a>
                        <a href="{{ route('store.cart.index') }}" class="btn btn-outline-light btn-lg">장바구니</a>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 offset-xxl-1 col-lg-7 col-12">
                <div class="row gx-0 gy-4 gy-lg-0">
                    <div class="col-md-6 flex-column justify-content-start align-items-center d-none d-md-flex">
                        <div class="position-relative me-n7">
                            <div class="position-absolute bottom-25 start-0 ms-n8 end-0 d-flex flex-column align-items-start">
                                <div class="bg-white rounded-pill py-2 px-3 shadow mb-2 d-inline-block">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7 4V2C7 1.45 7.45 1 8 1H16C16.55 1 17 1.45 17 2V4H20C20.55 4 21 4.45 21 5S20.55 6 20 6H19V19C19 20.1 18.1 21 17 21H7C5.9 21 5 20.1 5 19V6H4C3.45 6 3 5.55 3 5S3.45 4 4 4H7ZM9 3V4H15V3H9ZM7 6V19H17V6H7Z" fill="#F59E0B"/>
                                    </svg>
                                    <span class="text-dark fw-semibold">{{ $categories->count() }}+ 카테고리</span>
                                </div>
                                <div class="bg-white rounded-pill py-2 px-3 shadow mb-2 d-inline-block">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.9 1 3 1.9 3 3V21C3 22.1 3.9 23 5 23H19C20.1 23 21 22.1 21 21V9H21ZM19 21H5V3H14V9H19V21Z" fill="#139A74"/>
                                    </svg>
                                    <span class="text-dark fw-semibold">{{ $featuredProducts->count() }}+ 추천 상품</span>
                                </div>
                                <div class="bg-white rounded-pill py-2 px-3 shadow">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 12L10 17L20 7" stroke="#E53E3E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="text-dark fw-semibold">품질 보증</span>
                                </div>
                            </div>
                            <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2340&q=80"
                                 alt="Shopping" class="img-fluid rounded-3" style="width: 400px; height: 300px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-6 bg-white">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="h2 fw-bold text-primary mb-0">{{ $categories->count() }}+</div>
                    <div class="text-muted">카테고리</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="h2 fw-bold text-success mb-0">{{ $featuredProducts->count() }}+</div>
                    <div class="text-muted">추천 상품</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="h2 fw-bold text-warning mb-0">{{ $popularProducts->count() }}+</div>
                    <div class="text-muted">인기 상품</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="h2 fw-bold text-info mb-0">{{ $testimonials->count() }}+</div>
                    <div class="text-muted">고객 후기</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
@if($categories->count() > 0)
<section class="py-xl-8 py-6">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column gap-2 text-center mb-xl-8 mb-6">
                    <h2 class="mb-0 h1">카테고리별 상품</h2>
                    <p class="mb-0 text-body-secondary">다양한 카테고리의 상품을 확인해보세요</p>
                </div>
            </div>
        </div>
        <div class="row gy-4">
            @foreach($categories->take(8) as $category)
            <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                <div class="card border-0 shadow-sm h-100 category-card">
                    <div class="card-body text-center p-xl-5 p-4">
                        @if($category->image)
                            <img src="{{ $category->image }}" alt="{{ $category->title }}"
                                 class="mb-4 rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center"
                                 style="width: 80px; height: 80px;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted">
                                    <rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>
                                </svg>
                            </div>
                        @endif
                        <h5 class="card-title mb-3">{{ $category->title }}</h5>
                        @if($category->description)
                            <p class="card-text text-muted mb-4">{{ Str::limit($category->description, 60) }}</p>
                        @endif
                        <a href="{{ route('store.products.index', ['category' => $category->slug]) }}"
                           class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2">
                            <span>상품 보기</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Featured Products -->
@if($featuredProducts->count() > 0)
<section class="py-xl-8 py-6 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column gap-2 text-center mb-xl-8 mb-6">
                    <h2 class="mb-0 h1">추천 상품</h2>
                    <p class="mb-0 text-body-secondary">특별히 추천하는 상품들을 만나보세요</p>
                </div>
            </div>
        </div>
        <div class="row gy-4">
            @foreach($featuredProducts->take(8) as $product)
            <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                <div class="card border-0 shadow-sm h-100 product-card">
                    <div class="position-relative">
                        @if($product->image)
                            <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->title }}"
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-muted">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                </svg>
                            </div>
                        @endif
                        @if($product->sale_price && $product->sale_price < $product->price)
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-danger">세일</span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <h6 class="card-title mb-3">{{ Str::limit($product->title, 40) }}</h6>
                        @if($product->description)
                            <p class="card-text text-muted small mb-3">{{ Str::limit($product->description, 60) }}</p>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-column gap-1">
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="text-decoration-line-through text-muted small">₩{{ number_format($product->price) }}</span>
                                    <span class="fw-bold text-primary h6 mb-0">₩{{ number_format($product->sale_price) }}</span>
                                @else
                                    <span class="fw-bold h6 mb-0">₩{{ number_format($product->price) }}</span>
                                @endif
                            </div>
                            <a href="{{ route('store.products.show', $product->id) }}"
                               class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                                <span>보기</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-6">
            <a href="{{ route('store.products.index') }}" class="btn btn-outline-primary btn-lg">
                모든 상품 보기
            </a>
        </div>
    </div>
</section>
@endif

<!-- Popular & New Products -->
<section class="py-xl-8 py-6">
    <div class="container">
        <div class="row gy-xl-0 gy-6">
            <!-- Popular Products -->
            @if($popularProducts->count() > 0)
            <div class="col-lg-6">
                <div class="d-flex flex-column gap-2 text-center mb-6">
                    <h3 class="mb-0 h2">인기 상품</h3>
                    <p class="mb-0 text-body-secondary">많은 분들이 선택한 상품들</p>
                </div>
                <div class="d-flex flex-column gap-3">
                    @foreach($popularProducts->take(4) as $product)
                    <div class="card border-0 shadow-sm product-card-small">
                        <div class="row g-0">
                            <div class="col-4">
                                @if($product->image)
                                    <img src="{{ $product->image }}" class="img-fluid rounded-start h-100"
                                         alt="{{ $product->title }}" style="object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-start h-100 d-flex align-items-center justify-content-center">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-muted">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="col-8">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">{{ Str::limit($product->title, 25) }}</h6>
                                    <div class="mb-2">
                                        @if($product->sale_price && $product->sale_price < $product->price)
                                            <small class="text-decoration-line-through text-muted">₩{{ number_format($product->price) }}</small>
                                            <span class="fw-bold text-primary small ms-1">₩{{ number_format($product->sale_price) }}</span>
                                        @else
                                            <span class="fw-bold small">₩{{ number_format($product->price) }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('store.products.show', $product->id) }}"
                                       class="btn btn-outline-primary btn-sm">보기</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- New Arrivals -->
            @if($newArrivals->count() > 0)
            <div class="col-lg-6">
                <div class="d-flex flex-column gap-2 text-center mb-6">
                    <h3 class="mb-0 h2">신상품</h3>
                    <p class="mb-0 text-body-secondary">최근 등록된 상품들</p>
                </div>
                <div class="d-flex flex-column gap-3">
                    @foreach($newArrivals->take(4) as $product)
                    <div class="card border-0 shadow-sm product-card-small">
                        <div class="row g-0">
                            <div class="col-4">
                                @if($product->image)
                                    <img src="{{ $product->image }}" class="img-fluid rounded-start h-100"
                                         alt="{{ $product->title }}" style="object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-start h-100 d-flex align-items-center justify-content-center">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-muted">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="col-8">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">{{ Str::limit($product->title, 25) }}</h6>
                                    <div class="mb-2">
                                        @if($product->sale_price && $product->sale_price < $product->price)
                                            <small class="text-decoration-line-through text-muted">₩{{ number_format($product->price) }}</small>
                                            <span class="fw-bold text-primary small ms-1">₩{{ number_format($product->sale_price) }}</span>
                                        @else
                                            <span class="fw-bold small">₩{{ number_format($product->price) }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('store.products.show', $product->id) }}"
                                       class="btn btn-outline-primary btn-sm">보기</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Customer Testimonials -->
@if($testimonials->count() > 0)
<section class="py-xl-8 py-6 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column gap-2 text-center mb-xl-8 mb-6">
                    <h2 class="mb-0 h1">고객 후기</h2>
                    <p class="mb-0 text-body-secondary">고객들의 생생한 후기를 확인해보세요</p>
                </div>
            </div>
        </div>
        <div class="row gy-4">
            @foreach($testimonials as $testimonial)
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                                     style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($testimonial->customer_name ?: 'U', 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $testimonial->customer_name ?: '익명' }}</h6>
                                <div class="d-flex gap-1 text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $testimonial->rating)
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                                <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
                                            </svg>
                                        @else
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <h6 class="card-title">{{ $testimonial->title }}</h6>
                        <p class="card-text text-muted">{{ Str::limit($testimonial->content, 120) }}</p>
                        <small class="text-muted">{{ $testimonial->created_at->format('Y.m.d') }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Call to Action -->
<section class="py-xl-8 py-6 bg-primary">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-2">
                    <h3 class="mb-0 h2 text-white">지금 바로 쇼핑을 시작하세요!</h3>
                    <p class="mb-0 text-white">다양한 상품과 서비스가 여러분을 기다리고 있습니다.</p>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="{{ route('store.products.index') }}" class="btn btn-warning btn-lg d-flex align-items-center justify-content-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    <span>쇼핑 시작하기</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.category-card {
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.product-card {
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.product-card-small {
    transition: all 0.3s ease;
}

.product-card-small:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important;
}

.bottom-25 {
    bottom: 25% !important;
}

.ms-n8 {
    margin-left: -3rem !important;
}

.me-n7 {
    margin-right: -2.5rem !important;
}
</style>
@endpush