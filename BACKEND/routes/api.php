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
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserGroupController;
use App\Http\Controllers\Api\TenantRoleController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\EmailConfigureController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\MailDetailController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\SupportTicketNotificationController;
use App\Http\Controllers\Api\SupportTicketTransferController;
use App\Http\Controllers\Api\SupportTicketMergeController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\CallCDRListController;
use App\Http\Controllers\Api\IngroupListController;
use App\Http\Controllers\Api\CampaignListController;
use App\Http\Controllers\Api\UserListController;
use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\UpdateCustomFieldController;
use App\Http\Controllers\Api\AgentActivityController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AddPartyApiController;
use App\Http\Controllers\Api\IndustryTypeController;
use App\Http\Controllers\Api\BusinessTypeController;
use App\Http\Controllers\Api\CallTransferApiController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\LicenseUpdateController;
use App\Http\Controllers\Api\LicenseController;
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
use App\Http\Controllers\Api\SlaReportController;
use App\Http\Controllers\Api\EmailLogController;
use App\Http\Controllers\Api\SmsProviderConfigurationController;
use App\Http\Controllers\Api\SmsProviderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\EcdialListsController;
use App\Http\Controllers\Api\EcdialListFieldController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\ImportLeadController;
use App\Http\Controllers\Api\CustomLeadController;
use App\Http\Controllers\Api\CampaignApiController;
use App\Http\Controllers\Api\DialCallApiController;
use App\Http\Controllers\Api\DialNextCallApiController;
use App\Http\Controllers\Api\DisconnectConfApiController;
use App\Http\Controllers\Api\DisposCallApiController;
use App\Http\Controllers\Api\NeoxConfigurationController;
use App\Http\Controllers\Api\IngroupApiController;
use App\Http\Controllers\Api\DispositionApiController;
use App\Http\Controllers\Api\HangupApiController;
use App\Http\Controllers\Api\HoldCallApiController;
use App\Http\Controllers\Api\LeaveConfApiController;
use App\Http\Controllers\Api\LoginApiController;
use App\Http\Controllers\Api\LogoutApiController;
use App\Http\Controllers\Api\PauseResumeApiController;
use App\Http\Controllers\Api\RecordApiController;
use App\Http\Controllers\Api\RemovePartyApiController;
use App\Http\Controllers\Api\StartConfApiController;
use App\Http\Controllers\Api\LiveAgentController;
use App\Http\Controllers\Api\WebhookCallController;
use App\Http\Middleware\ModifyRequestMiddleware;
use App\Http\Controllers\Api\ChatBotConfigurationController;

