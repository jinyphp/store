<?php

namespace Jiny\Store\Http\Controllers\Admin\Promotions;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\Promotion;

class EditController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);

        return view('jiny-store::admin.promotions.edit', compact('promotion'));
    }
}