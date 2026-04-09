<?php

use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'index'])->name('endatix.index');
Route::get('/show/{id}', [WebController::class, 'show'])->name('endatix.show');
Route::get('/submission/{id}', [WebController::class, 'submission'])->name('endatix.submission');
Route::get('/submission/{form_id}/{submission_id}', [WebController::class, 'getSingleSubmission'])->name('endatix.singlesubmission');
