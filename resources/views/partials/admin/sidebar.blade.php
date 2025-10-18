<!-- Sidebar -->
<nav class="navbar-vertical navbar">
    <div class="vh-100" data-simplebar>
        <!-- Brand logo -->
        <a class="navbar-brand" href="/">
            <img src="{{ asset('assets/images/brand/logo/logo-inverse.svg') }}" alt="Jiny Store" />
        </a>

        <!-- Navbar nav -->
        <ul class="navbar-nav flex-column" id="sideNavbar">
            {{-- Store Dashboard --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.dashboard') }}">
                    <i class="nav-icon fe fe-shopping-cart me-2"></i>
                    스토어 대시보드
                </a>
            </li>

            <li class="nav-item">
                <div class="nav-divider"></div>
            </li>

            {{-- 상품 관리 --}}
            <li class="nav-item">
                <div class="navbar-heading">상품 관리</div>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navProducts"
                    aria-expanded="false" aria-controls="navProducts">
                    <i class="nav-icon fe fe-package me-2"></i>
                    상품
                </a>
                <div id="navProducts" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.products.index') }}">
                                <i class="fe fe-list me-2"></i>상품 목록
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.products.create') }}">
                                <i class="fe fe-plus me-2"></i>상품 추가
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.products.categories.index') }}">
                                <i class="fe fe-grid me-2"></i>카테고리 관리
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navServices"
                    aria-expanded="false" aria-controls="navServices">
                    <i class="nav-icon fe fe-briefcase me-2"></i>
                    서비스
                </a>
                <div id="navServices" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.services.index') }}">
                                <i class="fe fe-list me-2"></i>서비스 목록
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.services.create') }}">
                                <i class="fe fe-plus me-2"></i>서비스 추가
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.services.categories.index') }}">
                                <i class="fe fe-grid me-2"></i>서비스 카테고리
                            </a>
                        </li>
                    </ul>
                </div>
            </li> --}}

            {{-- 주문 관리 --}}
            <li class="nav-item">
                <div class="navbar-heading">주문 관리</div>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navOrders"
                    aria-expanded="false" aria-controls="navOrders">
                    <i class="nav-icon fe fe-file-text me-2"></i>
                    주문
                </a>
                <div id="navOrders" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.orders.index') }}">
                                <i class="fe fe-list me-2"></i>주문 목록
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.orders.create') }}">
                                <i class="fe fe-plus me-2"></i>주문 생성
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.orders.step') }}">
                                <i class="fe fe-arrow-right me-2"></i>단계별 주문
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.cart.index') }}">
                    <i class="nav-icon fe fe-shopping-bag me-2"></i>
                    장바구니 관리
                </a>
            </li>

            {{-- 재고 관리 --}}
            <li class="nav-item">
                <div class="navbar-heading">재고 관리</div>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navInventory"
                    aria-expanded="false" aria-controls="navInventory">
                    <i class="nav-icon fe fe-layers me-2"></i>
                    재고
                </a>
                <div id="navInventory" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.inventory.index') }}">
                                <i class="fe fe-archive me-2"></i>재고 현황
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.inventory.stock-in') }}">
                                <i class="fe fe-plus-circle me-2"></i>입고 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.inventory.stock-out') }}">
                                <i class="fe fe-minus-circle me-2"></i>출고 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.inventory.alerts') }}">
                                <i class="fe fe-bell me-2"></i>재고 알림
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 배송 관리 --}}
            <li class="nav-item">
                <div class="navbar-heading">배송 관리</div>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navShipping"
                    aria-expanded="false" aria-controls="navShipping">
                    <i class="nav-icon fe fe-truck me-2"></i>
                    배송
                </a>
                <div id="navShipping" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.shipping.index') }}">
                                <i class="fe fe-truck me-2"></i>배송 대시보드
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.shipping.zones.index') }}">
                                <i class="fe fe-map me-2"></i>배송 지역
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.shipping.methods.index') }}">
                                <i class="fe fe-settings me-2"></i>배송 방식
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.shipping.rates.index') }}">
                                <i class="fe fe-dollar-sign me-2"></i>배송 요금
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.shipping.calculator.index') }}">
                                <i class="fe fe-calculator me-2"></i>배송비 계산기
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 마케팅 --}}
            <li class="nav-item">
                <div class="navbar-heading">마케팅</div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.promotions.index') }}">
                    <i class="nav-icon fe fe-gift me-2"></i>
                    프로모션
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.coupons.index') }}">
                    <i class="nav-icon fe fe-tag me-2"></i>
                    쿠폰
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.testimonials.index') }}">
                    <i class="nav-icon fe fe-star me-2"></i>
                    고객 후기
                </a>
            </li>

            {{-- 시스템 --}}
            <li class="nav-item">
                <div class="navbar-heading">시스템</div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.tax.index') }}">
                    <i class="nav-icon fe fe-percent me-2"></i>
                    세금 관리
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.settings.index') }}">
                    <i class="nav-icon fe fe-settings me-2"></i>
                    스토어 설정
                </a>
            </li>

            {{-- Auth Dashboard (Admin 시스템 이동) --}}
            <li class="nav-item">
                <a class="nav-link" href="/admin/auth">
                    <i class="nav-icon fe fe-shield me-2"></i>
                    Auth 관리자
                </a>
            </li>

            {{-- 기타 패키지 메뉴 포함 --}}
            @includeIf("jiny-mail::partials.admin.menu")
            @includeIf("jiny-emoney::partials.admin.menu")



        </ul>

        <!-- Help Card -->
        <div class="card bg-dark-primary shadow-none text-center mx-4 mt-5">
            <div class="card-body py-4">
                <h5 class="text-white-50">도움이 필요하신가요?</h5>
                <p class="text-white-50 fs-6 mb-3">문서를 확인하세요</p>
                <a href="#" class="btn btn-white btn-sm">문서 보기</a>
            </div>
        </div>
    </div>
</nav>
