<!DOCTYPE html>
<html>
<head>
    <title>장바구니</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1>장바구니</h1>

        @if($cartItems && $cartItems->count() > 0)
            <div class="alert alert-info">
                장바구니에 {{ $summary['item_count'] }}개의 상품이 있습니다.
            </div>
            <!-- Cart items would go here -->
        @else
            <div class="alert alert-warning">
                장바구니가 비어있습니다.
            </div>
        @endif

        <div class="card mt-4">
            <div class="card-body">
                <h5>주문 요약</h5>
                <p>총 상품: {{ $summary['item_count'] }}개</p>
                <p>총 금액: {{ $summary['total_formatted'] }}</p>
                <p>통화: {{ $currency['currency_symbol'] }} {{ $currency['user_currency'] }}</p>
            </div>
        </div>
    </div>
</body>
</html>