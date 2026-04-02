<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FrontPageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\WechatPayController;

Route::get('/', [FrontPageController::class, 'home']);
Route::get('/packages', [FrontPageController::class, 'packages']);


Route::get('/packet/list', [HomeController::class, 'packetList']);
Route::post('/order/create', [OrderController::class, 'store']);
Route::get('/orders/{orderNo}/status', [OrderStatusController::class, 'show']);
Route::post('/wechat/pay/notify', [WechatPayController::class, 'notify']);
