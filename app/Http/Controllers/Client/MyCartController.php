<?php

namespace App\Http\Controllers\Client;

use App\Events\Client\MyCartUpdated;
use App\Events\Client\OrderUpdated;
use App\Http\Controllers\Controller;
use App\Models\Shop\CartProductItem;
use App\Models\Telegram\Account;
use App\Models\Telegram\BotOption;
use App\Models\Telegram\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MyCartController extends Controller
{
    public function index(){
        return Auth::user()->getCart()
            ->load([
                'cartProducts',
                'cartProducts.product',
                'cartProducts.cartProductItems',
                'cartProducts.cartProductItems.cartable'
            ]);
    }

    public function add($cartable_id, Request $request){
        $type = $request->get('type');
        $extra = [];

        if($type == 'referral') {
            $target = $request->post('target');
            $quantity = $request->post('quantity');
            $extra['target'] = $target;
            $extra['quantity'] = $quantity;
        }

        $cart = Auth::user()->getCart();
        $type = request()->get('type');
        $type = match($type){
            'account' => Account::class,
            'bot' => BotOption::class,
            'referral' => Referral::class,
        };
        $item = $type::find($cartable_id);
        $cart->add($item, $extra);

        MyCartUpdated::dispatch();
    }

    public function remove(CartProductItem $cartProductItem) {
        $cart = Auth::user()->cart;
        $cart->remove($cartProductItem);

        MyCartUpdated::dispatch();
    }

    public function checkout(){
        $cart = Auth::user()->getCart();
        $order = $cart->checkout();
        MyCartUpdated::dispatch();
        OrderUpdated::dispatch($order);
    }

    public function clear(){

    }
}
