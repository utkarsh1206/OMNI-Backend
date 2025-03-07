<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\OccupationController;
use App\Http\Controllers\Api\NamePrefixController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\UserGroupController;
use App\Http\Controllers\Api\TenantRoleController;
use App\Http\Controllers\Api\SupportticketController;
use App\Http\Controllers\Api\EmailConfigureController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\MailDetailController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\SupportTicketNotificationController;
use App\Http\Controllers\Api\SupporttickettransferController;
use App\Http\Controllers\Api\SupportticketmergeController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\CallCDRListController;
use App\Http\Controllers\Api\IngroupListController;
use App\Http\Controllers\Api\CampaignListController;
use App\Http\Controllers\Api\UserListController;
use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\UpdateCustomFieldController;
use App\Http\Controllers\Api\AgentActivityController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\IndustryTypeController;
use App\Http\Controllers\Api\BusinessTypeController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\LicenseUpdateController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\TenantLicenseUpdateController;
use App\Http\Controllers\Api\TenantLicenseController;
use App\Http\Controllers\Api\PhoneController;
use App\Http\Controllers\Api\MailTemplateController;
use App\Http\Controllers\Api\SlaSettingController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\IncidentTypeController;
use App\Http\Controllers\Api\CronCommandController;
use App\Http\Controllers\Api\CronExpressionController;
use App\Http\Controllers\Api\CronConfigurationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TicketReportController;
use App\Http\Controllers\Api\SLAReportController;
use App\Http\Controllers\Api\EmailLogController;
use App\Http\Controllers\Api\SmsProviderConfigurationController;
use App\Http\Controllers\Api\SmsProviderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\EcdialListsController;
use App\Http\Controllers\Api\EcdialListFieldController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ImportLeadController;
use App\Http\Controllers\Api\CustomLeadController;
use App\Http\Controllers\Api\LiveAgentController;

