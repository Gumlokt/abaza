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

//Route::get('/', function () {
//    return view('welcome');
//});



Route::get('/', 'IndexController@index');

Route::get('/autocomplete', 'IndexController@autocomplete');
Route::get('/translation', 'IndexController@translation');

Route::post('/{action}', 'ParseController@parse');



Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

