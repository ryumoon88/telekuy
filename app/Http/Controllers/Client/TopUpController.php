<?php

namespace App\Http\Controllers\Client;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Transaction\PaymentMethod;
use App\Models\Transaction\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Nekoding\Tripay\Signature;
use Nekoding\Tripay\Tripay;
use Nekoding\Tripay\TripayFacade;

class TopUpController extends Controller
{
    public function index() {
        $topUpOptions = [
            10000,
            20000,
            50000,
            100000,
            150000,
            200000,
            500000,
            1000000
        ];
        return Inertia::render('TopUp',[
            'paymentMethods' => Inertia::defer(fn() => PaymentMethod::all()),
            'topUpOptions' => Inertia::defer(fn() => $topUpOptions),
            'canCustomAmount' => false,
        ]);
    }

    public function topUp(Request $request) {
        $method = $request->string('method')->toString();
        $amount = $request->integer('amount');


        $user = Auth::user();

        $transaction = Transaction::TopUp($user, $method, $amount);
        // dd($method, $amount, $transaction->reference);

        $tripay_data = [
            'method' => $method,
            'merchant_ref' => $transaction->reference,
            'amount' => $amount,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '0',
            'order_items' => [
                [
                    'sku' => 'TOPUP-'.$amount,
                    'name' => 'TOPUP '.$amount,
                    'price' => $amount,
                    'quantity' => 1,
                ]
            ],
            'return_url' => env('APP_URL'),
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature' => Signature::generate($transaction->reference.$amount)
        ];

        $res = TripayFacade::createTransaction($tripay_data);
        $response = $res->getResponse();
        if($response){
            $transaction->update([
                'tripay_payload' => json_encode($response['data'])
            ]);
            
            return response()->json([
                'checkout_url' => $response['data']['checkout_url'],
            ]);
        }
        return response()->json([
            'message' => 'Error occured, please contact administrator'
        ]);
    }
}
