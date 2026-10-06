<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'HomeController::index', ['as' => 'store.home']);
$routes->get('delivery-policy', 'PolicyController::show/delivery', ['as' => 'policy.delivery']);
$routes->get('return-policy', 'PolicyController::show/returns', ['as' => 'policy.returns']);
$routes->get('privacy-terms', 'PolicyController::show/privacy_terms', ['as' => 'policy.privacy']);

$routes->group('', ['filter' => 'customerGuest'], static function ($routes) {
    $routes->get('register', 'Auth\CustomerAuthController::registerForm', ['as' => 'customer.register']);
    $routes->post('register', 'Auth\CustomerAuthController::register');
    $routes->get('login', 'Auth\CustomerAuthController::loginForm', ['as' => 'customer.login']);
    $routes->post('login', 'Auth\CustomerAuthController::login');
});
$routes->post('logout', 'Auth\CustomerAuthController::logout', ['filter' => 'customerAuth', 'as' => 'customer.logout']);
$routes->get('account', 'AccountController::index', ['filter' => 'customerAuth', 'as' => 'customer.account']);
$routes->get('account/orders','AccountController::orders',['filter'=>'customerAuth','as'=>'customer.orders']);$routes->get('account/orders/(:segment)','AccountController::order/$1',['filter'=>'customerAuth','as'=>'customer.order']);
$routes->get('search', 'SearchController::index', ['as' => 'store.search']);
$routes->get('sitemap.xml', 'GuideController::sitemap', ['as' => 'store.sitemap']);
$routes->get('guides', 'GuideController::index', ['as' => 'guides.index']);
$routes->get('guides/search', 'GuideController::search', ['as' => 'guides.search']);
$routes->get('guides/category/(:segment)', 'GuideController::category/$1', ['as' => 'guides.category']);
$routes->get('guides/(:segment)', 'GuideController::show/$1', ['as' => 'guides.show']);
$routes->get('videos', 'VideoController::index', ['as' => 'videos.index']);
$routes->get('videos/(:segment)', 'VideoController::show/$1', ['as' => 'videos.show']);
$routes->get('cart','CartController::index',['as'=>'cart.index']);$routes->post('cart/add','CartController::add',['as'=>'cart.add']);$routes->post('cart/update','CartController::update',['as'=>'cart.update']);$routes->post('cart/remove','CartController::remove',['as'=>'cart.remove']);$routes->post('cart/clear','CartController::clear',['as'=>'cart.clear']);$routes->post('buy-now','CartController::buyNow',['as'=>'buy.now']);
$routes->get('checkout','CheckoutController::index',['as'=>'checkout.index']);$routes->post('checkout/delivery-charge','CheckoutController::deliveryCharge',['as'=>'checkout.delivery']);$routes->post('checkout/place-order','CheckoutController::placeOrder',['as'=>'checkout.place']);
$routes->get('api/locations/upazilas/(:num)','LocationController::upazilas/$1',['as'=>'locations.upazilas']);$routes->get('order-success/(:segment)','OrderController::success/$1',['as'=>'order.success']);$routes->get('track-order','OrderController::track',['as'=>'order.track']);$routes->post('track-order','OrderController::lookup');
$routes->get('products', 'Catalog\ProductController::index', ['as' => 'catalog.products']);
$routes->get('categories', 'Catalog\CategoryController::index', ['as' => 'catalog.categories']);
$routes->get('products/(:segment)', 'Catalog\ProductController::show/$1', ['as' => 'catalog.product']);
$routes->get('category/(:segment)', 'Catalog\ProductController::category/$1', ['as' => 'catalog.category']);
$routes->get('packages', 'Catalog\PackageController::index', ['as' => 'catalog.packages']);
$routes->get('packages/(:segment)', 'Catalog\PackageController::show/$1', ['as' => 'catalog.package']);
$routes->get('review/(:segment)/(:num)', 'ReviewController::create/$1/$2', ['as' => 'review.create']);
$routes->post('review/(:segment)/(:num)', 'ReviewController::store/$1/$2', ['as' => 'review.store']);

