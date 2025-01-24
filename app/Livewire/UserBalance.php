<?php

namespace App\Livewire;

use Cknow\Money\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class UserBalance extends Component
{
    public $balance = 0;

    public function __construct()
    {
        $this->balance = Auth::user()->balance;
    }

    #[On('userBalanceUpdated')]
    public function userBalanceUpdated(){
        $this->balance = Auth::user()->balance;
    }


    public function render()
    {
        return view('livewire.user-balance', ['balance' => Money::IDR($this->balance, true)]);
    }
}
