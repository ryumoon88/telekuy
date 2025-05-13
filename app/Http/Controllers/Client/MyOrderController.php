<?php

namespace App\Http\Controllers\Client;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Events\Client\OrderUpdated;
use App\Events\Client\UserBalanceUpdated;
use App\Http\Controllers\Controller;
use App\Models\Shop\Order;
use App\Models\Shop\OrderProductItem;
use App\Models\Telegram\Account;
use App\Models\Telegram\Bot;
use App\Models\Telegram\BotOption;
use App\Models\Telegram\Referral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MyOrderController extends Controller
{
    public function index(Request $request){
        return Inertia::render('Orders/Index');
    }

    public function show(Order $order) {

        // dd($order->getAccounts()->isEmpty());
        // dd($order->licenses());
        return Inertia::render('Orders/Show', [
            'order' => Inertia::defer(fn() => $order->load([
                'orderProducts',
                'orderProducts.product',
                'orderProducts.orderProductItems.license' => function($query) use ($order) {
                    $query->when($order->orderProducts->isNotEmpty(), function($query) use ($order) {
                        $query->whereHas('orderable', function (Builder $morphTo) use ($order) {
                            $morphTo->where('orderable_type', BotOption::class);
                        });
                    });
                },
                'orderProducts.orderProductItems.orderable' => function(MorphTo $morphTo) use($order) {
                    $morphTo->constrain([
                        BotOption::class => function (Builder $query) use ($order) {
                            $query->with(['licenses' => function ($licenseQuery) use ($order) {
                                $licenseQuery->whereHas('orderProductItem', function ($itemQuery) use ($order) {
                                    $itemQuery->whereHas('orderProduct', function ($productQuery) use ($order) {
                                        $productQuery->where('order_id', $order->id);
                                    });
                                });
                            }]);
                        },
                    ]);
                },
            ])),
        ]);
    }

    public function latest(Request $request){
        $limit = $request->get('limit');
        $user = Auth::user();
        return $user->orders()->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    public function download(Order $order, Request $request) {
        $target = $request->get('target');
        $type = $request->get('type');

        if($order->status != OrderStatus::Completed){
            return redirect()->back();
        }

        if($target == 'account') {
            return Account::DownloadAccounts($order->id, $order->getAccounts());
        }
    }

    public function pay(Order $order){
        $order->pay();
        UserBalanceUpdated::dispatch();
        OrderUpdated::dispatch($order);
    }
}
