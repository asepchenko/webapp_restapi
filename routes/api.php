<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

//dashboard
use App\Http\Controllers\API\DashboardController;

//notification
use App\Http\Controllers\API\NotificationsController;

//user
use App\Http\Controllers\API\UsersController;

//user customers
use App\Http\Controllers\API\UserCustomersController;
use App\Http\Controllers\API\AuthCustomerController;

//user agents
use App\Http\Controllers\API\UserAgentsController;
use App\Http\Controllers\API\AuthAgentController;

//role
use App\Http\Controllers\API\RolesController;

//departemen
use App\Http\Controllers\API\DepartemensController;

//transaction
use App\Http\Controllers\API\OrdersController;
use App\Http\Controllers\API\OrderAgentsController;
use App\Http\Controllers\API\OrderCostsController;
use App\Http\Controllers\API\OrderReferencesController;
use App\Http\Controllers\API\OrderUnitsController;
use App\Http\Controllers\API\OrderTrackingsController;
use App\Http\Controllers\API\OrderCostAgentsController;
use App\Http\Controllers\API\OrderAgentDestinationsController;

//master data
use App\Http\Controllers\API\DriversController;
use App\Http\Controllers\API\TruckTypesController;
use App\Http\Controllers\API\TrucksController;
use App\Http\Controllers\API\TruckingPricesController;
use App\Http\Controllers\API\ServiceGroupsController;
use App\Http\Controllers\API\ServicesController;
use App\Http\Controllers\API\LocationsController;
use App\Http\Controllers\API\AreasController;
use App\Http\Controllers\API\AreaCitiesController;
//use App\Http\Controllers\API\LocationRatesController;
use App\Http\Controllers\API\ProvincesController;
use App\Http\Controllers\API\CitiesController;
use App\Http\Controllers\API\PaymentTypesController;
use App\Http\Controllers\API\BanksController;
//use App\Http\Controllers\API\LimitTimesController;
use App\Http\Controllers\API\StatusOrdersController;
use App\Http\Controllers\API\StatusAwbsController;
//use App\Http\Controllers\API\CogsController;

//customer
use App\Http\Controllers\API\CustomersController;
use App\Http\Controllers\API\CustomerMousController;
use App\Http\Controllers\API\CustomerBranchsController;
use App\Http\Controllers\API\CustomerBrandsController;
use App\Http\Controllers\API\CustomerPicsController;
use App\Http\Controllers\API\CustomerMasterPricesController;
use App\Http\Controllers\API\CustomerTruckingPricesController;

//agent
use App\Http\Controllers\API\AgentsController;
use App\Http\Controllers\API\AgentPicsController;
use App\Http\Controllers\API\AgentMasterPricesController;
use App\Http\Controllers\API\AgentCitiesController;

//branch
use App\Http\Controllers\API\BranchsController;

//compro
use App\Http\Controllers\API\ComproServicesController;
use App\Http\Controllers\API\ComproGalleriesController;
use App\Http\Controllers\API\ComproBannersController;
use App\Http\Controllers\API\ComproPostsController;
use App\Http\Controllers\API\ComproContactsController;
use App\Http\Controllers\API\ComproMainBannersController;

//manifest
use App\Http\Controllers\API\ManifestsController;
use App\Http\Controllers\API\ManifestDetailsController;
use App\Http\Controllers\API\ManifestCogsController;

//invoices
use App\Http\Controllers\API\InvoicesController;
use App\Http\Controllers\API\InvoiceDetailsController;
use App\Http\Controllers\API\InvoiceApprovalsController;

//Bill
use App\Http\Controllers\API\BillsController;
use App\Http\Controllers\API\BillDetailsController;

//trip
use App\Http\Controllers\API\TripsController;
use App\Http\Controllers\API\TripDetailsController;
use App\Http\Controllers\API\TripCitiesController;

//document / stt balik
use App\Http\Controllers\API\DocumentsController;
use App\Http\Controllers\API\DocumentDetailsController;

