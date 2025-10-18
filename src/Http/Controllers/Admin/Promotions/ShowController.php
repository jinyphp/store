<?php

namespace Jiny\Store\Http\Controllers\Admin\Promotions;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Store\Models\Promotion;

class ShowController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);

        return view('jiny-store::admin.promotions.show', compact('promotion'));
    }
}