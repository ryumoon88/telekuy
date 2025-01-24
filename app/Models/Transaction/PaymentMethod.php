<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Nekoding\Tripay\TripayFacade as Tripay;
use Sushi\Sushi;

class PaymentMethod extends Model
{
    use Sushi;

    protected $casts = [
        'total_fee' => 'json',
        'fee_customer' => 'json',
        'fee_merchant' => 'json',
    ];

    public function getRows()
    {
        $response = Tripay::getChannelPembayaran()->jsonSerialize();
        if(!$response['success'])
            return [];
        $data = Arr::map($response['data'], function($data){
            $data['fee_merchant'] = json_encode($data['fee_merchant']);
            $data['fee_customer'] = json_encode($data['fee_customer']);
            $data['total_fee'] = json_encode($data['total_fee']);
            // $data = Arr::except($data, ['fee_merchant', 'fee_customer']);
            return $data;
        });
        return $data;
    }
}
