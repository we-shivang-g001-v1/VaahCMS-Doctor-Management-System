<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(
    [
        'prefix'     => '/',
        'middleware' => ['web'],
        'namespace' => 'Frontend',
    ],
    function () {
        //------------------------------------------------
        Route::get( '/', 'FrontendController@index' )
            ->name( 'vh.frontend.bulmablogtheme' );
        //------------------------------------------------
        Route::get('/category/{slug}', 'FrontendController@searchCategory')
            ->name('vh.frontend.bulmablogtheme.category');
        //------------------------------------------------
        Route::get('/search', 'FrontendController@searchResult')
            ->name('vh.frontend.bulmablogtheme.search');
        //------------------------------------------------
        //------------------------------------------------
    });

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(
    [
        'prefix' => '/ajax/',
        'middleware' => ['web'],
        'namespace' => 'Frontend',
    ],
    function () {
        //------------------------------------------------
        Route::post('/getBlogList', 'AjaxController@getBLogList')
            ->name('vh.frontend.ajax.getBlogList');
        //------------------------------------------------
        Route::post('/store-form', 'AjaxController@storeForm')
            ->name('vh.frontend.bulmablogtheme.ajax.store-form');
        //------------------------------------------------
    });
