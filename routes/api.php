<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Api\Manager\UserController as ApiUserController;
use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\PointsApiController;
use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\AnnouncementApiCustomerController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\EventApiCustomerController;

// ----------------------
// API v1 Routes
// ----------------------
Route::prefix('v1')->group(function () {

    // Health check
    Route::get('ping', fn () => response()->json(['ok' => true, 'time' => now()]));

    // ----------------------
    // Auth (Sanctum)
    // ----------------------
    Route::middleware('throttle:api')->group(function () {
        Route::post('auth/login',    [AuthController::class, 'login']);
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get ('auth/me',       [AuthController::class, 'me'])->middleware('auth:sanctum');
    });

    // ----------------------
    // Manager-only User CRUD
    // ----------------------
    Route::middleware(['auth:sanctum', 'role:manager', 'throttle:api'])
        ->prefix('manager')->name('api.manager.')->group(function () {
        Route::apiResource('users', ApiUserController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy']);
    });

    // ----------------------
    // Public: Books & Users
    // ----------------------
    Route::get('/books', [BookApiController::class, 'index']);
    Route::get('/books/{book}', [BookApiController::class, 'show']);
    Route::get('/users/{user}', [ApiUserController::class, 'show']);

    // ----------------------
    // Protected: Books CRUD
    // ----------------------
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/books', [BookApiController::class, 'store']);
        Route::put('/books/{book}', [BookApiController::class, 'update']);
        Route::delete('/books/{book}', [BookApiController::class, 'destroy']);
    });

    // ----------------------
    // Reviews API
    // ----------------------
    Route::get('/books/{book}/ratings', [ReviewController::class, 'apiRatingsSummary']);
Route::get('/books/{book}/reviews', [ReviewController::class, 'apiList']);

Route::middleware('auth:sanctum')->group(function () {
    // Create review (any authenticated user)
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store']);
    // Update/Delete review (reviewer only)
    Route::put('/reviews/{review}', [ReviewController::class, 'apiUpdate']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'apiDestroy']);
    // Flag/unflag review (other users)
    Route::patch('/reviews/{review}/flag', [ReviewController::class, 'flag']);
    Route::patch('/reviews/{review}/unflag', [ReviewController::class, 'unflag']);
    // Hide/unhide review (staff/admin only)
    Route::patch('/reviews/{review}/hide', [ReviewController::class, 'hide'])->middleware('role:staff,manager');
    Route::patch('/reviews/{review}/unhide', [ReviewController::class, 'unhide'])->middleware('role:staff,manager');
    // Logged-in user review history
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
    // Average rating
    Route::get('/books/{book}/average-rating', [ReviewController::class, 'averageRating']);
});

    // ----------------------
    // Inventory
    // ----------------------
    Route::get('/inventory/{book}/stock', [InventoryApiController::class, 'stock']);
    Route::post('/books/{book}/decrement', [BookApiController::class, 'decrement'])
        ->middleware('throttle:5,1');

    // ----------------------
    // Cart (customer)
    // ----------------------
    Route::middleware(['auth:sanctum'])->name('api.cart.')->group(function () {
        Route::get('/cart', [CartApiController::class, 'index'])->name('index');
        Route::post('/cart', [CartApiController::class, 'store'])->name('store');
        Route::patch('/cart/{cartItem}', [CartApiController::class, 'update'])->name('update');
        Route::delete('/cart/{cartItem}', [CartApiController::class, 'destroy'])->name('destroy');
    });

    // ----------------------
    // Orders (customer)
    // ----------------------
    Route::middleware(['auth:sanctum'])->name('api.orders.')->group(function () {
        Route::get('/orders', [OrderApiController::class, 'index'])->name('index');
        Route::post('/orders', [OrderApiController::class, 'store'])->name('store');
        Route::get('/orders/{order}', [OrderApiController::class, 'show'])->name('show');
        Route::patch('/orders/{order}', [OrderApiController::class, 'update'])->name('update');
        Route::patch('/orders/{order}/cancel', [OrderApiController::class, 'cancel'])->name('cancel');
        Route::post('/checkout', [OrderApiController::class, 'checkout'])->name('checkout');
    });

    // ----------------------
    // Staff/Manager Orders
    // ----------------------
    Route::middleware(['auth:sanctum', 'role:staff,manager'])->group(function () {
        Route::get('/staff/orders', [OrderApiController::class, 'staffIndex']);
        Route::get('/staff/orders/{order}', [OrderApiController::class, 'staffShow']);
    });

    // ----------------------
    // Points
    // ----------------------
    Route::middleware(['auth:sanctum', 'role:staff,manager,customer'])->group(function () {
        Route::post('/users/{user}/points/redeem', [PointsApiController::class, 'redeem']);
    });

    Route::get('users/{user}/points', [PointsApiController::class, 'show']);
    Route::post('users/{user}/points/add', [PointsApiController::class, 'add']);
});

// ----------------------
// Staff Routes
// ----------------------
Route::prefix('v1/staff')->group(function () {
    // Announcements
    Route::get('/announcements', [AnnouncementApiController::class, 'index']);
    Route::post('/announcements', [AnnouncementApiController::class, 'store']);
    Route::get('/announcements/{announcement}', [AnnouncementApiController::class, 'show']);
    Route::put('/announcements/{announcement}', [AnnouncementApiController::class, 'update']);
    Route::delete('/announcements/{announcement}', [AnnouncementApiController::class, 'destroy']);
    // Events
    Route::get('/events', [EventApiController::class, 'index']);
    Route::post('/events', [EventApiController::class, 'store']);
    Route::get('/events/{event}', [EventApiController::class, 'show']);
    Route::put('/events/{event}', [EventApiController::class, 'update']);
    Route::delete('/events/{event}', [EventApiController::class, 'destroy']);
});

// ----------------------
// Customer Routes (Public)
// ----------------------
Route::prefix('v1')->group(function () {
    Route::get('/announcements', [AnnouncementApiCustomerController::class, 'index']);
    Route::get('/announcements/{announcement}', [AnnouncementApiCustomerController::class, 'show']);
    Route::get('/events', [EventApiCustomerController::class, 'index']);
    Route::get('/events/{event}', [EventApiCustomerController::class, 'show']);
    Route::post('/events/{event}/register', [EventApiCustomerController::class, 'register']);
});

// ----------------------
// JSON 404 fallback
// ----------------------
Route::fallback(fn () => response()->json(['message' => 'Not Found'], 404));