//customer roles
use App\Http\Controllers\API\Customer\CustomerOrdersController;
use App\Http\Controllers\API\Customer\CustomerBranchsFEController;
use App\Http\Controllers\API\Customer\CustomerDashboardsController;
use App\Http\Controllers\API\Customer\CustomerInvoiceController;
use App\Http\Controllers\API\Customer\CustomerReportTransactionController;

//agent roles
use App\Http\Controllers\API\Agent\AgentOrdersController;
use App\Http\Controllers\API\Agent\AgentDashboardsController;
use App\Http\Controllers\API\Agent\AgentDocumentsController;
use App\Http\Controllers\API\Agent\AgentBillsController;

//frontend - no token required
use App\Http\Controllers\API\Frontend\FrontServicesController;
use App\Http\Controllers\API\Frontend\FrontBannersController;
use App\Http\Controllers\API\Frontend\FrontMainBannersController;
use App\Http\Controllers\API\Frontend\FrontGalleriesController;
use App\Http\Controllers\API\Frontend\FrontPostsController;
use App\Http\Controllers\API\Frontend\FrontContactsController;
use App\Http\Controllers\API\Frontend\FrontTrackingController;
use App\Http\Controllers\API\Frontend\FrontCheckPriceController;
use App\Http\Controllers\API\Frontend\FrontResetPasswordController;
use App\Http\Controllers\API\Frontend\FrontDataController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('login-customers',[AuthCustomerController::class, 'login'])->name('auth-customers.login');
Route::post('login-agents',[AuthAgentController::class, 'login'])->name('auth-agents.login');

//frontend
Route::get('front-check-prices/cities',[FrontCheckPriceController::class, 'listCity']);
Route::get('front-check-prices/services',[FrontCheckPriceController::class, 'listService']);
Route::get('front-check-prices/truck-types',[FrontCheckPriceController::class, 'listTruckTypes']);
Route::post('front-check-prices',[FrontCheckPriceController::class, 'checkPrice']);
Route::get('front-services',[FrontServicesController::class, 'index']);
Route::get('front-banners',[FrontBannersController::class, 'index']);
Route::get('front-main-banners',[FrontMainBannersController::class, 'index']);
Route::get('front-galleries',[FrontGalleriesController::class, 'index']);
Route::get('front-posts',[FrontPostsController::class, 'index']);
Route::get('front-posts/{slug}',[FrontPostsController::class, 'show']);
Route::get('front-latest-posts',[FrontPostsController::class, 'latestPost']);
Route::get('front-tracking/{no_awb}',[FrontTrackingController::class, 'tracking']);
Route::get('front-data',[FrontDataController::class, 'index']);
Route::post('front-contacts',[FrontContactsController::class, 'store']);
Route::post('front-reset-password',[FrontResetPasswordController::class, 'store']);

