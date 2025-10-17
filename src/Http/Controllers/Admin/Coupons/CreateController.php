<?php

namespace Jiny\Shop\Http\Controllers\Ecommerce\Coupons;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jiny-shop::ecommerce.coupons.create');
    }
}