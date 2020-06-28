<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('v1')->group(function () {
    // public api
    // login
    Route::post('login', 'AuthController@login');
    // register
    Route::post('register', 'AuthController@register');
    Route::get('categories/random/{count}', 'CategoryController@random');
    Route::get('book/top/{count}', 'BookController@top');
    Route::get('book', 'BookController@index');
    Route::get('categories' ,'CategoryController@index');
    // detail categories
    Route::get('categories/detail/{slug}', 'CategoryController@slug');
    Route::get('books/detail/{slug}', 'BookController@slug');
    ROute::get('book/search/{keyword}', 'BookController@search');
    // region
    Route::get('province', 'ShopController@provinces');
    Route::get('city', 'ShopController@cities');

    Route::get('couriers', 'ShopController@couriers');
    
    // private api
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', 'AuthController@logout');
        Route::post('/shipping','ShopController@shipping');
       
        Route::post('services', 'ShopController@services');
    });
});
