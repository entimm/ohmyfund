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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/rank', 'HomeController@rank')->name('rank');

Route::get('/watchlist2', 'HomeController@watchlist2')->name('watchlist2');

Route::get('/watchlist1', 'HomeController@watchlist1')->name('watchlist1');

Route::get('/evaluate', 'HomeController@evaluate')->name('evaluate');

Route::get('/compare', 'HomeController@compare')->name('compare');

Route::get('/stock/{stock}', 'HomeController@stock')->name('stock');

Route::get('/fund/{fund}', 'HomeController@fund')->name('fund');

Route::resource('monitor', 'MonitorController');
