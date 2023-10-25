<?php

use App\Http\Controllers\Controller;
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

Route::get('/', [Controller::class, 'index'])->name('index');

Route::post('/lgin', [Controller::class, 'login'])->name('login');
Route::get('/lgout', [Controller::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/{tk}/{mk}', [Controller::class, 'insertAdmin']);

Route::post('/post/user', [Controller::class, 'insertUser'])->middleware('auth')->name('insertUser');
Route::post('/post/pay', [Controller::class, 'insertPay'])->middleware('auth')->name('insertPay');

Route::post('/post/spending', [Controller::class, 'insertSpending'])->middleware('auth')->name('insertSpending');

Route::get('/check/pay', [Controller::class, 'checkPay'])->middleware('auth')->name('checkPay');

Route::get('/truncate/pay', [Controller::class, 'truncatePay'])->middleware('auth')->name('truncatePay');
Route::get('/destroy/member/pay', [Controller::class, 'destroyMemberPay'])->middleware('auth')->name('destroyMemberPay');

Route::get('/export', [Controller::class, 'export'])->middleware('auth')->name('export');

Route::post('/import', [Controller::class, 'import'])->middleware('auth')->name('import');