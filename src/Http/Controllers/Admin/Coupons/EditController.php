<?php

namespace Jiny\Store\Http\Controllers\Admin\Coupons;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Store\Models\Coupon;

class EditController extends Controller
{
    public function __invoke(Request $request, Coupon $coupon)
    {
        return view('jiny-store::admin.coupons.edit', compact('coupon'));
    }
}