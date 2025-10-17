# Jiny Shop Package

Laravel 패키지로 완전한 이커머스 솔루션을 제공합니다. 상품 관리, 장바구니, 주문 처리, 재고 관리, 결제 처리 등의 기능을 포함합니다.

## 설치

```bash
composer require jiny/shop
```

## 설정

### 서비스 프로바이더 등록 (Laravel 11+ Auto Discovery 지원)

Laravel 11 이상에서는 자동으로 서비스 프로바이더가 등록됩니다.

### 설정 파일 발행

```bash
php artisan vendor:publish --tag=jiny-shop-config
```

### 마이그레이션 실행

```bash
php artisan migrate
```

### 뷰 파일 발행 (선택사항)

```bash
php artisan vendor:publish --tag=jiny-shop-views
```

## 주요 기능

### 상품 관리 (Products)
- 상품 CRUD 작업
- 상품 카테고리 관리
- 상품 이미지 관리
- 상품 가격 관리
- 상품 재고 추적
- 상품 변형 지원
- 상품 리뷰 및 평점

### 장바구니 (Cart)
- 세션 기반 장바구니
- 영구 장바구니 (회원용)
- 게스트 장바구니 지원
- 장바구니 저장 기능
- 자동 정리 기능

### 주문 관리 (Orders)
- 주문 생성 및 처리
- 다단계 체크아웃 프로세스
- 주문 추적 시스템
- 주문 취소 및 반품
- 게스트 주문 지원
- 자동 주문 완료

### 재고 관리 (Inventory)
- 실시간 재고 추적
- 재고 부족 알림
- 재고 입고/출고 관리
- 백오더 관리
- 자동 재고 차감

### 결제 처리 (Payment)
- 다중 결제 수단 지원
- 다중 통화 지원
- 결제 계획 지원
- 결제 실패 처리

### 배송 관리 (Shipping)
- 다양한 배송 방법
- 실시간 배송비 계산
- 무료 배송 임계값
- 배송 추적 시스템
- 픽업 서비스 지원

### 프로모션 (Promotions)
- 쿠폰 시스템
- 할인 관리
- 대량 구매 할인
- Buy X Get Y 프로모션
- 쿠폰 스택 지원

### 세금 관리 (Tax)
- 국가별 세율 관리
- 가격 내 세금 포함/제외
- 배송비 세금 계산
- 디지털 상품 세율

## 설정 옵션

`config/shop.php` 파일에서 다양한 옵션을 설정할 수 있습니다:

```php
'products' => [
    'enable' => true,
    'pagination' => 20,
    'enable_reviews' => true,
    'enable_ratings' => true,
    'enable_wishlist' => true,
],

'cart' => [
    'enable' => true,
    'session_key' => 'jiny_cart',
    'persistent' => true,
    'expire_days' => 30,
],

'orders' => [
    'enable' => true,
    'order_number_prefix' => 'ORD',
    'enable_guest_orders' => true,
    'auto_complete_days' => 7,
],
```

## 라우트

### 관리자 라우트
- `/admin/cms/ecommerce` - 이커머스 대시보드
- `/admin/cms/cart` - 장바구니 관리
- `/admin/site/products` - 상품 관리
- `/admin/cms/ecommerce/orders` - 주문 관리
- `/admin/cms/ecommerce/inventory` - 재고 관리

### 사용자 라우트
- `/shop` - 쇼핑몰 메인
- `/products` - 상품 목록
- `/cart` - 장바구니
- `/checkout` - 주문결제
- `/orders` - 주문내역

## 뷰 커스터마이징

뷰 파일을 커스터마이징하려면:

```bash
php artisan vendor:publish --tag=jiny-shop-views
```

발행된 뷰 파일은 `resources/views/vendor/jiny-shop/` 디렉토리에 위치합니다.

## 보안 기능

- CSRF 보호
- Rate Limiting
- 사기 탐지
- 주문 검증
- 최대 주문 금액 제한

## 캐시 지원

- 상품 캐시
- 가격 캐시
- 설정 가능한 캐시 TTL
- 캐시 태그 지원

## 분석 및 추적

- 페이지 뷰 추적
- 장바구니 추가 추적
- 구매 추적
- 검색 추적
- Google Analytics 연동

## 알림

- 주문 완료 알림
- 주문 배송 알림
- 재고 부족 알림
- 결제 실패 알림

## 권한

관리자 기능은 `admin` 미들웨어를 사용합니다. `jiny/admin` 패키지가 필요합니다.

## 요구사항

- PHP 8.1 이상
- Laravel 10.0 이상
- MySQL 또는 PostgreSQL

## 라이선스

MIT License

## 지원

문의사항이나 버그 리포트는 GitHub Issues를 이용해주세요.