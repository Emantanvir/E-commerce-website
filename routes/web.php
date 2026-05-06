<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthManager;
use App\Http\Controllers\ProductsManager;
use App\Http\Controllers\OrderManager;

Route::get('/', [ProductsManager::class,"index"])
->name('home');

// Login
Route::get('login', [AuthManager::class, 'login'])->name('login');
Route::get('logout', [AuthManager::class, 'logout'])->name('logout');
Route::post('login', [AuthManager::class, 'loginPost'])->name('login.post');

// Register
Route::get('register', [AuthManager::class, 'register'])->name('register');
Route::post('register', [AuthManager::class, 'registerPost'])->name('register.post');

Route::get("/products/{slug}", [ProductsManager::class, 'details'])->name("products.details");

Route::middleware("auth")->group(function(){
   
Route::get("/cart/{id}", [ProductsManager::class, 'addToCart'])->name("cart.add");

Route::get("/cart", [ProductsManager::class, 'showCart'])->name("cart.show");
Route::get("/checkout", [OrderManager::class, 'showCheckout'])->name("checkout.show");

Route::post("/checkout", [OrderManager::class, 'CheckoutPost'])->name("checkout.post");

});