//route group admin
Route::group(['middleware' => ['auth:api','scopes:admin']], function () {

    //dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //notification
    Route::apiResource('notifications', NotificationsController::class);
    Route::get('notifications/list/LKE',[NotificationsController::class, 'indexByLKE'])->name('notifications.lke');

    //user
    Route::post('users/change-password',[UsersController::class, 'changePassword'])->name('users.changePassword');
    Route::post('users/change-profile',[UsersController::class, 'changeProfile'])->name('users.changeProfile');
    Route::apiResource('users', UsersController::class);

    //role
    Route::get('roles',[RolesController::class, 'index'])->name('roles');
    Route::get('roles/{id}',[RolesController::class, 'show'])->name('roles.show');

    //departemen
    Route::apiResource('departemens', DepartemensController::class);

    //transaction
    Route::get('orders/{start_date}/{end_date}', [OrdersController::class, 'indexByDate'])->name('orders.indexByDate');
    Route::apiResource('orders', OrdersController::class);
    Route::post('orders/closing',[OrdersController::class, 'closing'])->name('orders.closing');
    Route::get('orders-list',[OrdersController::class, 'list'])->name('orders.list');
    Route::get('orders-list/user',[OrdersController::class, 'listByUserID'])->name('orders.listByUserID');

    Route::get('order-trackings/order/{id}',[OrderTrackingsController::class, 'indexByOrderID'])->name('order-trackings.indexByOrderID');
    Route::apiResource('order-trackings', OrderTrackingsController::class);

    //Route::get('order-costs/order/{id}',[OrderCostsController::class, 'indexByOrderID'])->name('order-costs.indexByOrderID');
    Route::apiResource('order-costs', OrderCostsController::class);
    Route::post('order-references/import-excel',[OrderReferencesController::class, 'importExcel'])->name('order-references.importExcel');
    Route::get('order-references/order/{id}',[OrderReferencesController::class, 'indexByOrderID'])->name('order-references.indexByOrderID');
    Route::apiResource('order-references', OrderReferencesController::class);
    Route::get('order-agents/order/{id}',[OrderAgentsController::class, 'indexByOrderID'])->name('order-agents.indexByOrderID');
    Route::apiResource('order-agents', OrderAgentsController::class);
    Route::get('order-units/order/{id}',[OrderUnitsController::class, 'indexByOrderID'])->name('order-units.indexByOrderID');
    Route::apiResource('order-units', OrderUnitsController::class);
    Route::apiResource('order-cost-agents', OrderCostAgentsController::class);
    Route::apiResource('order-agent-destinations', OrderAgentDestinationsController::class);

    //master data
    Route::apiResource('drivers', DriversController::class);
    Route::apiResource('truck-types', TruckTypesController::class);

    Route::get('trucks/type/{type}', [TrucksController::class, 'indexByTypeID']);
    Route::apiResource('trucks', TrucksController::class);

    Route::get('trucking-prices/origin/{origin}/destination/{destination}/type/{type}',[TruckingPricesController::class, 'getTruckingPriceRates'])->name('trucking-prices.getRates');
    Route::apiResource('trucking-prices', TruckingPricesController::class);
    Route::apiResource('services', ServicesController::class);
    Route::apiResource('service-groups', ServiceGroupsController::class);
    Route::apiResource('locations', LocationsController::class);
    Route::apiResource('areas', AreasController::class);

    Route::get('area-cities/area/{id}',[AreaCitiesController::class, 'indexByAreaID']);
    Route::apiResource('area-cities', AreaCitiesController::class);
    //Route::apiResource('location-rates', LocationRatesController::class);
    Route::apiResource('provinces', ProvincesController::class);

    Route::get('cities/search/{name}',[CitiesController::class, 'indexByCityName']);
    Route::apiResource('cities', CitiesController::class);
    Route::apiResource('payment-types', PaymentTypesController::class);
    Route::apiResource('banks', BanksController::class);
    //Route::apiResource('limit-times', LimitTimesController::class);
    Route::apiResource('status-orders', StatusOrdersController::class);
    Route::apiResource('status-awbs', StatusAwbsController::class);
    //Route::apiResource('cogs', CogsController::class);

    //customer
    Route::apiResource('customers', CustomersController::class);

    Route::get('customer-mous/customer/{id}',[CustomerMousController::class, 'indexByCustomerID']);
    Route::apiResource('customer-mous', CustomerMousController::class);

    Route::get('customer-branchs/customer/{id}',[CustomerBranchsController::class, 'indexByCustomerID']);
    Route::apiResource('customer-branchs', CustomerBranchsController::class);
    
    Route::get('customer-brands/customer/{id}',[CustomerBrandsController::class, 'indexByCustomerID']);
    Route::apiResource('customer-brands', CustomerBrandsController::class);
    
    Route::get('customer-pics/customer/{id}',[CustomerPicsController::class, 'indexByCustomerID']);
    Route::apiResource('customer-pics', CustomerPicsController::class);
    
    Route::get('customer-master-prices/customer/{id}',[CustomerMasterPricesController::class, 'indexByCustomerID']);
    Route::get('customer-master-prices/customer/{customer}/origin/{origin}/destination/{destination}/service/{service}',[CustomerMasterPricesController::class, 'getMasterPriceRates'])->name('customer-master-prices.getRates');
    Route::get('customer-master-prices/pending',[CustomerMasterPricesController::class, 'indexPending'])->name('customer-master-prices.pending');
    Route::post('customer-master-prices/approve', [CustomerMasterPricesController::class, 'approve'])->name('customer-master-prices.approve');
    Route::post('customer-master-prices/mass-approve', [CustomerMasterPricesController::class, 'massApprove'])->name('customer-master-prices.massApprove');
    Route::apiResource('customer-master-prices', CustomerMasterPricesController::class);

    Route::get('customer-trucking-prices/customer/{id}',[CustomerTruckingPricesController::class, 'indexByCustomerID']);
    Route::get('customer-trucking-prices/customer/{customer}/origin/{origin}/destination/{destination}/truck/{truck}',[CustomerTruckingPricesController::class, 'getTruckingPriceRates'])->name('customer-trucking-prices.getRates');
    Route::get('customer-trucking-prices/pending',[CustomerTruckingPricesController::class, 'indexPending'])->name('customer-trucking-prices.pending');
    Route::post('customer-trucking-prices/approve', [CustomerTruckingPricesController::class, 'approve'])->name('customer-trucking-prices.approve');
    Route::post('customer-trucking-prices/mass-approve', [CustomerTruckingPricesController::class, 'massApprove'])->name('customer-trucking-prices.massApprove');
    Route::apiResource('customer-trucking-prices', CustomerTruckingPricesController::class);

    //agent
    Route::get('agents/city/{id}',[AgentsController::class, 'indexByCityID']);
    //ga kepake [?]
    Route::get('agents/city/{id}/address',[AgentsController::class, 'indexByCityAddressID']);

    Route::apiResource('agents', AgentsController::class);

    Route::get('agent-pics/agent/{id}',[AgentPicsController::class, 'indexByAgentID']);
    Route::apiResource('agent-pics', AgentPicsController::class);

    Route::get('agent-master-prices/agent/{id}',[AgentMasterPricesController::class, 'indexByAgentID']);
    Route::get('agent-master-prices/agent/{agent}/origin/{origin}/destination/{destination}/service/{service}',[AgentMasterPricesController::class, 'getMasterPriceRates'])->name('agent-master-prices.getRates');
    Route::apiResource('agent-master-prices', AgentMasterPricesController::class);

    Route::get('agent-cities/agent/{id}',[AgentCitiesController::class, 'indexByAgentID']);
    Route::apiResource('agent-cities', AgentCitiesController::class);

    //branch
    Route::get('branchs/city/{id}',[BranchsController::class, 'indexByCityID']);
    Route::apiResource('branchs', BranchsController::class);

    //compro
    Route::apiResource('compro-services', ComproServicesController::class);
    Route::apiResource('compro-galleries', ComproGalleriesController::class);
    Route::apiResource('compro-banners', ComproBannersController::class);
    Route::apiResource('compro-posts', ComproPostsController::class);
    Route::apiResource('compro-contacts', ComproContactsController::class);
    Route::apiResource('compro-main-banners', ComproMainBannersController::class);

    //manifest
    Route::apiResource('manifests', ManifestsController::class);
    Route::post('manifests/closing',[ManifestsController::class, 'closing'])->name('manifests.closing');
    Route::apiResource('manifest-details', ManifestDetailsController::class);
    Route::apiResource('manifest-cogs', ManifestCogsController::class);
    Route::get('manifests-cogs/trip/{no}',[ManifestCogsController::class, 'indexByTripNumber']);
    Route::get('manifests-list',[ManifestsController::class, 'list'])->name('manifests.list');
    Route::get('manifests-schedule',[ManifestsController::class, 'schedule'])->name('manifests.schedule');
    Route::get('manifests-stt-list/{manifest_number}',[ManifestsController::class, 'sttList'])->name('manifests.sttList');
    Route::get('manifests-stt/{manifest_number}/agent',[ManifestsController::class, 'manifestAgent'])->name('manifests.manifestAgent');
    Route::get('manifests-driver/{driver_id}',[ManifestsController::class, 'manifestByDriverID'])->name('manifests.manifestByDriverID');
    Route::get('manifests-driver/{manifest_number}/detail',[ManifestsController::class, 'manifestDriverDetail'])->name('manifests.manifestDriverDetail');
    Route::get('manifests-driver/{manifest_number}/agent',[ManifestsController::class, 'manifestDriverAgent'])->name('manifests.manifestDriverAgent');
    Route::post('manifests-driver',[ManifestsController::class, 'manifestUpdateTracking'])->name('manifests.manifestUpdateTracking');

    //invoice
    Route::get('invoices-order-list/{customer_id}',[InvoicesController::class, 'orderListByCustomerID'])->name('invoices.orderListByCustomerID');
    Route::apiResource('invoices', InvoicesController::class);
    Route::post('invoices/closing',[InvoicesController::class, 'closing'])->name('invoices.closing');
    Route::post('invoices/verify',[InvoicesController::class, 'verify'])->name('invoices.verify');
    Route::post('invoices/accept',[InvoicesController::class, 'accept'])->name('invoices.accept');
    Route::post('invoices/pay',[InvoicesController::class, 'payment'])->name('invoices.payment');
    Route::get('invoices-list',[InvoicesController::class, 'list'])->name('invoices.list');
    Route::get('invoices-order-list',[InvoicesController::class, 'orderList'])->name('invoices.orderList');
    Route::apiResource('invoice-details', InvoiceDetailsController::class);
    Route::apiResource('invoice-approvals', InvoiceApprovalsController::class);

    //bill
    Route::get('bills-order-list/{agent_id}',[BillsController::class, 'orderListByAgentID'])->name('bills.orderListByAgentID');
    Route::apiResource('bills', BillsController::class);
    Route::post('bills/pay',[BillsController::class, 'pay'])->name('bills.verify');
    Route::post('bills/closing',[BillsController::class, 'closing'])->name('bills.closing');
    Route::post('bills/store-by-admin',[BillsController::class, 'storeByAdmin'])->name('bills.storeByAdmin');
    Route::apiResource('bill-details', BillDetailsController::class);

    //trip
    Route::apiResource('trips', TripsController::class);
    Route::post('trips/closing',[TripsController::class, 'closing'])->name('trips.closing');
    Route::apiResource('trip-details', TripDetailsController::class);
    Route::apiResource('trip-cities', TripCitiesController::class);
    Route::get('trip-cities/trip/{no}',[TripCitiesController::class, 'indexByTripNumber']);

    //document / stt balik
    Route::apiResource('documents', DocumentsController::class);
    Route::post('documents/closing',[DocumentsController::class, 'closing'])->name('documents.closing');
    Route::apiResource('document-details', DocumentDetailsController::class);
});