Route::post('login', [LoginController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



//Route::get('login', [AuthController::class, 'login'])->name('login');

#Route::middleware('auth:api')->apiResource('tenants', TenantController::class);
Route::apiResource('sla-settings', SlaSettingController::class);
Route::apiResource('users', UsersController::class);
Route::put('update-tenantpassword', [UsersController::class, 'updatepassword']);
Route::get('my-profile', [UsersController::class, 'myprofile']);
Route::post('transfer', [SupporttickettransferController::class, 'store']);
Route::post('merge', [SupportticketmergeController::class, 'store']);

Route::get('callCDR', [CallCDRListController::class, 'getCallCDRList']);
Route::get('custome-field/header', [CustomFieldController::class, 'custumFieldHeader']);
Route::get('custome-field/value', [CustomFieldController::class, 'custumFieldvalue']);
Route::middleware('auth:api')->post('license_update', [LicenseUpdateController::class, 'updateLicense']);
Route::get('/decode-license', [LicenseController::class, 'decodeLicense']);
Route::get('/encode-license', [LicenseController::class, 'encodeLicense']);

Route::put('/tenant-license/{id}', [TenantLicenseController::class, 'update']);
Route::get('/tenant/{id}/license', [TenantLicenseController::class, 'getLicense']);

Route::get('ticketReport', [TicketReportController::class, 'ticketReport']);
Route::get('ticketReport/csv', [TicketReportController::class, 'exportCsv']);
Route::get('/ticket/summary/category', [TicketReportController::class, 'categorySummary']);
Route::get('/ticket/summary/priority', [TicketReportController::class, 'prioritySummary']);
Route::get('/ticket/summary/status', [TicketReportController::class, 'statusSummary']);
Route::get('/ticket/summary/weekday', [TicketReportController::class, 'weekdaySummary']);
Route::get('slareport', [SLAReportController::class, 'generateReport']);
Route::get('slareport/csv', [SLAReportController::class, 'exportCsv']);
Route::get('sla-priority', [SLAReportController::class, 'prioritySLA']);
Route::get('sla-category', [SLAReportController::class, 'categorySLA']);
Route::get('sla-incident', [SLAReportController::class, 'incidentSLA']);


Route::middleware(['jwt.auth'])->group(function () {
    Route::apiResource('supportticket', SupportticketController::class);
    Route::post('updateticketstatus/{supportticket}', [SupportticketController::class, 'statusupdate']);
    Route::apiResource('audit-logs', AuditController::class);
    Route::post('save-user-provider', [SmsProviderController::class, 'saveUserProvider']);
    Route::post('logout', [LoginController::class, 'logout']);
    Route::apiResource('tenants', TenantController::class);
    Route::apiResource('usergroup', UserGroupController::class);
    Route::apiResource('tenantrole', TenantRoleController::class);
    Route::get('cron-commands', [CronCommandController::class, 'index']);
    Route::apiResource('cron-configurations', CronConfigurationController::class);
    Route::get('cron-expressions', [CronExpressionController::class, 'index']);
    Route::get('tenantrole-list', [TenantRoleController::class, 'list']);
    Route::get('tenantrole-menu', [TenantRoleController::class, 'menu']);
    Route::get('tenant-dashboard', [TenantController::class, 'dashboard']);
    Route::apiResource('sms-providers', SmsProviderController::class);
    Route::apiResource('sms-configurations', SmsProviderConfigurationController::class);
    Route::get('notification', [SupportTicketNotificationController::class, 'index']);
    Route::apiResource('accounts', AccountController::class);
    Route::get('industry_type', [IndustryTypeController::class, 'index']);
    Route::get('business_type', [BusinessTypeController::class, 'index']);
    Route::get('countries', [CountryController::class, 'index']);
    Route::get('nameprefixes', [NamePrefixController::class, 'index']);
    Route::get('occupations', [OccupationController::class, 'index']);
    Route::get('states', [StateController::class, 'index']);    
    Route::get('cities', [CityController::class, 'index']);
    Route::apiResource('email-logs', EmailLogController::class);
    Route::get('email_logs/csv', [EmailLogController::class, 'exportCsv']);
    Route::get('usersList', [UserListController::class, 'index']);
    Route::get('campaigns', [CampaignListController::class, 'index']);
    Route::get('ingroups', [IngroupListController::class, 'index']);
    Route::get('dashboard/ticket_details', [DashboardController::class, 'index']);
    Route::get('note', [NoteController::class, 'index']);
    Route::post('note', [NoteController::class, 'store']);
    Route::post('send-email', [EmailController::class, 'sendEmail']);
    Route::post('insert-update-custom', [UpdateCustomFieldController::class, 'index']);
    Route::apiResource('contacts', ContactController::class);
    Route::get('agent-activity', [AgentActivityController::class, 'getAgentActivity']);
    Route::get('mail-details', [MailDetailController::class, 'index']);
    Route::post('mail-details', [MailDetailController::class, 'store']);
    Route::apiResource('mail-templates', MailTemplateController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('email-configure', EmailConfigureController::class);
    Route::apiResource('products', ProductController::class);
    Route::get('dashboard/ticket_details', [DashboardController::class, 'index']);
    Route::apiResource('email-logs', EmailLogController::class);
    Route::apiResource('sms-configurations', SmsProviderConfigurationController::class);
    Route::apiResource('sms-providers', SmsProviderController::class);
    Route::apiResource('lists', EcdialListsController::class);
    Route::get('email_logs/csv', [EmailLogController::class, 'exportCsv']);
    Route::apiResource('incident-types', IncidentTypeController::class);
    Route::apiResource('phones', PhoneController::class);
});



Route::apiResource('live-agents', LiveAgentController::class);

Route::get('/branding', [UsersController::class, 'getBranding']);

Route::apiResource('/lists_fields', EcdialListFieldController::class);

Route::apiResource('chatbots', ChatbotController::class);

Route::get('/download-sample/{list_id}', [ImportLeadController::class, 'downloadSampleCsv']);
Route::post('/upload-lead-csv/{list_id}', [ImportLeadController::class, 'uploadLeadCsv']);

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/custom-leads/{list_id}', [CustomLeadController::class, 'index']);  // Get all leads for a list
    Route::get('/custom-leads/{list_id}/{lead_id}', [CustomLeadController::class, 'show']); // Get a single lead by ID
    Route::put('/custom-leads/{list_id}/{lead_id}', [CustomLeadController::class, 'update']);
    Route::delete('custom-leads/{list_id}/{lead_id}', [CustomLeadController::class, 'destroy']);
});
