@php
    use Cknow\Money\Money;
@endphp

<div class="flex flex-col text-sm">
    <span>Balance</span>
    <span class="text-gray-400">{{Money::IDR($balance, true)}}</span>
</div>