$routes->group('admin', static function ($routes) {
    $routes->get('login', 'Admin\AuthController::loginForm', ['filter' => 'adminGuest', 'as' => 'admin.login']);
    $routes->post('login', 'Admin\AuthController::login', ['filter' => 'adminGuest']);
    $routes->post('logout', 'Admin\AuthController::logout', ['filter' => 'adminAuth', 'as' => 'admin.logout']);
        $routes->group('', ['filter' => 'adminAuth'], static function ($routes) {
        $routes->get('', 'Admin\DashboardController::index');
        $routes->get('dashboard', 'Admin\DashboardController::index', ['as' => 'admin.dashboard']);
        $routes->get('profile', 'Admin\ProfileController::index', ['as' => 'admin.profile']);
$routes->get('delivery-zones','Admin\DeliveryZoneController::index',['filter'=>'adminPermission:delivery.manage','as'=>'admin.delivery']);$routes->get('delivery-zones/create','Admin\DeliveryZoneController::create',['filter'=>'adminPermission:delivery.manage','as'=>'admin.delivery.create']);$routes->post('delivery-zones','Admin\DeliveryZoneController::store',['filter'=>'adminPermission:delivery.manage']);$routes->get('delivery-zones/(:num)/edit','Admin\DeliveryZoneController::edit/$1',['filter'=>'adminPermission:delivery.manage','as'=>'admin.delivery.edit']);$routes->put('delivery-zones/(:num)','Admin\DeliveryZoneController::update/$1',['filter'=>'adminPermission:delivery.manage']);$routes->post('delivery-zones/(:num)/delete','Admin\DeliveryZoneController::delete/$1',['filter'=>'adminPermission:delivery.manage']);
$routes->get('payment-methods','Admin\PaymentMethodController::index',['filter'=>'adminPermission:payments.verify','as'=>'admin.payment.methods']);$routes->post('payment-methods/(:num)','Admin\PaymentMethodController::update/$1',['filter'=>'adminPermission:payments.verify']);
$routes->get('shipments','Admin\ShipmentController::index',['filter'=>'adminPermission:shipments.view','as'=>'admin.shipments']);
$routes->get('orders/(:num)/shipment','Admin\ShipmentController::order/$1',['filter'=>'adminPermission:shipments.view','as'=>'admin.shipments.order']);
$routes->post('orders/(:num)/shipment','Admin\ShipmentController::prepare/$1',['filter'=>'adminPermission:shipments.manage']);
$routes->post('shipments/(:num)/book','Admin\ShipmentController::book/$1',['filter'=>'adminPermission:shipments.manage']);
$routes->post('shipments/(:num)/status','Admin\ShipmentController::status/$1',['filter'=>'adminPermission:shipments.manage']);
$routes->get('sms','Admin\SmsController::index',['filter'=>'adminPermission:sms.manage','as'=>'admin.sms']);
$routes->post('sms/settings','Admin\SmsController::update',['filter'=>'adminPermission:sms.manage']);
$routes->post('sms/(:num)/retry','Admin\SmsController::retry/$1',['filter'=>'adminPermission:sms.manage']);
$routes->get('reviews','Admin\ReviewController::index',['filter'=>'adminPermission:reviews.view','as'=>'admin.reviews']);
$routes->get('reviews/(:num)','Admin\ReviewController::show/$1',['filter'=>'adminPermission:reviews.view','as'=>'admin.reviews.show']);
$routes->post('reviews/(:num)/moderate','Admin\ReviewController::moderate/$1',['filter'=>'adminPermission:reviews.manage']);
$routes->post('reviews/(:num)/reply','Admin\ReviewController::reply/$1',['filter'=>'adminPermission:reviews.manage']);
$routes->post('reviews/(:num)/delete','Admin\ReviewController::delete/$1',['filter'=>'adminPermission:reviews.manage']);
$routes->get('orders','Admin\OrderController::index',['filter'=>'adminPermission:orders.view','as'=>'admin.orders']);$routes->get('orders/create','Admin\OrderController::create',['filter'=>'adminPermission:orders.manage','as'=>'admin.orders.create']);$routes->post('orders','Admin\OrderController::store',['filter'=>'adminPermission:orders.manage','as'=>'admin.orders.store']);$routes->get('orders/(:num)','Admin\OrderController::show/$1',['filter'=>'adminPermission:orders.view','as'=>'admin.orders.show']);$routes->post('orders/(:num)/status','Admin\OrderController::status/$1',['filter'=>'adminPermission:orders.manage']);$routes->post('orders/(:num)/payment','Admin\OrderController::payment/$1',['filter'=>'adminPermission:payments.verify']);$routes->post('orders/(:num)/delivery-payment','Admin\OrderController::deliveryPayment/$1',['filter'=>'adminPermission:payments.verify']);$routes->post('orders/(:num)/note','Admin\OrderController::note/$1',['filter'=>'adminPermission:orders.manage']);$routes->get('orders/(:num)/print','Admin\OrderController::print/$1',['filter'=>'adminPermission:orders.view','as'=>'admin.orders.print']);
        $routes->post('profile', 'Admin\ProfileController::update');
        $routes->get('customers', 'Admin\CustomerController::index', ['as' => 'admin.customers']);
        $routes->get('customers/(:num)', 'Admin\CustomerController::show/$1', ['as' => 'admin.customer.show']);
        $routes->post('customers/(:num)/status', 'Admin\CustomerController::status/$1');
        $routes->group('guides', ['filter' => 'adminPermission:guides.view'], static function ($routes) {
            $routes->get('', 'Admin\GuideController::index', ['as' => 'admin.guides']);
            $routes->get('create', 'Admin\GuideController::create', ['filter' => 'adminPermission:guides.manage', 'as' => 'admin.guides.create']);
            $routes->post('', 'Admin\GuideController::store', ['filter' => 'adminPermission:guides.manage']);
            $routes->get('(:num)/edit', 'Admin\GuideController::edit/$1', ['filter' => 'adminPermission:guides.manage', 'as' => 'admin.guides.edit']);
            $routes->put('(:num)', 'Admin\GuideController::update/$1', ['filter' => 'adminPermission:guides.manage']);
            $routes->post('(:num)/delete', 'Admin\GuideController::delete/$1', ['filter' => 'adminPermission:guides.manage']);
        });
        $routes->group('guide-categories', ['filter' => 'adminPermission:guide_categories.view'], static function ($routes) {
            $routes->get('', 'Admin\GuideCategoryController::index', ['as' => 'admin.guide.categories']);
            $routes->get('create', 'Admin\GuideCategoryController::create', ['filter' => 'adminPermission:guide_categories.manage', 'as' => 'admin.guide.categories.create']);
            $routes->post('', 'Admin\GuideCategoryController::store', ['filter' => 'adminPermission:guide_categories.manage']);
            $routes->get('(:num)/edit', 'Admin\GuideCategoryController::edit/$1', ['filter' => 'adminPermission:guide_categories.manage', 'as' => 'admin.guide.categories.edit']);
            $routes->put('(:num)', 'Admin\GuideCategoryController::update/$1', ['filter' => 'adminPermission:guide_categories.manage']);
            $routes->post('(:num)/delete', 'Admin\GuideCategoryController::delete/$1', ['filter' => 'adminPermission:guide_categories.manage']);
        });
        $routes->group('videos', ['filter' => 'adminPermission:videos.view'], static function ($routes) {
            $routes->get('', 'Admin\VideoController::index', ['as' => 'admin.videos']);
            $routes->get('create', 'Admin\VideoController::create', ['filter' => 'adminPermission:videos.manage', 'as' => 'admin.videos.create']);
            $routes->post('', 'Admin\VideoController::store', ['filter' => 'adminPermission:videos.manage']);
            $routes->get('(:num)/edit', 'Admin\VideoController::edit/$1', ['filter' => 'adminPermission:videos.manage', 'as' => 'admin.videos.edit']);
            $routes->put('(:num)', 'Admin\VideoController::update/$1', ['filter' => 'adminPermission:videos.manage']);
            $routes->post('(:num)/delete', 'Admin\VideoController::delete/$1', ['filter' => 'adminPermission:videos.manage']);
        });
        $routes->group('video-categories', ['filter' => 'adminPermission:video_categories.view'], static function ($routes) {
            $routes->get('', 'Admin\VideoCategoryController::index', ['as' => 'admin.video.categories']);
            $routes->get('create', 'Admin\VideoCategoryController::create', ['filter' => 'adminPermission:video_categories.manage', 'as' => 'admin.video.categories.create']);
            $routes->post('', 'Admin\VideoCategoryController::store', ['filter' => 'adminPermission:video_categories.manage']);
            $routes->get('(:num)/edit', 'Admin\VideoCategoryController::edit/$1', ['filter' => 'adminPermission:video_categories.manage', 'as' => 'admin.video.categories.edit']);
            $routes->put('(:num)', 'Admin\VideoCategoryController::update/$1', ['filter' => 'adminPermission:video_categories.manage']);
            $routes->post('(:num)/delete', 'Admin\VideoCategoryController::delete/$1', ['filter' => 'adminPermission:video_categories.manage']);
        });
        $routes->group('categories', ['filter' => 'adminPermission:categories.view'], static function ($routes) {
            $routes->get('', 'Admin\CategoryController::index', ['as' => 'admin.categories']);
            $routes->get('create', 'Admin\CategoryController::create', ['filter' => 'adminPermission:categories.manage', 'as' => 'admin.categories.create']);
            $routes->post('', 'Admin\CategoryController::store', ['filter' => 'adminPermission:categories.manage']);
            $routes->get('(:num)/edit', 'Admin\CategoryController::edit/$1', ['filter' => 'adminPermission:categories.manage', 'as' => 'admin.categories.edit']);
            $routes->put('(:num)', 'Admin\CategoryController::update/$1', ['filter' => 'adminPermission:categories.manage']);
            $routes->post('(:num)/status', 'Admin\CategoryController::status/$1', ['filter' => 'adminPermission:categories.manage']);
            $routes->post('(:num)/delete', 'Admin\CategoryController::delete/$1', ['filter' => 'adminPermission:categories.manage']);
        });
        $routes->group('products', ['filter' => 'adminPermission:products.view'], static function ($routes) {
            $routes->get('', 'Admin\ProductController::index', ['as' => 'admin.products']);
            $routes->get('create', 'Admin\ProductController::create', ['filter' => 'adminPermission:products.manage', 'as' => 'admin.products.create']);
            $routes->post('', 'Admin\ProductController::store', ['filter' => 'adminPermission:products.manage']);
            $routes->post('bulk', 'Admin\ProductController::bulk', ['filter' => 'adminPermission:products.manage']);
            $routes->get('(:num)', 'Admin\ProductController::show/$1', ['as' => 'admin.products.show']);
            $routes->get('(:num)/edit', 'Admin\ProductController::edit/$1', ['filter' => 'adminPermission:products.manage', 'as' => 'admin.products.edit']);
            $routes->put('(:num)', 'Admin\ProductController::update/$1', ['filter' => 'adminPermission:products.manage']);
            $routes->post('(:num)/delete', 'Admin\ProductController::delete/$1', ['filter' => 'adminPermission:products.manage']);
            $routes->post('(:num)/duplicate', 'Admin\ProductController::duplicate/$1', ['filter' => 'adminPermission:products.manage']);
            $routes->post('images/(:num)/delete', 'Admin\ProductController::imageDelete/$1', ['filter' => 'adminPermission:products.manage']);
            $routes->post('(:num)/images', 'Admin\ProductController::imagesUpdate/$1', ['filter' => 'adminPermission:products.manage']);
        });
        $routes->group('inventory', ['filter' => 'adminPermission:inventory.view'], static function ($routes) {
            $routes->get('', 'Admin\InventoryController::index', ['as' => 'admin.inventory']);
            $routes->get('(:segment)/(:num)/adjust', 'Admin\InventoryController::adjustForm/$1/$2', ['filter' => 'adminPermission:inventory.adjust', 'as' => 'admin.inventory.adjust']);
            $routes->post('(:segment)/(:num)/adjust', 'Admin\InventoryController::adjust/$1/$2', ['filter' => 'adminPermission:inventory.adjust']);
            $routes->get('(:segment)/(:num)/history', 'Admin\InventoryController::history/$1/$2', ['as' => 'admin.inventory.history']);
        });
        $routes->group('packages', ['filter' => 'adminPermission:packages.view'], static function ($routes) {
            $routes->get('', 'Admin\PackageController::index', ['as' => 'admin.packages']);
            $routes->get('create', 'Admin\PackageController::create', ['filter' => 'adminPermission:packages.manage', 'as' => 'admin.packages.create']);
            $routes->post('', 'Admin\PackageController::store', ['filter' => 'adminPermission:packages.manage']);
            $routes->get('(:num)/edit', 'Admin\PackageController::edit/$1', ['filter' => 'adminPermission:packages.manage', 'as' => 'admin.packages.edit']);
            $routes->put('(:num)', 'Admin\PackageController::update/$1', ['filter' => 'adminPermission:packages.manage']);
            $routes->post('(:num)/delete', 'Admin\PackageController::delete/$1', ['filter' => 'adminPermission:packages.manage']);
            $routes->post('(:num)/recalculate', 'Admin\PackageController::recalculate/$1', ['filter' => 'adminPermission:packages.manage']);
        });
        $routes->group('', ['filter' => 'adminRole:super_admin,admin'], static function ($routes) {
            $routes->get('homepage', 'Admin\HomepageController::index', ['as' => 'admin.homepage']);
            $routes->post('homepage', 'Admin\HomepageController::update');
            $routes->get('policies', 'Admin\PolicyController::index', ['as' => 'admin.policies']);
            $routes->post('policies', 'Admin\PolicyController::update');
            $routes->get('settings', 'Admin\SettingsController::index', ['as' => 'admin.settings']);
            $routes->post('settings', 'Admin\SettingsController::update');
        });
        $routes->group('admin-users', ['filter' => 'adminRole:super_admin'], static function ($routes) {
            $routes->get('', 'Admin\AdminUserController::index', ['as' => 'admin.users']);
            $routes->get('create', 'Admin\AdminUserController::create', ['as' => 'admin.users.create']);
            $routes->post('', 'Admin\AdminUserController::store');
            $routes->get('(:num)/edit', 'Admin\AdminUserController::edit/$1', ['as' => 'admin.users.edit']);
            $routes->put('(:num)', 'Admin\AdminUserController::update/$1');
            $routes->post('(:num)/delete', 'Admin\AdminUserController::delete/$1');
        });
    });
});
