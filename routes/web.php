<?php

use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GeneratedController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Member\DashboardController as MemberDashboard;
use App\Http\Controllers\Member\GeneratedController as MemberGenerated;
use App\Http\Controllers\Super\ClientController;
use App\Http\Controllers\Super\DashboardController;
use App\Http\Controllers\URLResolver;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Member;
use App\Http\Middleware\Super;
use Illuminate\Support\Facades\Route;

// Authentication
Route::view('/', 'auth.login')->name('home');
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Super Admin
Route::prefix('super')->name('super.')->middleware(['auth', Super::class])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('clients/store', [ClientController::class, 'store'])->name('clients.store');
});

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', Admin::class])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');
    Route::get('urls/create', [GeneratedController::class, 'create'])->name('urls.create');
    Route::post('urls/store', [GeneratedController::class, 'store'])->name('urls.store');
    Route::get('clients/create', [AdminClientController::class, 'create'])->name('clients.create');
    Route::post('clients/store', [AdminClientController::class, 'store'])->name('clients.store');
});

// Member
Route::prefix('member')->name('member.')->middleware(['auth', Member::class])->group(function () {
    Route::get('dashboard', [MemberDashboard::class, 'index'])->name('dashboard.index');
    Route::get('urls/create', [MemberGenerated::class, 'create'])->name('urls.create');
    Route::post('urls/store', [MemberGenerated::class, 'store'])->name('urls.store');
});

// public URL resolver
Route::get('{short_url}', [URLResolver::class, 'resolve']);
