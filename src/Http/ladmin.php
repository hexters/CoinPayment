<?php 

use Illuminate\Support\Facades\Route;

Route::prefix('coinpayment')->group(function() {
  Route::get('/lls', function() {
    return 'lls';
  });
});