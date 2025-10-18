<?php

namespace Jiny\Store\Http\Controllers\Admin\Orders;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Store\Models\Order;
use App\Models\User;

class CreateController extends Controller
{
    public function __invoke(Request $request)
    {
        // Redirect to step-by-step order creation process
        return redirect()->route('admin.store.orders.step', 1);
    }
}