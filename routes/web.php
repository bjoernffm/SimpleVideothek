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

Auth::routes();
Route::get('/', 'MediasController@index');

Route::resource('/videos', 'VideosController');
Route::resource('/media', 'MediasController');
Route::get('/assets/{type}/{file}', 'AssetsController@returnMedia')->name('assets_thing');

Route::get('/home', 'HomeController@index')->name('home');
