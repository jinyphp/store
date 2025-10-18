<?php

namespace Jiny\Store\Http\Controllers\Admin\Coupons;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Store\Models\Coupon;

class ShowController extends Controller
{
    public function __invoke(Request $request, Coupon $coupon)
    {
        $coupon->load(['usages.user', 'usages.order']);

        return view('jiny-store::admin.coupons.show', compact('coupon'));
    }
}