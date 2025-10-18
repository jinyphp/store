@extends('www_layout')

@section('title', $config['title'])

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">{{ $config['title'] }}</h1>
                <p class="lead mb-4">{{ $config['subtitle'] }}</p>
                <div class="d-flex gap-3">
                    <a href="{{ route('store.products.index') }}" class="btn btn-light btn-lg">
                        <i class="fe fe-shopping-bag me-2"></i>상품 보기
                    </a>
                    <a href="{{ route('store.services.index') }}" class="btn btn-outline-light btn-lg">
                        <i class="fe fe-briefcase me-2"></i>서비스 보기
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <img src="{{ asset('assets/images/store/hero-image.png') }}"
                         alt="Store Hero"
                         class="img-fluid rounded-3 shadow"
                         style="max-height: 400px; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
@if($categories->count() > 0)
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h2 class="h3 fw-bold">카테고리별 상품</h2>
                    <p class="text-muted">다양한 카테고리의 상품을 확인해보세요</p>
                </div>
            </div>
        </div>
        <div class="row g-4">
            @foreach($categories as $category)
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 category-card">
                    <div class="card-body text-center p-4">
                        @if($category->image)
                            <img src="{{ $category->image }}"
                                 alt="{{ $category->title }}"
                                 class="mb-3 rounded-circle"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                 style="width: 80px; height: 80px;">
                                <i class="fe fe-grid text-muted fs-3"></i>
                            </div>
                        @endif
                        <h5 class="card-title">{{ $category->title }}</h5>
                        @if($category->description)
                            <p class="card-text text-muted small">{{ Str::limit($category->description, 60) }}</p>
                        @endif
                        <a href="{{ route('store.products.category', $category->slug) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="fe fe-arrow-right me-1"></i>보기
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
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h2 class="h3 fw-bold">추천 상품</h2>
                    <p class="text-muted">특별히 추천하는 상품들을 만나보세요</p>
                </div>
            </div>
        </div>
        <div class="row g-4">
            @foreach($featuredProducts as $product)
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 product-card">
                    @if($product->image)
                        <img src="{{ $product->image }}"
                             class="card-img-top"
                             alt="{{ $product->title }}"
                             style="height: 200px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                             style="height: 200px;">
                            <i class="fe fe-image text-muted fs-1"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <h6 class="card-title">{{ Str::limit($product->title, 40) }}</h6>
                        @if($product->description)
                            <p class="card-text text-muted small">{{ Str::limit($product->description, 60) }}</p>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="text-decoration-line-through text-muted small">₩{{ number_format($product->price) }}</span>
                                    <span class="fw-bold text-primary">₩{{ number_format($product->sale_price) }}</span>
                                @else
                                    <span class="fw-bold">₩{{ number_format($product->price) }}</span>
                                @endif
                            </div>
                            <a href="{{ route('store.products.show', $product->id) }}"
                               class="btn btn-primary btn-sm">
                                <i class="fe fe-eye me-1"></i>보기
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('store.products.index') }}" class="btn btn-outline-primary">
                <i class="fe fe-grid me-2"></i>모든 상품 보기
            </a>
        </div>
    </div>
</section>
@endif

<!-- New Arrivals & Best Sellers -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- New Arrivals -->
            @if($newArrivals->count() > 0)
            <div class="col-lg-6 mb-5">
                <div class="text-center mb-4">
                    <h3 class="h4 fw-bold">신상품</h3>
                    <p class="text-muted">최근 등록된 상품들</p>
                </div>
                <div class="row g-3">
                    @foreach($newArrivals->take(6) as $product)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm product-card-small">
                            <div class="row g-0">
                                <div class="col-4">
                                    @if($product->image)
                                        <img src="{{ $product->image }}"
                                             class="img-fluid rounded-start h-100"
                                             alt="{{ $product->title }}"
                                             style="object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded-start h-100 d-flex align-items-center justify-content-center">
                                            <i class="fe fe-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-8">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-1">{{ Str::limit($product->title, 25) }}</h6>
                                        <div class="mb-2">
                                            @if($product->sale_price && $product->sale_price < $product->price)
                                                <small class="text-decoration-line-through text-muted">₩{{ number_format($product->price) }}</small>
                                                <span class="fw-bold text-primary small">₩{{ number_format($product->sale_price) }}</span>
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
                    </div>
                    @endforeach
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('store.specials.new-arrivals') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fe fe-plus me-1"></i>더 보기
                    </a>
                </div>
            </div>
            @endif

            <!-- Best Sellers -->
            @if($bestSellers->count() > 0)
            <div class="col-lg-6 mb-5">
                <div class="text-center mb-4">
                    <h3 class="h4 fw-bold">베스트셀러</h3>
                    <p class="text-muted">인기 있는 상품들</p>
                </div>
                <div class="row g-3">
                    @foreach($bestSellers->take(6) as $product)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm product-card-small">
                            <div class="row g-0">
                                <div class="col-4">
                                    @if($product->image)
                                        <img src="{{ $product->image }}"
                                             class="img-fluid rounded-start h-100"
                                             alt="{{ $product->title }}"
                                             style="object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded-start h-100 d-flex align-items-center justify-content-center">
                                            <i class="fe fe-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-8">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-1">{{ Str::limit($product->title, 25) }}</h6>
                                        <div class="mb-2">
                                            @if($product->sale_price && $product->sale_price < $product->price)
                                                <small class="text-decoration-line-through text-muted">₩{{ number_format($product->price) }}</small>
                                                <span class="fw-bold text-primary small">₩{{ number_format($product->sale_price) }}</span>
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
                    </div>
                    @endforeach
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('store.specials.bestsellers') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fe fe-star me-1"></i>더 보기
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Customer Testimonials -->
@if($testimonials->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-5">
                    <h2 class="h3 fw-bold">고객 후기</h2>
                    <p class="text-muted">고객들의 생생한 후기를 확인해보세요</p>
                </div>
            </div>
        </div>
        <div class="row g-4">
            @foreach($testimonials as $testimonial)
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                                     style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($testimonial->customer_name ?: 'U', 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">{{ $testimonial->customer_name ?: 'Anonymous' }}</h6>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $testimonial->rating)
                                            <i class="fe fe-star" style="fill: currentColor;"></i>
                                        @else
                                            <i class="fe fe-star"></i>
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
        <div class="text-center mt-4">
            <a href="{{ route('store.testimonials.index') }}" class="btn btn-outline-primary">
                <i class="fe fe-message-circle me-2"></i>모든 후기 보기
            </a>
        </div>
    </div>
</section>
@endif

<!-- Call to Action -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="h4 fw-bold mb-2">지금 바로 쇼핑을 시작하세요!</h3>
                <p class="mb-0">다양한 상품과 서비스가 여러분을 기다리고 있습니다.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('store.products.index') }}" class="btn btn-light btn-lg">
                    <i class="fe fe-shopping-cart me-2"></i>쇼핑 시작하기
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 60vh;
}

.category-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
}

.product-card:hover {
    transform: translateY(-3px);
    transition: transform 0.3s ease;
}

.product-card-small:hover {
    transform: translateY(-2px);
    transition: transform 0.3s ease;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.min-vh-50 {
    min-height: 50vh;
}
</style>
@endpush