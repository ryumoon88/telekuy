<?php

namespace Database\Factories\Telegram;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tapp\FilamentCountryCodeField\Concerns\HasCountryCodeData;
use Tapp\FilamentCountryCodeField\Tables\Columns\CountryCodeColumn;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AccountFactory extends Factory
{
    use HasCountryCodeData;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $country_data = collect($this->getCountriesData())->random(1)->first();
        $locale = Str::lower($country_data['iso_code']).'_'.$country_data['iso_code'];
        return [
            'country_code' => $country_data['country_code'],
            'phone_number' => fake($locale)->e164PhoneNumber(),
            'path' => '',
            'status' => AccountStatus::Available,
            // 'status' => fake()->randomElement([AccountStatus::Available, AccountStatus::Sold]),
            'selling_price' => fake()->numberBetween(5000, 10000),
        ];
    }
}
