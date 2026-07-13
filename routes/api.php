    <?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\MembreshipController;
use App\Http\Controllers\API\SocialAuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ShippingAddressController;
use App\Http\Controllers\API\SellerController;
use App\Http\Controllers\API\FooterApiController;
use App\Http\Controllers\API\BuyerController;
use App\Http\Controllers\API\DesputeController;
use App\Http\Controllers\API\FollowController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\BoostController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\ReplacementController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\NotificationSettingController;
use App\Http\Controllers\API\CmsController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\BlockController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\PromoCodeController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\ItemPDFController;
use App\Http\Controllers\API\SalesInvoiceController;
use App\Http\Controllers\API\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return response()->json(['success' => false, 'message' => 'Authentication token is require to access this api.'], 401);
})->name('login');

// Route::prefix('items')->group(function () {

//     // Generate PDF for selected items
//     Route::post('/pdf', [ItemPDFController::class, 'generatePDF']);
//     Route::post('/pdf1', [ItemPDFController::class, 'generatePDF1']);

//     // Generate PDF as Base64
//     Route::post('/pdf/base64', [ItemPDFController::class, 'generatePDFBase64']);

//     // Generate full report with filters
//     Route::post('/pdf/full', [ItemPDFController::class, 'generateFullReport']);

//     // Get selected items data as JSON
//     Route::post('/data', [ItemPDFController::class, 'getItemsData']);
// });

Route::prefix('items')->group(function () {
    // Generate PDF for selected items (returns blade view)
    Route::post('/pdf', [ItemPDFController::class, 'generatePDF']);
    
    // Generate PDF as downloadable file
    Route::post('/pdfs', [ItemPDFController::class, 'generatePDFDownload']);
    
    // Generate PDF as Base64
    Route::post('/pdf/base64', [ItemPDFController::class, 'generatePDFBase64']);
    
    // Generate full report with filters (returns blade view)
    Route::post('/pdf/full', [ItemPDFController::class, 'generateFullReport']);
    
    // Get selected items data as JSON
    Route::post('/data', [ItemPDFController::class, 'getItemsData']);
    
    // Preview PDF in browser
    Route::post('/pdf/preview', [ItemPDFController::class, 'previewPDF']);
});

Route::get(
    'shared-items/{code}',
    [SalesInvoiceController::class, 'openSharedItems']
);
Route::post('refresh-token', [AuthController::class, 'refreshToken']);

