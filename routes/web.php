<?php

use App\Admin\Showcase\Actions\SampleCsv;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

// The file of the showcase's download examples, behind a temporary signed URL.
Route::get('/showcase/sample.csv', SampleCsv::class)->middleware('signed')->name('showcase.sample-csv');
