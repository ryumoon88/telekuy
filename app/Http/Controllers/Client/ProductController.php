<?php

namespace App\Http\Controllers\Client;

use App\Enums\AccountStatus;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Shop\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $type = request('type');
        $format = request('format');

        $products = Product::where(function ($query) use ($type) {
            if ($type) {
                $query->where('type', $type);
            }
        })->get()->groupBy('type');

        if ($format == 'json') {
            $data = collect([
                'account' => $products['account'] ?? [],
                'bot' => $products['bot'] ?? [],
                'referral' => $products['referral'] ?? [],
            ]);

            return $type ? [$type => $data->first(fn ($item, $key) => $key == $type)] : $data;
        }

        return Inertia::render('Products/Index', [
            'type' => $type,
        ]);
    }

    public function show(Product $product)
    {
        if ($product->type == ProductType::Account) {
            $product->load(['accounts' => function ($query) {
                $query->where('status', AccountStatus::Available);
            }]);
        } elseif ($product->type == ProductType::Referral) {
            $product->load('referral');
        } elseif ($product->type == ProductType::Bot) {
            $product->load(['bot', 'bot.options']);
        }

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }
}
