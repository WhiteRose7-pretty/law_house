<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'WelcomeController@index' )->name('welcome');
Route::get('/home1', 'WelcomeController@index1' )->name('welcome1');

Auth::routes(['verify' => true]);


Route::get('/app/{any}', 'App\SpaController@index')->where(['any'=>'.*'])->middleware('verified')->name('app');
Route::redirect('/app','/app/start');

Route::get('/admin/{any}', 'Admin\SpaController@index')->where(['any'=>'.*'])->middleware('verified');
Route::redirect('/admin','/admin/start');

Route::get('/ping', 'Auth\HashController@ping');

Route::get('/lista/aktualnosci/{page?}','ContentController@news')->name('news.list');
Route::get('/aktualnosci/{title_uri}','ContentController@view')->name('news');
Route::get('/czytaj/{title_uri}','ContentController@view')->name('promo');
Route::get('/czytaj2/{title_uri}','ContentController@view')->name('promo2');
Route::get('/informacje/{title_uri}','ContentController@view')->name('info');
Route::get('/przepisy-prawne/{title_uri}','ContentController@view')->name('regula');


Route::get('/content/publish/{id}','ContentController@publish')->name('publish');

Route::get('/kontakt/{ok?}','ContactController@form')->name('contact');
Route::post('/kontakt','ContactController@send')->name('contact.send');

Route::get('/verify/email/change/{code?}','Auth\EmailChangeController@index')->name('verify.email.change');
Route::get('logout',function(){
    Auth::logout();
    return redirect('/');
})->middleware("auth");

