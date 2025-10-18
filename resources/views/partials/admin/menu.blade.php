{{-- 스토어 --}}
            <li class="nav-item">
                <div class="navbar-heading">스토어</div>
            </li>

            {{-- 이커머스 대시보드 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.ecommerce.dashboard') }}">
                    <i class="nav-icon fe fe-shopping-cart me-2"></i>
                    이커머스 대시보드
                </a>
            </li>

            {{-- 주문 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.ecommerce.orders.index') }}">
                    <i class="nav-icon fe fe-file-text me-2"></i>
                    주문 관리
                </a>
            </li>

            {{-- 상품 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navProducts" aria-expanded="false" aria-controls="navProducts">
                    <i class="nav-icon fe fe-package me-2"></i>
                    상품 관리
                </a>
                <div id="navProducts" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.products.index') }}">
                                <i class="bi bi-list me-2"></i>
                                상품 목록
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.products.categories.index') }}">
                                <i class="bi bi-tags me-2"></i>
                                상품 카테고리
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 서비스 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navServices" aria-expanded="false" aria-controls="navServices">
                    <i class="nav-icon fe fe-briefcase me-2"></i>
                    서비스 관리
                </a>
                <div id="navServices" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.services.index') }}">
                                <i class="bi bi-list me-2"></i>
                                서비스 목록
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.services.categories.index') }}">
                                <i class="bi bi-tags me-2"></i>
                                서비스 카테고리
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 장바구니 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.store.cart.index') }}">
                    <i class="nav-icon fe fe-shopping-cart me-2"></i>
                    장바구니 관리
                </a>
            </li>

            {{-- 배송 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navShipping" aria-expanded="false" aria-controls="navShipping">
                    <i class="nav-icon fe fe-truck me-2"></i>
                    배송 관리
                </a>
                <div id="navShipping" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.shipping.index') }}">
                                <i class="bi bi-speedometer2 me-2"></i>
                                배송 대시보드
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.shipping.zones.index') }}">
                                <i class="bi bi-globe2 me-2"></i>
                                배송 지역 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.shipping.methods.index') }}">
                                <i class="bi bi-truck me-2"></i>
                                배송 방식 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.shipping.rates.index') }}">
                                <i class="bi bi-currency-dollar me-2"></i>
                                배송 요금 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.shipping.calculator.index') }}">
                                <i class="bi bi-calculator me-2"></i>
                                배송비 계산기
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 결제 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navPayment" aria-expanded="false" aria-controls="navPayment">
                    <i class="nav-icon fe fe-credit-card me-2"></i>
                    결제 관리
                </a>
                <div id="navPayment" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('준비 중입니다.')">
                                <i class="bi bi-speedometer2 me-2"></i>
                                결제 대시보드
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('준비 중입니다.')">
                                <i class="bi bi-credit-card me-2"></i>
                                결제 수단 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('준비 중입니다.')">
                                <i class="bi bi-receipt me-2"></i>
                                결제 내역
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('준비 중입니다.')">
                                <i class="bi bi-arrow-return-left me-2"></i>
                                환불 관리
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 프로모션 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navPromotion" aria-expanded="false" aria-controls="navPromotion">
                    <i class="nav-icon fe fe-tag me-2"></i>
                    프로모션
                </a>
                <div id="navPromotion" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.coupons.index') }}">
                                <i class="bi bi-ticket-perforated me-2"></i>
                                쿠폰 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.promotions.index') }}">
                                <i class="bi bi-percent me-2"></i>
                                할인 정책
                            </a>
                        </li>
                        {{-- 이벤트 관리 - 라우트 미정의로 임시 비활성화 --}}
                        {{--
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.events.index') }}">
                                <i class="bi bi-gift me-2"></i>
                                이벤트 관리
                            </a>
                        </li>
                        --}}
                    </ul>
                </div>
            </li>

            {{-- 재고 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navInventory" aria-expanded="false" aria-controls="navInventory">
                    <i class="nav-icon fe fe-package me-2"></i>
                    재고 관리
                </a>
                <div id="navInventory" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.inventory.dashboard') }}">
                                <i class="bi bi-boxes me-2"></i>
                                재고 현황
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.inventory.stock-in') }}">
                                <i class="bi bi-arrow-down-square me-2"></i>
                                입고 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.inventory.stock-out') }}">
                                <i class="bi bi-arrow-up-square me-2"></i>
                                출고 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.store.ecommerce.inventory.alerts') }}">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                품절 알림
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