//route group customers
Route::group(['middleware' => ['auth:api-customer','scopes:customer']], function () {
    Route::apiResource('user-customers', UserCustomersController::class);
    Route::get('user-customers/customer/list',[UserCustomersController::class, 'indexByCustID']);
    Route::post('user-customers/change-password',[UserCustomersController::class, 'changePassword']);
    Route::post('user-customers/change-profile',[UserCustomersController::class, 'changeProfile']);

    Route::get('customer-dashboards',[CustomerDashboardsController::class, 'index']);

    Route::get('customer-orders',[CustomerOrdersController::class, 'index']);
    Route::get('customer-orders/{order_number}',[CustomerOrdersController::class, 'show']);
    Route::get('customer-orders/{order_number}/track',[CustomerOrdersController::class, 'track']);
    Route::get('customer-orders-city',[CustomerOrdersController::class, 'getCity']);
    Route::get('customer-orders-service',[CustomerOrdersController::class, 'getService']);
    Route::get('customer-orders-service-group',[CustomerOrdersController::class, 'getServiceGroup']);
    Route::get('customer-orders-payment-type',[CustomerOrdersController::class, 'getPaymentType']);
    Route::get('customer-orders-truck',[CustomerOrdersController::class, 'getTruck']);
    Route::get('customer-orders/{id}/branch',[CustomerOrdersController::class, 'getCustBranch']);
    Route::get('customer-orders/{order_number}/ref',[CustomerOrdersController::class, 'getOrderRef']);
    Route::get('customer-orders/branch-cities/{id}',[CustomerOrdersController::class, 'getCustCityBranch']);
    Route::get('customer-orders/{start_date}/{end_date}', [CustomerOrdersController::class, 'getIndexByDate']);
    Route::get('customer-orders-limit-5',[CustomerOrdersController::class, 'getOrderLimit5']);

    Route::get('customer-orders/customer/{customer}/origin/{origin}/destination/{destination}/service/{service}',[CustomerOrdersController::class, 'getPrice']);
    Route::get('customer-orders/trucking/customer/{customer}/origin/{origin}/destination/{destination}/truck/{truck}',[CustomerOrdersController::class, 'getTruckingPrice']);

    Route::get('customer-branchs-fe',[CustomerBranchsFEController::class, 'index']);

    Route::get('customer-invoices',[CustomerInvoiceController::class, 'index']);
    Route::get('customer-invoices/{no}',[CustomerInvoiceController::class, 'detail']);
    Route::get('customer-invoices/{no}/detail',[CustomerInvoiceController::class, 'listDetail']);
    Route::post('customer-invoices',[CustomerInvoiceController::class, 'store']);
    Route::post('customer-invoices-accept',[CustomerInvoiceController::class, 'accept']);

    Route::post('customer-orders',[CustomerOrdersController::class, 'store']);

    Route::get('customer-orders/report/transaction/delivery-status/{month}/{year}',[CustomerReportTransactionController::class, 'getDeliveryStatus']);
});

