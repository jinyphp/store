<?php

namespace Jiny\Store\Http\Controllers\Admin\Promotions;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('jiny-store::admin.promotions.create');
    }
}