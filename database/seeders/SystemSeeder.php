<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\AccountTransactionType;
use App\Enums\TransactionType;
use App\Models\Shop\Product;
use App\Models\Telegram\Account;
use App\Models\Telegram\AccountTransaction;
use App\Models\Telegram\Bundle;
use App\Models\Telegram\Referral;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Account::factory(100)
            ->has(AccountTransaction::factory(1)->state([
                'type' => AccountTransactionType::Purchase,
                'amount' => fake()->numberBetween(2500, 5000),
            ]), 'transactions')
            ->create();

        Account::all()->each(function($account) {
            foreach(Referral::inRandomOrder()->limit(rand(1, 4))->get() as $referral){
                $account->transactions()->create([
                    'type' => AccountTransactionType::Referral,
                    'referral_id' => $referral->id,
                    'causer_id' => User::whereHas('roles', fn($query) => $query->where('name', 'admin'))->inRandomOrder()->first()->id,
                ]);
            }
        });

        // Account::where('status', AccountStatus::Sold)->get()->each(function($account){
        //     $account->transactions()->create([
        //         'type' => TransactionType::AccountSelling,
        //         'amount' => $account->selling_price,
        //     ]);
        // });

        $available_accounts = Account::where('status', AccountStatus::Available)->count();
        $bundle_account_number = floor(($available_accounts/2) / 10);

        for ($k = 0 ; $k < $bundle_account_number; $k++) {
            $bundle = Bundle::create([
                'name' => 'Bundle #'.$k+1,
            ]);
            $accounts_to_bundle = Account::where('status', AccountStatus::Available)->limit(10)->get();
            $bundle->attachAccount($accounts_to_bundle);
        }

        $available_accounts_to_product = Account::where('status', AccountStatus::Available)->count();
        $divided_into_products = floor(($available_accounts_to_product/2)/10);
        for ($i=0;$i<$divided_into_products;$i++){
            $product = Product::create([
                'name' => 'Product #'.$i+1,
            ]);
            $accounts_to_product = Account::inRandomOrder()->where('status', AccountStatus::Available)->limit(rand(10, 15))->get();
            $product->attachAccount($accounts_to_product);
        }
    }
}