Route::post('login', [LoginController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

#Route::middleware('auth:api')->apiResource('tenants', TenantController::class);
#Route::middleware('auth:api')->post('license_update', [LicenseUpdateController::class, 'updateLicense']);

#This APIs are not used into current. In future it will be used.
Route::get('callCDR', [CallCDRListController::class, 'getCallCDRList']);
Route::get('custome-field/header', [CustomFieldController::class, 'custumFieldHeader']);
Route::get('custome-field/value', [CustomFieldController::class, 'custumFieldvalue']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('license_update', [LicenseUpdateController::class, 'updateLicense']);
    Route::get('/decode-license', [LicenseController::class, 'decodeLicense']);
    Route::get('/encode-license', [LicenseController::class, 'encodeLicense']);
});

Route::middleware(['jwt.auth'])->group(function () {
    Route::put('/tenant-license/{id}', [TenantLicenseController::class, 'update']);
    Route::get('/tenant/{id}/license', [TenantLicenseController::class, 'getLicense']);
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
    // Route::get('dashboard/ticket_details', [DashboardController::class, 'index']);
    Route::apiResource('email-logs', EmailLogController::class);
    Route::apiResource('sms-configurations', SmsProviderConfigurationController::class);
    Route::apiResource('sms-providers', SmsProviderController::class);
    Route::apiResource('lists', EcdialListsController::class);
    Route::get('email_logs/csv', [EmailLogController::class, 'exportCsv']);
    Route::apiResource('incident-types', IncidentTypeController::class);
    Route::apiResource('phones', PhoneController::class);
    Route::apiResource('users', UserController::class);
    Route::put('update-tenantpassword', [UserController::class, 'updatepassword']);
    Route::get('my-profile', [UserController::class, 'myprofile']);
    Route::get('/branding', [UserController::class, 'getBranding']);
    Route::apiResource('live-agents', LiveAgentController::class);
    Route::apiResource('/lists_fields', EcdialListFieldController::class);
    Route::apiResource('chatbots', ChatbotController::class);
    Route::get('/download-sample/{list_id}', [ImportLeadController::class, 'downloadSampleCsv']);
    Route::post('/upload-lead-csv/{list_id}', [ImportLeadController::class, 'uploadLeadCsv']);
    Route::get('/custom-leads/{list_id}', [CustomLeadController::class, 'index']);  // Get all leads for a list
    Route::get('/custom-leads/{list_id}/{lead_id}', [CustomLeadController::class, 'show']); // Get a single lead by ID
    Route::put('/custom-leads/{list_id}/{lead_id}', [CustomLeadController::class, 'update']);
    Route::delete('custom-leads/{list_id}/{lead_id}', [CustomLeadController::class, 'destroy']);

    // Support Ticket
    Route::apiResource('supportticket', SupportTicketController::class);
    Route::post('updateticketstatus/{supportticket}', [SupportTicketController::class, 'statusUpdate']);
    Route::post('transfer', [SupportTicketTransferController::class, 'store']);
    Route::post('merge', [SupportTicketMergeController::class, 'store']);
    Route::get('notification', [SupportTicketNotificationController::class, 'index']);

    // Ticket Report
    Route::get('ticketReport', [TicketReportController::class, 'ticketReport']);
    Route::get('ticketReport/csv', [TicketReportController::class, 'exportCsv']);
    Route::get('/ticket/summary/category', [TicketReportController::class, 'categorySummary']);
    Route::get('/ticket/summary/priority', [TicketReportController::class, 'prioritySummary']);
    Route::get('/ticket/summary/status', [TicketReportController::class, 'statusSummary']);
    Route::get('/ticket/summary/weekday', [TicketReportController::class, 'weekdaySummary']);

    // SLA Settings
    Route::apiResource('sla-settings', SlaSettingController::class);

    // SLA Report
    Route::get('slareport', [SlaReportController::class, 'generateReport']);
    Route::get('slareport/csv', [SlaReportController::class, 'exportCsv']);
    Route::get('sla-priority', [SlaReportController::class, 'prioritySLA']);
    Route::get('sla-category', [SlaReportController::class, 'categorySLA']);
    Route::get('sla-incident', [SlaReportController::class, 'incidentSLA']);

    Route::apiResource('neox-configurations', NeoxConfigurationController::class);
    Route::prefix('ext-api')->middleware([ModifyRequestMiddleware::class])->group(function () {
        Route::post('dispo-list', [DispositionApiController::class, 'dispositionListApi']);
        Route::post('hold-call', [HoldCallApiController::class, 'holdCallApi']);
        Route::post('logout', [LogoutApiController::class, 'agentLogoutApi']);
        Route::post('dial-call', [DialCallApiController::class, 'dialCallApi']);
        Route::post('pause-resume', [PauseResumeApiController::class, 'pauseResumeApi']);
        Route::post('hangup', [HangupApiController::class, 'hangupApi']);
        Route::post('dispos-call', [DisposCallApiController::class, 'disposCallApi']);
        Route::post('dial-next', [DialNextCallApiController::class, 'dialNextCallApi']);
        Route::post('add-party', [AddPartyApiController::class, 'addPartyApi']);
        Route::post('call-tansfer', [CallTransferApiController::class, 'callTransferApi']);
        Route::post('disconnect-conf', [DisconnectConfApiController::class, 'disconnectConfApi']);
        Route::post('leave-conf', [LeaveConfApiController::class, 'leaveConfApi']);
        Route::post('record-call', [RecordApiController::class, 'callRecordingApi']);
        Route::post('remove-party', [RemovePartyApiController::class, 'removePartyApi']);
        Route::post('start-conf', [StartConfApiController::class, 'startConfApi']);
    });
    Route::post('call-initiate', [WebhookCallController::class, 'callInitiated']);
    Route::post('call-connected', [WebhookCallController::class, 'callConnected']);
    Route::post('call-hangup', [WebhookCallController::class, 'callHangup']);
    Route::post('call-history', [WebhookCallController::class, 'callHistory']);
    Route::apiResource('chat-bot-configurations', ChatBotConfigurationController::class);
});

Route::post('/ext-api/camp-list', [CampaignApiController::class, 'campaignListApi']);
Route::middleware([ModifyRequestMiddleware::class])->group(function () {
    Route::post('/ext-api/ingroup-list', [IngroupApiController::class, 'ingroupListApi']);
});
Route::post('/ext-api/login', [LoginApiController::class, 'agentLoginApi']);
