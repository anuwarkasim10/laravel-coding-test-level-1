<?php

use App\Http\Controllers\EventsController;
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

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', [EventsController::class, 'index'])->name('events-index');
    Route::get('/show/{id}', [EventsController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [EventsController::class, 'edit'])->name('edit');
    Route::get('/create', [EventsController::class, 'create'])->name('create');
    Route::post('/', [EventsController::class, 'store'])->name('store');

    Route::put('/{id}/update', [EventsController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [EventsController::class, 'destroy'])->name('destroy');
});
