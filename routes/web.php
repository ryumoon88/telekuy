<?php

use App\Enums\AccountStatus;
use App\Enums\ProductType;
use App\Http\Controllers\Callback\TripayCallbackController;
use App\Http\Controllers\Client\LicenseController;
use App\Http\Controllers\Client\MyCartController;
use App\Http\Controllers\Client\MyOrderController;
use App\Http\Controllers\Client\ProductController;
use App\Http\Controllers\Client\TopUpController;
use App\Http\Controllers\ProfileController;
use App\Models\Shop\Product;
use App\Models\Telegram\Account;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::middleware('auth')->group(function(){
    // Cart
    Route::group(['controller' => MyCartController::class,'prefix' => 'my-cart','as' => 'my-cart.'],function(){
        Route::get('', 'index')->name('index'); // JSON
        Route::post('/{cartable_id}/add', 'add')->name('add');
        Route::delete('/{cartProductItem}/remove', 'remove')->name('remove');
        Route::post('/checkout', 'checkout')->name('checkout');
        Route::put('/clear', 'clear')->name('clear');
    });

    // Order
    Route::group(['controller' => MyOrderController::class,'prefix' => 'my-order','as' => 'my-order.'], function() {
        Route::get('', 'index')->name('index');
        Route::get('/latest', 'latest')->name('latest');
        Route::post('{order}/pay', 'pay')->name('pay');
        Route::get('/{order}', 'show')->name('show');
        Route::get('/{order}/attachment/download', 'download')->name('attachment.download');

    });

    Route::group(['controller' => ProfileController::class, 'prefix' => 'profile', 'as' => 'profile.'], function(){
        Route::get('', 'edit')->name('edit');
        Route::patch('', 'update')->name('update');
        // Route::delete('', 'destroy')->name('destroy');
    });

    Route::group(['controller' => TopUpController::class, 'prefix' => 'top-up', 'as' => 'top-up.'], function(){
        Route::get('', 'index')->name('index');
        Route::post('', 'topUp')->name('topup');
    });
    // Route::group(['controller' => Ord])
});

Route::group([], function(){
    // Product
    Route::group(['controller' => ProductController::class,'prefix' => 'products','as' => 'products.'],function(){
        Route::get('', 'index')->name('index');
        Route::get('{product}', 'show')->name('show'); // Inertia
    });

    Route::group(['controller' => LicenseController::class, 'prefix' => 'licenses', 'as' => 'licenses.'], function() {
        Route::get('validate', 'validate')->name('check');
    });

    Route::group(['controller' => TripayCallbackController::class, 'prefix' => 'callback', 'as' => 'callback.'], function() {
        Route::post('', 'handle')->name('tripay');
    });
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

});

require __DIR__.'/auth.php';
require __DIR__.'/api.php';
