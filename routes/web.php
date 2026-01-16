<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RoleLoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Manager\UserAdminController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Manager\TransactionController as ManagerTransactionController;
use App\Http\Controllers\Staff\OrderController as StaffOrderController;
use App\Http\Controllers\Manager\SalesReportController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventCustomerController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnnouncementCustomerController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::view('/', 'home')->name('home');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/login/manager',  [RoleLoginController::class, 'showManager'])->name('login.manager');
Route::get('/login/staff',    [RoleLoginController::class, 'showStaff'])->name('login.staff');
Route::get('/login/customer', [RoleLoginController::class, 'showCustomer'])->name('login.customer');
Route::post('/login/{type}',  [RoleLoginController::class, 'login'])->name('login.role'); // {type}=manager|staff|customer
Route::post('/logout',        [RoleLoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboards
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::view('/', 'dashboards.manager')->name('dashboard');

    Route::resource('users', UserAdminController::class)
        ->only(['index','create','store','edit','update','destroy'])
        ->names('users');

    // Optional CSV export
    Route::get('users/export', [UserAdminController::class, 'export'])->name('users.export');

    // Report
    Route::get('reports/customers', [ReportController::class, 'customersMonthly'])->name('reports.customers');

    Route::get('/transactions', [ManagerTransactionController::class, 'index'])
      ->name('transactions.index');
    Route::get('/transactions/{tx}', [ManagerTransactionController::class, 'show'])
      ->name('transactions.show');
});

Route::middleware(['auth','role:staff'])->group(function () {
    Route::view('/staff', 'dashboards.staff')->name('staff.dashboard');
    // Events CRUD
    Route::get('staff/events', [EventController::class,'index'])->name('staff.events.index');
    Route::get('staff/events/create', [EventController::class,'create'])->name('staff.events.create');
    Route::post('staff/events', [EventController::class,'store'])->name('staff.events.store');
    Route::get('staff/events/{event}/edit', [EventController::class,'edit'])->name('staff.events.edit');
    Route::put('staff/events/{event}', [EventController::class,'update'])->name('staff.events.update');
    Route::delete('staff/events/{event}', [EventController::class,'destroy'])->name('staff.events.destroy');

    // Announcements CRUD
    Route::get('staff/announcements', [AnnouncementController::class,'index'])->name('staff.ann.index');
    Route::get('staff/announcements/create', [AnnouncementController::class,'create'])->name('staff.ann.create');
    Route::post('staff/announcements', [AnnouncementController::class,'store'])->name('staff.ann.store');
    Route::get('staff/announcements/{announcement}/edit', [AnnouncementController::class,'edit'])->name('staff.ann.edit');
    Route::put('staff/announcements/{announcement}', [AnnouncementController::class,'update'])->name('staff.ann.update');
    Route::delete('staff/announcements/{announcement}', [AnnouncementController::class,'destroy'])->name('staff.ann.destroy');

    // Back-compat alias for old code using dashboard.staff
    Route::redirect('/dashboard/staff', '/staff')->name('dashboard.staff');
});

Route::middleware(['auth','role:customer'])->group(function () {
    Route::view('/customer', 'dashboards.customer')->name('customer.dashboard');
    Route::get('/events', [EventCustomerController::class,'index'])->name('cust.events.index');
    Route::get('/events/{event:slug}', [EventCustomerController::class,'show'])->name('cust.events.show');
    Route::post('/events/{event:slug}/register', [EventCustomerController::class,'register'])->name('cust.events.register');

    // Announcements
    Route::get('/announcements', [AnnouncementCustomerController::class,'index'])->name('cust.ann.index');
    Route::get('/announcements/{announcement}', [AnnouncementCustomerController::class,'show'])->name('cust.ann.show');
});

/*
|--------------------------------------------------------------------------
| Profile (blocked for manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:staff,customer'])->group(function () {
    Route::get('/profile', [ProfileController::class,'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class,'update'])->name('profile.update');

    // Cart
    Route::get('/cart',               [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add',          [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{cartItem}',  [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout & orders
    Route::get( '/checkout',                [OrderController::class, 'showCheckout'])->name('checkout.show');
    Route::post('/checkout',                [OrderController::class, 'checkout'])->name('checkout');
    Route::get( '/orders',                  [OrderController::class, 'index'])->name('orders.index');
    Route::get( '/orders/{order}',          [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/address', [OrderController::class, 'updateAddress'])->name('orders.address');
    Route::patch('/orders/{order}/cancel',  [OrderController::class, 'cancel'])->name('orders.cancel');
});

/*
|--------------------------------------------------------------------------
| Reviews
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Customer reviews
    Route::get('/catalog/{book}/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/catalog/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/catalog/{book}/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/catalog/{book}/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Flag/unflag review (customer)
    Route::post('/reviews/{review}/flag', [ReviewController::class, 'flag'])->name('reviews.flag');
    Route::post('/reviews/{review}/unflag', [ReviewController::class, 'unflag'])->name('reviews.unflag');
});

Route::middleware(['auth','role:manager'])->group(function () {
    Route::get('/reviews/flagged', [ReviewController::class, 'flagged'])->name('reviews.flagged');
    Route::patch('/reviews/{review}/hide',   [ReviewController::class, 'managerHide'])->name('reviews.managerHide');
    Route::patch('/reviews/{review}/unhide', [ReviewController::class, 'managerUnhide'])->name('reviews.managerUnhide');
    Route::patch('/reviews/{review}/trust',  [ReviewController::class, 'managerTrust'])->name('reviews.managerTrust');
    Route::delete('/reviews/{review}',       [ReviewController::class, 'managerDelete'])->name('reviews.managerDelete');
});

// Manager reports sales 
Route::middleware(['auth'/*,'role:manager'*/])->group(function () {
    Route::get('/manager/reports/sales', [SalesReportController::class, 'index'])
         ->name('manager.reports.sales');
});

/*
|--------------------------------------------------------------------------
| Books CRUD & Inventory (staff or manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:staff,manager'])->group(function () {
    Route::resource('books', BookController::class);
    Route::post('books/{book}/stock',   [InventoryController::class, 'adjust'])
        ->name('inventory.adjust')
        ->middleware('throttle:5,1');
    Route::get( 'books/{book}/history', [InventoryController::class, 'history'])->name('inventory.history');
});

/*
|--------------------------------------------------------------------------
| Staff orders
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/orders',        [StaffOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}',[StaffOrderController::class, 'show'])->name('orders.show');
    });

/*
|--------------------------------------------------------------------------
| Order state transitions: staff or manager only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','role:staff,manager'])->group(function () {
    Route::patch('/orders/{order}/ship',     [OrderController::class, 'ship'])->name('orders.ship');
    Route::patch('/orders/{order}/arrive',   [OrderController::class, 'arrive'])->name('orders.arrive');
    Route::patch('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
});

/*
|--------------------------------------------------------------------------
| Public catalog
|--------------------------------------------------------------------------
*/
Route::get('/catalog',        [BookController::class, 'customerIndex'])->name('customer.index');
Route::get('/catalog/{book}', [BookController::class, 'customerShow'])->name('customer.show');