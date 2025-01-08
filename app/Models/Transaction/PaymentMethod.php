<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Nekoding\Tripay\TripayFacade as Tripay;
use Sushi\Sushi;

class PaymentMethod extends Model
{
    use Sushi;

    public function getRows()
    {
        $response = Tripay::getChannelPembayaran()->jsonSerialize();
        if(!$response['success'])
            return [];
        $data = Arr::map($response['data'], fn($data) => Arr::only($data, [
            'group',
            'code',
            'name',
            'type',
            'icon_url',
            'active',
        ]));
        return $data;
    }
}
