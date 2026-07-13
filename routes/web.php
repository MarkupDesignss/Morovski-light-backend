<?php

use App\Http\Controllers\Admin\AuthController;
// use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
// use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\ContactSettingController;
use App\Http\Controllers\Admin\FooterSettingController;
use App\Http\Controllers\Admin\DesputeController;
use App\Http\Controllers\Admin\ReportController;
// use App\Http\Controllers\Admin\UserMembershipController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ShippingChargeController;
use App\Http\Controllers\Admin\ContactRequestController;
use App\Http\Controllers\Admin\BoostSettingController;
// use App\Http\Controllers\Admin\HowItWorksController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\HeaderMenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\CommisionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\ReplacementController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\UserDeleteController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\CouponController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/clear-all', function () {
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');

    return 'All caches cleared!';
});

Route::get('/verify-payment', [OrderController::class, 'verifyPayment']);


// Link storage
Route::get('/run-storage-link', function () {
    Artisan::call('storage:link');
    return 'Storage link created successfully!';
});
    Route::get('/share-product/{slug}', [ItemController::class, 'shareProduct']);
Route::prefix('admin')->name('admin.')->group(function () {

    // Language switcher (public, no auth required)
    Route::get('/lang/{locale}', function ($locale) {
        if (in_array($locale, ['en', 'de'])) {
            session(['admin_locale' => $locale]);
            app()->setLocale($locale);
        }
        return back();
    })->name('lang.switch');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('loginn');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.submit');

    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])
        ->name('forgot-password.form');

    Route::post('/forgot-password', [AuthController::class, 'sendResetOtp'])
        ->name('forgot-password.send');

    // OTP Verify
    Route::get('/verify-otp', [AuthController::class, 'otpForm'])
        ->name('otp.form');

    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])
        ->name('otp.verify');

    // Reset Password
    Route::get('/reset-password', [AuthController::class, 'resetPasswordForm'])
        ->name('reset.form');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('reset.password');

    Route::middleware(['auth:admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        // Update Admin Profile
        Route::put('/profile/update', [AuthController::class, 'update'])->name('profile.update');

        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{id}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::patch('/users/{id}/discount', [UserController::class, 'updateDiscount'])->name('users.update-discount');
        Route::get('/standard-users', [UserController::class, 'standarduserindex'])->name('standard_users.index');
        Route::get('/business-users', [UserController::class, 'businessUserindex'])->name('business-users.index');
        Route::post('/users/assign-role', [UserController::class, 'assignRole'])
            ->name('users.assign-role');
         Route::get('/deleted-users', [UserDeleteController::class, 'index'])->name('deleted-users.index');
         Route::post(
            '/delete-requests/{id}/approve',
            [UserDeleteController::class, 'approve']
        )->name('delete-request.approve');

        Route::post(
            '/delete-requests/{id}/reject',
            [UserDeleteController::class, 'reject']
        )->name('delete-request.reject');

        // Rejected Users
        Route::get('/rejected-users', [UserController::class, 'rejectedUsers'])
            ->name('rejected.users');
        Route::get('/rejected-users/{id}', [UserController::class, 'rejectedUserShow'])
            ->name('rejected.users.show');

        Route::get('/orders', [AdminOrderController::class, 'index'])
            ->name('orders.index');
        Route::post('/dispatch-item', [AdminOrderController::class, 'dispatchItem'])
            ->name('dispatch.item');
        Route::get('/support-tickets', [SupportTicketController::class, 'index'])
            ->name('support-tickets.index');

        Route::get('/support-tickets/{id}', [SupportTicketController::class, 'show'])
            ->name('support-tickets.show');

        // Categories
        Route::resource('categories', CategoryController::class);
        
                // Promocode
        Route::resource('coupons', CouponController::class);

        // Items - CSV Import & Image Uploader (must be before resource)
        Route::get('/items/import', [ItemController::class, 'showImportForm'])->name('items.import.form');
        Route::post('/items/import', [ItemController::class, 'importCsv'])->name('items.import');
        Route::get('/items/import-images', [ItemController::class, 'showImageUploader'])->name('items.import.images');
        Route::post('/items/import-images', [ItemController::class, 'uploadImportImages'])->name('items.import.images.upload');
        Route::delete('/items/import-images/{filename}', [ItemController::class, 'deleteImportImage'])->name('items.import.images.delete');

        Route::get('items/check-sku-similar', [ItemController::class, 'checkSimilarSku'])
        ->name('items.checkSkuSimilar');
        Route::post('/items/import-chunk', [ItemController::class, 'importChunk'])->name('items.importChunk');
        Route::resource('items', ItemController::class);
        Route::delete('item-image/{id}', [ItemController::class, 'deleteImage'])
            ->name('items.delete-image');
            
              // Contact Settings
        Route::get('contact-settings', [ContactSettingController::class, 'index'])->name('contact-settings.index');
        Route::get('contact-settings/edit', [ContactSettingController::class, 'edit'])->name('contact-settings.edit');
        Route::put('contact-settings/update', [ContactSettingController::class, 'update'])->name('contact-settings.update');
            
        // Footer Settings
        Route::get('footer', [FooterSettingController::class, 'index'])->name('footer.index');
        Route::get('footer/edit', [FooterSettingController::class, 'edit'])->name('footer.edit');
        Route::put('footer/update', [FooterSettingController::class, 'update'])->name('footer.update');
            
        // Meemberships
        // Route::resource('memberships', MembershipController::class);
        // Route::get('/subscriptions', [UserMembershipController::class, 'subscriptions'])
        //     ->name('subscriptions');

        // Route::post('/request/{id}', [UserMembershipController::class, 'handleRequest'])
        //     ->name('request.action');
        // Global search (AJAX)
        Route::get('/search', [SearchController::class, 'search'])->name('search');
        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        // Shipping charges
        Route::resource('shipping', ShippingChargeController::class);

        // Order Management
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');

        // CMS
        Route::delete('/pages/image/{image}', [PageController::class, 'deleteImage'])
            ->name('pages.image.delete');
        Route::get('/pages', [PageController::class, 'index'])
            ->name('pages.index');
        Route::get('/pages/create', [PageController::class, 'create'])
            ->name('pages.create');
        Route::post('/pages', [PageController::class, 'store'])
            ->name('pages.store');
        Route::get('/pages/{page}/edit', [PageController::class, 'edit'])
            ->name('pages.edit');
        Route::put('/pages/{page}', [PageController::class, 'update'])
            ->name('pages.update');
        Route::delete('/pages/{page}', [PageController::class, 'destroy'])
            ->name('pages.destroy');
            
        // Staff management
        Route::resource('staff', StaffController::class);
        Route::patch('staff/{id}/toggle-status', [StaffController::class, 'toggleStatus'])
            ->name('staff.toggleStatus');
            
        Route::resource('warehouses', WarehouseController::class)->except(['show']);
        Route::post('warehouses/{id}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status');


        // How It Works

        // Route::get('how-it-works', [HowItWorksController::class, 'index'])
        //     ->name('how-it-works.index');
        // Route::get('how-it-works/create', [HowItWorksController::class, 'create'])
        //     ->name('how-it-works.create');
        // Route::post('how-it-works/store', [HowItWorksController::class, 'store'])
        //     ->name('how-it-works.store');
        // Route::get('how-it-works/edit', [HowItWorksController::class, 'edit'])
        //     ->name('how-it-works.edit');
        // Route::put('how-it-works/update', [HowItWorksController::class, 'update'])
        //     ->name('how-it-works.update');
        // Faqs
        Route::get('faq', [FaqController::class, 'index'])->name('faq.index');
        Route::get('faq/create', [FaqController::class, 'create'])->name('faq.create');
        Route::post('faq/store', [FaqController::class, 'store'])->name('faq.store');
        Route::get('faq/edit-all', [FaqController::class, 'edit'])->name('faq.edit');
        Route::put('faq/update-all', [FaqController::class, 'update'])->name('faq.update');

        // Header
        Route::get('/header', [HeaderMenuController::class, 'index'])->name('header.index');
        Route::get('/header/edit', [HeaderMenuController::class, 'edit'])->name('header.edit');
        Route::post('/header/update', [HeaderMenuController::class, 'update'])->name('header.update');

        // Analytics
        // Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        // Route::get('analytics/data', [AnalyticsController::class, 'getData'])->name('analytics.data');
        // Route::get('analytics/export/csv', [AnalyticsController::class, 'exportCsv'])->name('analytics.csv');
        // Route::get('analytics/export/pdf', [AnalyticsController::class, 'exportPdf'])->name('analytics.pdf');

        // Contact request
        Route::get('contact-requests', [ContactRequestController::class, 'index'])->name('contact_requests.index');
        Route::get('contact-requests/{id}', [ContactRequestController::class, 'show'])->name('contact_requests.show');
            
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
        Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread_list');
        Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread_count');
        Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.unread');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
        Route::delete('/notifications/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
        Route::delete('/notifications', [NotificationController::class, 'deleteAll'])->name('notifications.delete_all');
        // Route::get('/notifications/{id}/pdf', [NotificationController::class, 'downloadPDF'])->name('notifications.pdf');

        // Blogs
        Route::get('blogs', [BlogController::class, 'index'])->name('blogs.index');
        Route::get('blogs/create', [BlogController::class, 'create'])->name('blogs.create');
        Route::post('blogs', [BlogController::class, 'store'])->name('blogs.store');
        Route::get('blogs/{blog}', [BlogController::class, 'show'])->name('blogs.show');
        Route::get('blogs/{blog}/edit', [BlogController::class, 'edit'])->name('blogs.edit');
        Route::put('blogs/{blog}', [BlogController::class, 'update'])->name('blogs.update');
        Route::delete('blogs/{blog}', [BlogController::class, 'destroy'])->name('blogs.destroy');
        Route::delete('/blogs/image/{id}', [BlogController::class, 'deleteImage'])
            ->name('blogs.image.delete');
         
         Route::get(
            '/replacement-requests',
            [ReplacementController::class, 'index']
        )->name('replacement_requests.index');
        Route::get(
            '/replacement-request/{id}',
            [ReplacementController::class, 'show']
        )->name('replacement_requests.show');
        Route::post(
            '/replacement-request/{id}/approve',
            [ReplacementController::class, 'approve']
        )->name('replacement_requests.approve');
        Route::post(
            '/replacement-request/{id}/reject',
            [ReplacementController::class, 'reject']
        )->name('replacement_requests.reject');
        Route::post(
            '/replacement-request/{id}/received',
            [ReplacementController::class, 'markReceived']
        )->name('replacement_requests.received');
    });
});
