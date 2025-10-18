<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JinyShop - 온라인 스토어')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Feather Icons -->
    <link href="https://unpkg.com/feather-icons@4.29.0/dist/feather.css" rel="stylesheet">
    
    @stack('styles')
    
    <style>
        .store-header {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .store-footer {
            background: #2c3e50;
            color: #ecf0f1;
            margin-top: 80px;
        }
        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #dee2e6;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wishlist-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .breadcrumb-store {
            background: #f8f9fa;
            padding: 1rem 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="store-header sticky-top">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <a class="navbar-brand fw-bold" href="{{ route('store.index') }}">
                    <i data-feather="shopping-bag" class="me-2"></i>JinyShop
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('store.index') }}">홈</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('store.products.index') }}">상품</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('store.services.index') }}">서비스</a>
                        </li>
                    </ul>
                    
                    <!-- Search Form -->
                    <form class="d-flex me-3" action="{{ route('store.search') }}" method="GET">
                        <input class="form-control me-2" type="search" name="q" placeholder="상품 검색..." value="{{ request('q') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i data-feather="search"></i>
                        </button>
                    </form>
                    
                    <!-- User Actions -->
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('store.wishlist.index') }}">
                                <i data-feather="heart"></i>
                                <span class="cart-badge" id="wishlist-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('store.cart.index') }}">
                                <i data-feather="shopping-cart"></i>
                                <span class="cart-badge" id="cart-count">0</span>
                            </a>
                        </li>
                        @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i data-feather="user"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('store.orders.index') }}">주문내역</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout">로그아웃</a></li>
                            </ul>
                        </li>
                        @else
                        <li class="nav-item">
                            <a class="nav-link" href="/login">로그인</a>
                        </li>
                        @endauth
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Breadcrumb -->
    @hasSection('breadcrumb')
    <div class="breadcrumb-store">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('store.index') }}">홈</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="store-footer">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>JinyShop</h5>
                    <p class="text-muted">다양한 상품과 서비스를 만나보세요. 안전하고 편리한 온라인 쇼핑을 경험하세요.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i data-feather="facebook"></i></a>
                        <a href="#" class="text-light"><i data-feather="twitter"></i></a>
                        <a href="#" class="text-light"><i data-feather="instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 mb-4">
                    <h6>쇼핑</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('store.products.index') }}" class="text-muted">상품</a></li>
                        <li><a href="{{ route('store.services.index') }}" class="text-muted">서비스</a></li>
                        <li><a href="{{ route('store.cart.index') }}" class="text-muted">장바구니</a></li>
                        <li><a href="{{ route('store.wishlist.index') }}" class="text-muted">찜목록</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 mb-4">
                    <h6>고객지원</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted">고객센터</a></li>
                        <li><a href="#" class="text-muted">FAQ</a></li>
                        <li><a href="#" class="text-muted">배송정보</a></li>
                        <li><a href="#" class="text-muted">교환/반품</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 mb-4">
                    <h6>회사정보</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted">회사소개</a></li>
                        <li><a href="#" class="text-muted">이용약관</a></li>
                        <li><a href="#" class="text-muted">개인정보처리방침</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 mb-4">
                    <h6>연락처</h6>
                    <p class="text-muted small">
                        <i data-feather="phone" class="me-1"></i> 1588-0000<br>
                        <i data-feather="mail" class="me-1"></i> support@jinyshop.com<br>
                        <i data-feather="clock" class="me-1"></i> 09:00 - 18:00
                    </p>
                </div>
            </div>
            <hr class="mt-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted small mb-0">&copy; 2025 JinyShop. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <img src="/images/payment-methods.png" alt="Payment Methods" class="img-fluid" style="max-height: 30px;">
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    @stack('scripts')
    
    <script>
        // Initialize Feather Icons
        feather.replace();
        
        // Load cart and wishlist counts
        document.addEventListener('DOMContentLoaded', function() {
            loadCartCount();
            loadWishlistCount();
        });
        
        function loadCartCount() {
            fetch('{{ route("store.cart.count") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('cart-count').textContent = data.count || 0;
                })
                .catch(error => console.error('Cart count error:', error));
        }
        
        function loadWishlistCount() {
            fetch('{{ route("store.wishlist.count") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('wishlist-count').textContent = data.count || 0;
                })
                .catch(error => console.error('Wishlist count error:', error));
        }
        
        // Global functions for cart and wishlist
        function addToCart(type, itemId, quantity = 1, pricingId = null) {
            const formData = new FormData();
            formData.append('type', type);
            formData.append('item_id', itemId);
            formData.append('quantity', quantity);
            if (pricingId) formData.append('pricing_id', pricingId);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            return fetch('{{ route("store.cart.add") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartCount();
                    showNotification('success', data.message);
                } else {
                    showNotification('error', data.message);
                }
                return data;
            });
        }
        
        function addToWishlist(type, itemId) {
            const formData = new FormData();
            formData.append('type', type);
            formData.append('item_id', itemId);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            return fetch('{{ route("store.wishlist.add") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadWishlistCount();
                    showNotification('success', data.message);
                } else {
                    showNotification('error', data.message);
                }
                return data;
            });
        }
        
        function showNotification(type, message) {
            // Simple alert for now - can be replaced with toast notifications
            if (type === 'success') {
                alert('✅ ' + message);
            } else {
                alert('❌ ' + message);
            }
        }
    </script>
</body>
</html>