Route::post('send-otp', [AuthController::class, 'sendOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('register-user', [AuthController::class, 'registerUser']);
Route::post('login', [AuthController::class, 'login']);

//Forgot password api
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('verify-reset-otp', [AuthController::class, 'verifyResetOtp']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// Categories and items
Route::get('categories', [CategoryController::class, 'index']);
Route::get('/item-by-category/{id}', [ItemController::class, 'itemBycategory']);
Route::get('/b2b-catalog-items', [ItemController::class, 'b2bItems']);
Route::get('/unique-specifications', [ItemController::class, 'getUniqueSpecifications']);
Route::post('/notify-me', [WarehouseController::class, 'notifyMe']);
Route::get('items/search', [ItemController::class, 'searchItems']);
Route::get('/featured-items', [ItemController::class, 'featuredItems']);
Route::get('/best-sellers', [ItemController::class, 'bestSellers']);
Route::get('/newly-launched', [ItemController::class, 'newlyLaunched']);
Route::get('/newly-arrived', [ItemController::class, 'newlyArrivals']);
Route::get('/share-product/{slug}', [ItemController::class, 'shareProduct']);

// Items
Route::get('/items', [ItemController::class, 'index']);
Route::get('/item/{slug}', [ItemController::class, 'show']);
Route::get('/footer-settings', [FooterApiController::class, 'index']);


// Membership
Route::get('/memberships', [MembreshipController::class, 'list']);
// Route::get('/payment-success', [SubscriptionController::class, 'success']);

// Item review
Route::get('/users/{itemId}/reviews', [ReviewController::class, 'index']);
Route::get('/states', [AuthController::class, 'states']);

// Newsletter subscribe
Route::post('/newsletter-subscribe', [AuthController::class, 'subscribe']);
Route::middleware('auth:api')->group(function () {

    Route::post('/notifications/read/{id}', [WarehouseController::class, 'markNotificationAsRead']);
    Route::post('/notifications/all/read', [WarehouseController::class, 'markNotificationAllAsRead']);
    Route::post('/notifications/delete/{id}', [WarehouseController::class, 'deleteNotification']);
    Route::post('/notifications/all/delete', [WarehouseController::class, 'deleteAllNotification']);
    
    Route::get('/warehouse-list', [WarehouseController::class, 'warehouseList']);
    
    // Refresh token endpoint (uses the expired token to get a new one)

    // User profile and Logout
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('profile', [AuthController::class, 'updateProfile']);
    Route::delete('/remove-profile-picture', [AuthController::class, 'removeProfilePicture']);
    Route::post('update/social/profile', [SocialAuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('deleteaccount', [AuthController::class, 'deleteAccount']);
    Route::post('verifydeleteaccount', [AuthController::class, 'verifyDeleteAccountOtp']);
    Route::get('allUsers', [AuthController::class, 'allUsers']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    

    // Membership's subscription
    // Route::post('/buy-membership', [SubscriptionController::class, 'gold']);
    // Route::post('/gold', [SubscriptionController::class, 'buy']);

    // Shipping address
    Route::post('/shipping-address', [ShippingAddressController::class, 'store']);
    Route::get('/shipping-methods', [ShippingAddressController::class, 'methods']);
    Route::get('/shipping-address', [ShippingAddressController::class, 'index']);
    Route::put('/shipping-address/{id}', [ShippingAddressController::class, 'update']);
    Route::delete('/shipping-address/{id}', [ShippingAddressController::class, 'destroy']);

    //Cart and orders
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/delete-cart', [CartController::class, 'removeCartItem']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::post('/cart/update-quantity', [CartController::class, 'updateCartQuantity']);
    Route::post('/checkout', [CartController::class, 'checkout']);
    Route::post('/place-order', [OrderController::class, 'placeOrder']);
    Route::post('/pay-remaining', [OrderController::class, 'payRemaining']);
    Route::get('pending-order-details/{order_id}', [OrderController::class, 'pendingOrderDetails']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/my-orders-items/{id}', [OrderController::class, 'myOrdersItems']);
    Route::get('/order-details/{id}', [OrderController::class, 'orderDetails']);
    Route::get('/order-by-number/{orderNumber}', [OrderController::class, 'orderByNumber']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::post('/orders/{orderId}/reorder', [OrderController::class, 'reorder']);

    Route::post('/replace-request', [ReplacementController::class, 'store']);
    Route::get('/replacement-requests', [ReplacementController::class, 'index']);
    Route::get('/replacement-request/{id}', [ReplacementController::class, 'show']);
    Route::post(
        'replacement-request/{id}/cancel',
        [ReplacementController::class, 'cancelReplacementRequest']
    );

    Route::post('/support/create-ticket', [ContactController::class, 'createTicket']);
    // Promo Codes
    Route::post('/apply-promocode', [PromoCodeController::class, 'applyPromocode']);
    Route::post('/remove-promocode', [PromoCodeController::class, 'removePromocode']);
    Route::get('/promocodes', [PromoCodeController::class, 'allPromocodes']);

    // Wishlist
    Route::post('/wishlist/add', [WishlistController::class, 'add']);
    Route::post('/wishlist/remove', [WishlistController::class, 'remove']);
    Route::get('/wishlist', [WishlistController::class, 'index']);

    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::post('/order-reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/testimonials', [ReviewController::class, 'getTestimonials']);

    // Notification setting
    Route::get('/notification-settings', [NotificationSettingController::class, 'index']);
    Route::get('/allSettings', [NotificationSettingController::class, 'allSettings']);
    Route::post('/notification-settings', [NotificationSettingController::class, 'update']);




    // Conversations and messaging
    // Route::post('/conversations', [ConversationController::class, 'store']);
    // Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/users/block', [BlockController::class, 'block']);
    Route::patch('/users/unblock/{id}', [BlockController::class, 'unblock']);

    Route::get('/dashboard-counts', [OrderController::class, 'counts']);
    Route::post('send-item-links-whatsapp', [SalesInvoiceController::class, 'sendItemLinksWhatsApp']);


    Route::middleware('warehouse')->group(function () {
        Route::post('/mark-as-delivered', [WarehouseController::class, 'markAsDelivered']);
        Route::get('warehouse-dashboard', [WarehouseController::class, 'warehouseDashboard']);
        Route::get('/warehouse/{id}/items', [WarehouseController::class, 'getWarehouseItems']);
        Route::get('/warehouse/{id}/items-update', [WarehouseController::class, 'getWarehouseItemquantity']);
        Route::post('/items/bulk-upload', [WarehouseController::class, 'bulkUpload']);
        Route::post('/items/bulk-update', [WarehouseController::class, 'bulkUpdate']);
        Route::get('/damaged-items/{id}', [WarehouseController::class, 'getDamagedItems']);
        Route::post('/pack-order', [WarehouseController::class, 'packOrder']);
        Route::get('/getpendingorders', [WarehouseController::class, 'getPendingB2BOrders']);
        Route::post('/dispatch-item', [WarehouseController::class, 'dispatchItem']);
        Route::post('/adjustitemquantity', [WarehouseController::class, 'adjustItemQuantity']);
        Route::post('/damaged-items', [WarehouseController::class, 'reportDamagedStock']);
        Route::post('mark-as-shipped', [WarehouseController::class, 'markAsShipped']);
        Route::get('/replacement-requests', [WarehouseController::class, 'getReplacementRequests']);
        Route::get('/replacement-request-details/{id}', [WarehouseController::class, 'replacementRequestDetails']);
        Route::get('/warehouse-manager-notifications', [WarehouseController::class, 'warehouseManagerNotifications']);
        Route::post('/warehouse-transfers/{id}', [WarehouseController::class, 'updateTransfer']);


        Route::get(
            '/in-transit-items',
            [WarehouseController::class, 'getInTransitItems']
        );
        
        Route::post(
            '/replacement/move-to-damaged',
            [WarehouseController::class, 'moveReplacementToDamaged']
        );
        Route::post(
            '/dispatch-replacement',
            [WarehouseController::class, 'dispatchReplacement']
        );
        Route::post(
            'warehouse-transfer/create',
            [WarehouseController::class, 'createTransfer']
        );
        
        Route::post(
            'warehouse-transfer/dispatch/{id}',
            [WarehouseController::class, 'dispatchTransfer']
        );

        Route::get(
            'warehouse-transfer/list',
            [WarehouseController::class, 'getTransfers']
        );

        Route::post(
            'warehouse-transfer/approve/{id}',
            [WarehouseController::class, 'approveTransfer']
        );

        Route::post(
            'warehouse-transfer/receive/{id}',
            [WarehouseController::class, 'receiveTransfer']
        );
        
        Route::get(
            'warehouse/allocated-items',
            [WarehouseController::class, 'allocatedItems']
        );

        Route::get('/warehouse-items', [WarehouseController::class, 'getItems']);
    });
    Route::middleware('sales')->group(function () {
        Route::get('performance', [SalesInvoiceController::class, 'report']);
        // Route::get('shipping-methods', [SalesInvoiceController::class, 'shippingMethods']);
        // Route::get('sales-executive/performance-report', [SalesInvoiceController::class, 'report']);
        Route::get('/sales-executive-notifications', [SalesInvoiceController::class, 'salesExecutiveNotifications']);
        Route::get('/dashboard', [SalesInvoiceController::class, 'dashboard']);
        // Route::get('/performance', [SalesInvoiceController::class, 'performance']);
        Route::get('proforma-invoices/{id}', [SalesInvoiceController::class, 'getProformaInvoiceDetails']);
        Route::post('/pi/send', [SalesInvoiceController::class, 'sendPI']);
        Route::post('/pi/create', [SalesInvoiceController::class, 'createProformaInvoice']);
        Route::get('/all-performa', [SalesInvoiceController::class, 'getAllProformaInvoices']);
        Route::delete('/delete-performa/{id}', [SalesInvoiceController::class, 'deleteProformaInvoice']);
        Route::post('/pi/payment', [OrderController::class, 'recordPiPayment']);
        Route::post('/performa/mark-paid', [SalesInvoiceController::class, 'markAsPaid']);
        Route::put('/performa/update/{id}', [SalesInvoiceController::class, 'updateProformaInvoice']);
        Route::get('/all-users', [SalesInvoiceController::class, 'getB2BUsers']);
        Route::get('/all-orders', [SalesInvoiceController::class, 'getB2BUserOrders']);
        Route::get('/all-items', [SalesInvoiceController::class, 'allItems']);
        Route::post('/orders/{id}/update-tracking', [SalesInvoiceController::class, 'updateTrackingNumber']);
        Route::post('/update/{id}/orders', [SalesInvoiceController::class, 'updateOrder']);
        Route::post('/create/{id}/orders', [SalesInvoiceController::class, 'createOrderFromInvoice']);
        Route::post(
            'generate-item-link',
            [SalesInvoiceController::class, 'generateItemLink']
        );
        Route::post(
            'sales/orders/allocate-items',
            [SalesInvoiceController::class, 'allocateItems']
        );
    

        Route::post(
            'send-item-links-mail',
            [SalesInvoiceController::class, 'sendItemLinksMail']
        );
        

    });
});

Route::get('/performa/download/{id}', [SalesInvoiceController::class, 'downloadPI']);

// Contact us
Route::post('/contact', [ContactController::class, 'store']);

//Google and apple login

Route::get('/auth/google/redirect', [SocialAuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'googleCallback']);
Route::get('/auth/apple/redirect', [AuthController::class, 'appleRedirect']);
Route::get('/auth/apple/callback', [AuthController::class, 'appleCallback']);

// CMS
Route::get('pages', [CmsController::class, 'index']);
Route::get('contactSetting', [CmsController::class, 'contactSetting']);
Route::get('pages/{slug}', [CmsController::class, 'show']);
Route::get('how-it-works', [CmsController::class, 'howitworks']);
Route::get('platform-advantages', [CmsController::class, 'platformadvantages']);
Route::get('/faqs', [CmsController::class, 'faq']);
Route::get('/header', [CmsController::class, 'getHeader']);

Route::get('/blogs', [BlogController::class, 'apiIndex']);
Route::get('/blogs/{slug}', [BlogController::class, 'apiShow']);

Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => 'API endpoint not found',
    ], 404);
});