//route group agents
Route::group(['middleware' => ['auth:api-agent','scopes:agent']], function () {
    Route::apiResource('user-agents', UserAgentsController::class);
    Route::get('user-agents/agent/list',[UserAgentsController::class, 'indexByAgentID']);
    Route::post('user-agents/change-password',[UserAgentsController::class, 'changePassword']);
    Route::post('user-agents/change-profile',[UserAgentsController::class, 'changeProfile']);

    Route::get('agent-dashboards',[AgentDashboardsController::class, 'index']);
    Route::get('agent-orders',[AgentOrdersController::class, 'index']);
    Route::get('agent-orders/{order_number}',[AgentOrdersController::class, 'show']);
    Route::get('agent-orders/{order_number}/track',[AgentOrdersController::class, 'track']);
    Route::get('agent-orders/{order_number}/is-delivered',[AgentOrdersController::class, 'isDelivered']);
    Route::get('agent-orders/{order_number}/list-agent',[AgentOrdersController::class, 'listAgent']);
    Route::get('agent-orders/{start_date}/{end_date}', [AgentOrdersController::class, 'getIndexByDate']);
    Route::get('agent-orders-limit-5',[AgentOrdersController::class, 'getOrderLimit5']);
    Route::post('agent-orders',[AgentOrdersController::class, 'store']);

    Route::get('agent-documents',[AgentDocumentsController::class, 'index']);
    Route::get('agent-documents/{id}',[AgentDocumentsController::class, 'show']);
    Route::post('agent-documents',[AgentDocumentsController::class, 'store']);
    Route::post('agent-documents/{id}',[AgentDocumentsController::class, 'update']);
    Route::post('agent-documents/{id}/closing',[AgentDocumentsController::class, 'closing']);

    Route::get('agent-document-details/{id}',[AgentDocumentsController::class, 'showDetail']);
    Route::get('agent-document-details/{id}/awb-list',[AgentDocumentsController::class, 'indexList']);
    Route::post('agent-document-details/{id}',[AgentDocumentsController::class, 'storeDetail']);
    Route::delete('agent-document-details/{id}',[AgentDocumentsController::class, 'deleteDetail']);

    Route::get('agent-bills',[AgentBillsController::class, 'index']);
    Route::get('agent-bills/list-data-order-delivered',[AgentBillsController::class, 'listOrderDelivered']);
    Route::get('agent-bills/{no}',[AgentBillsController::class, 'detail']);
    Route::post('agent-bills',[AgentBillsController::class, 'store']);
    Route::post('agent-bills/{id}',[AgentBillsController::class, 'update']);
    Route::post('agent-bills/{id}/closing',[AgentBillsController::class, 'closing']);
    Route::delete('agent-bills/{id}',[AgentBillsController::class, 'delete']);
});
