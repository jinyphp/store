<?php

namespace Jiny\Shop\Http\Controllers\Ecommerce\Promotions;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Promotion;

class EditController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);

        return view('jiny-shop::ecommerce.promotions.edit', compact('promotion'));
    }
}