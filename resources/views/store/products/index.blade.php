@extends('jiny-store::layouts.store')

@section('title', $config['title'])

@section('breadcrumb')
<li class="breadcrumb-item active">상품</li>
@endsection

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">필터</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('store.products.index') }}" id="filterForm">
                        <!-- Search -->
                        <div class="mb-4">
                            <label class="form-label">검색</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="상품명 검색...">
                        </div>

                        <!-- Categories -->
                        @if($categories && $categories->count() > 0)
                        <div class="mb-4">
                            <label class="form-label">카테고리</label>
                            <select class="form-select" name="category">
                                <option value="">전체 카테고리</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>
                                    {{ $category->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label">가격 범위</label>
                            @foreach($filters['price_ranges'] as $range)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="price_range" 
                                       value="{{ $range['min'] }}-{{ $range['max'] ?: 'max' }}"
                                       id="price_{{ $loop->index }}"
                                       {{ (request('price_min') == $range['min'] && request('price_max') == $range['max']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="price_{{ $loop->index }}">
                                    {{ $range['label'] }}
                                </label>
                            </div>
                            @endforeach
                        </div>

                        <!-- Custom Price Range -->
                        <div class="mb-4">
                            <label class="form-label">직접 입력</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="number" class="form-control" name="price_min" 
                                           value="{{ request('price_min') }}" placeholder="최소 가격">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="price_max" 
                                           value="{{ request('price_max') }}" placeholder="최대 가격">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">필터 적용</button>
                        <a href="{{ route('store.products.index') }}" class="btn btn-outline-secondary w-100 mt-2">초기화</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>상품 목록</h2>
                    <p class="text-muted mb-0">총 {{ $products->total() }}개의 상품</p>
                </div>
                <div class="d-flex gap-2">
                    <!-- Sort -->
                    <select class="form-select" name="sort" onchange="this.form.submit()" form="filterForm">
                        @foreach($filters['sort_options'] as $value => $label)
                        <option value="{{ $value }}" {{ request('sort') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    
                    <!-- View Toggle -->
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary active" data-view="grid">
                            <i data-feather="grid"></i>
                        </button>
                        <button class="btn btn-outline-secondary" data-view="list">
                            <i data-feather="list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
            <div class="row g-4" id="products-grid">
                @foreach($products as $product)
                <div class="col-lg-4 col-md-6 product-item">
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
                            
                            <!-- Wishlist Button -->
                            <button class="wishlist-btn" onclick="addToWishlist('product', {{ $product->id }})">
                                <i data-feather="heart"></i>
                            </button>
                            
                            <!-- Sale Badge -->
                            @if($product->sale_price && $product->sale_price < $product->price)
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                {{ round((($product->price - $product->sale_price) / $product->price) * 100) }}% OFF
                            </span>
                            @endif
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">
                                <a href="{{ route('store.products.show', $product->id) }}" class="text-decoration-none text-dark">
                                    {{ $product->title }}
                                </a>
                            </h6>
                            
                            @if($product->category_name)
                            <small class="text-muted">{{ $product->category_name }}</small>
                            @endif
                            
                            <p class="card-text flex-grow-1">{{ Str::limit($product->description, 100) }}</p>
                            
                            <!-- Price -->
                            <div class="mt-auto">
                                @if($product->sale_price && $product->sale_price < $product->price)
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="fw-bold text-danger">{{ number_format($product->sale_price) }}원</span>
                                    <small class="text-muted text-decoration-line-through">{{ number_format($product->price) }}원</small>
                                </div>
                                @else
                                <div class="fw-bold mb-2">{{ number_format($product->price) }}원</div>
                                @endif
                                
                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-sm" onclick="addToCart('product', {{ $product->id }})">
                                        <i data-feather="shopping-cart" class="me-1" style="width: 16px; height: 16px;"></i>
                                        장바구니 담기
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

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
            @else
            <!-- No Products -->
            <div class="text-center py-5">
                <i data-feather="search" style="width: 64px; height: 64px;" class="text-muted mb-3"></i>
                <h5 class="text-muted">검색 결과가 없습니다</h5>
                <p class="text-muted">다른 검색어나 필터를 시도해보세요.</p>
                <a href="{{ route('store.products.index') }}" class="btn btn-primary">전체 상품 보기</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const gridBtn = document.querySelector('[data-view="grid"]');
    const listBtn = document.querySelector('[data-view="list"]');
    const productsGrid = document.getElementById('products-grid');

    gridBtn.addEventListener('click', function() {
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
        productsGrid.className = 'row g-4';
        document.querySelectorAll('.product-item').forEach(item => {
            item.className = 'col-lg-4 col-md-6 product-item';
        });
    });

    listBtn.addEventListener('click', function() {
        listBtn.classList.add('active');
        gridBtn.classList.remove('active');
        productsGrid.className = 'd-flex flex-column gap-3';
        document.querySelectorAll('.product-item').forEach(item => {
            item.className = 'product-item';
        });
    });

    // Price range radio button handling
    document.querySelectorAll('input[name="price_range"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const [min, max] = this.value.split('-');
            document.querySelector('input[name="price_min"]').value = min;
            document.querySelector('input[name="price_max"]').value = max === 'max' ? '' : max;
        });
    });

    // Auto-submit form on change
    document.querySelectorAll('#filterForm select, #filterForm input[type="radio"]').forEach(element => {
        element.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});
</script>
@endpush
