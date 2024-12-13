<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\OccupationsController;
use App\Http\Controllers\Api\NameprefixesController;
use App\Http\Controllers\Api\StatesController;
use App\Http\Controllers\Api\CitiesController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\UsergroupController;
use App\Http\Controllers\Api\TenantroleController;
use App\Http\Controllers\Api\SupportticketController;
use App\Http\Controllers\Api\EmailConfigureController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\MailDetailController;
use App\Http\Controllers\Api\NotesController;
use App\Http\Controllers\Api\SupportticketnotificationController;
use App\Http\Controllers\Api\SupporttickettransferController;
use App\Http\Controllers\Api\SupportticketmergeController;

use App\Models\EmailConfigure;
use App\Models\Supportticket;
use App\Models\Tenantrole;
use App\Http\Controllers\Api\CallCDRListController;
use App\Http\Controllers\Api\IngroupListController;
use App\Http\Controllers\Api\CampaignListController;
use App\Http\Controllers\Api\UsersListController;
use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\UpdateCustomFieldsController;
use App\Http\Controllers\Api\AgentActivityController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\IndustryTypeController;
use App\Http\Controllers\Api\BusinessTypeController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\LicenseUpdateController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\TenantLicenseUpdateController;
use App\Http\Controllers\Api\TenantLicenseController;

Route::post('login', [LoginController::class, 'login_action']);

Route::get('get_test', [TestController::class, 'index']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});
//Route::get('login', [AuthController::class, 'login'])->name('login');
Route::get('countries', [CountriesController::class, 'index']);
Route::get('nameprefixes', [NameprefixesController::class, 'index']);
Route::get('occupations', [OccupationsController::class, 'index']);
Route::get('states', [StatesController::class, 'index']);
Route::get('cities', [CitiesController::class, 'index']);
#Route::middleware('auth:api')->apiResource('tenants', TenantController::class);
Route::apiResource('tenants', TenantController::class);

Route::apiResource('users', UsersController::class);
Route::apiResource('usergroup', UsergroupController::class);
Route::apiResource('tenantrole', TenantroleController::class);
Route::apiResource('supportticket', SupportticketController::class);
Route::apiResource('email-configure', EmailConfigureController::class);
Route::get('tenantrole-list', [TenantroleController::class, 'list']);
Route::get('tenantrole-menu', [TenantroleController::class, 'menu']);
Route::get('tenant-dashboard', [TenantController::class, 'dashboard']);
Route::put('update-tenantpassword', [UsersController::class, 'updatepassword']);
Route::get('my-profile', [UsersController::class, 'myprofile']);
Route::post('send-email', [EmailController::class, 'sendEmail']);
Route::get('mail-details', [MailDetailController::class, 'index']);  
Route::post('mail-details', [MailDetailController::class, 'store']); 
Route::get('note', [NotesController::class, 'index']);  
Route::post('note', [NotesController::class, 'store']); 
Route::get('notification', [SupportticketnotificationController::class, 'index']);  
Route::post('transfer', [SupporttickettransferController::class, 'store']);  
Route::post('merge', [SupportticketmergeController::class, 'store']);  

Route::get('callCDR', [CallCDRListController::class, 'getCallCDRList']);
Route::get('custome-field/header', [CustomFieldController::class, 'custumFieldHeader']);
Route::get('custome-field/value', [CustomFieldController::class, 'custumFieldvalue']);
Route::post('insert-update-custom', [UpdateCustomFieldsController::class, 'updateInsertCustomField']);

Route::get('agent-activity', [AgentActivityController::class, 'getAgentActivity']);

Route::get('ingroups', [IngroupListController::class, 'index']);
Route::get('campaigns', [CampaignListController::class, 'index']);
Route::get('usersList', [UsersListController::class, 'index']);

Route::apiResource('accounts', AccountController::class);
Route::get('industry_type', [IndustryTypeController::class, 'index']);
Route::get('business_type', [BusinessTypeController::class, 'index']);

Route::apiResource('contacts', ContactController::class);

Route::middleware('auth:api')->post('license_update', [LicenseUpdateController::class, 'updateLicense']);
Route::get('/decode-license', [LicenseController::class, 'decodeLicense']);
Route::get('/encode-license', [LicenseController::class, 'encodeLicense']);

// Route::put('/tenants/{id}/license', [TenantLicenseUpdateController::class, 'updateLicense']);
Route::put('/tenant-license/{id}', [TenantLicenseController::class, 'update']);
Route::get('/tenant/{id}/license', [TenantLicenseController::class, 'getLicense']);

// Route::prefix('license')->group(function () {
//     // Route to encode (generate) a license key
//     Route::post('encode', [LicenseController::class, 'encodeLicense']);
    
//     // Route to decode an existing license key
//     Route::post('decode', [LicenseController::class, 'decodeLicense']);
// });


#Route::middleware('auth:api')->apiResource('users', UsersController::class);

Route::middleware(['auth:api'])->group(function () {
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
});
