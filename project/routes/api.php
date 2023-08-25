<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('appVersion', [ApiController::class, 'appVersion']);
Route::get('splashScreen', [ApiController::class, 'splashScreen']);
Route::get('introScreen', [ApiController::class, 'introScreen']);
Route::get('termsOfUse', [ApiController::class, 'termsOfUse']);
Route::get('privacyPolicy', [ApiController::class, 'privacyPolicy']);
Route::get('contactUs', [ApiController::class, 'contactUs']);
Route::post('forgotPassword', [ApiController::class, 'forgotPassword']);
Route::post('categories', [ApiController::class, 'categories']);
Route::post('subCategories', [ApiController::class, 'subCategories']);
Route::post('childCategories', [ApiController::class, 'childCategories']);
Route::post('slider', [ApiController::class, 'slider']);
Route::post('productList', [ApiController::class, 'productList']);
Route::post('productDetails', [ApiController::class, 'productDetails']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [ApiController::class, 'createUser']);
Route::post('/auth/login', [ApiController::class, 'loginUser']);

Route::middleware('auth:sanctum')->group( function () {
    Route::get('logout','Api\ApiController@logout');
    Route::post('/getProfile', 'Api\ApiController@getProfile');
    Route::post('/updateProfile', 'Api\ApiController@updateProfile');
    Route::post('/removeAccount', 'Api\ApiController@removeAccount');
});

Route::get('unauthorized', function () {
    return response()->json(['status' => false, 'authenticate' => false, 'message' => 'Unauthorized.'], 401);
})->name('unauthorized');
