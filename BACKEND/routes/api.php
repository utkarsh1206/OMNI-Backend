<?php

use App\Enums\PermissionAction;
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
use App\Http\Controllers\Api\ActionController;
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
use App\Http\Controllers\Api\ApiConfigurationController;
use App\Http\Controllers\Api\SmsProviderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DynamicListFieldController;
use App\Http\Controllers\Api\EcdialListsController;
use App\Http\Controllers\Api\EcdialListFieldController;
use App\Http\Controllers\Api\ChatBotController;
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
use App\Http\Controllers\Api\LogoutRefreshApiController;
use App\Http\Controllers\Api\UserMessageController;
use App\Http\Controllers\Api\IncomingCallController;
use App\Http\Controllers\Api\ApiConfigurationTypeController;
use App\Http\Controllers\Api\CallLogReportController;
use App\Http\Controllers\Api\EvaluationCallReviewController;
use App\Http\Controllers\Api\EvaluationQuestionController;
use App\Http\Controllers\Api\UserActivityReportController;
use App\Http\Controllers\Api\UtilityController;
use App\Http\Controllers\Api\CallBackController;
use App\Http\Controllers\Api\AgentMessageController;
use App\Http\Controllers\Api\CustomCdrLeadController as ApiCustomCdrLeadController;
use App\Http\Controllers\Api\LiveCallDashboardController;
use App\Http\Controllers\Api\PriorityController;
use App\Http\Controllers\Api\ProductCustomFieldController;
use App\Http\Controllers\Api\TranscriptApiController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SuperAdminDashboardController;
use App\Http\Controllers\Api\SupportTicketStatusController;
use App\Http\Controllers\Api\TenantModuleController;
use App\Http\Controllers\Api\TicketTypeController;
use App\Http\Middleware\CheckPermissionMiddleware;

require base_path('routes/externalUse.php');

Route::post('login', [LoginController::class, 'login']);

Route::put('update-adminpassword', [UserController::class, 'updateAdminPassword']);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

#Route::middleware('auth:api')->apiResource('tenants', TenantController::class);

#Route::middleware('auth:api')->post('license_update', [LicenseUpdateController::class, 'updateLicense']);

#This APIs are not used into current. In future it will be used.
Route::get('callCDR', [CallCDRListController::class, 'getCallCDRList']);
Route::get('custome-field/header', [CustomFieldController::class, 'custumFieldHeader']);
Route::get('custome-field/value', [CustomFieldController::class, 'custumFieldvalue']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('/license_update', [LicenseUpdateController::class, 'updateLicense']);
});

Route::get('/decode-license', [LicenseController::class, 'decodeLicense']);
Route::get('/encode-license', [LicenseController::class, 'encodeLicense']);

Route::middleware(['jwt.auth'])->group(function () {

    Route::prefix('/utility')->group(function () {
        //      Route::get('export-database', [ExportController::class, 'export']);
        Route::get('export-database', [UtilityController::class, 'export']);
        Route::get('export-listing', [UtilityController::class, 'exportUtility']);
        Route::post('import-database', [UtilityController::class, 'import']);
        Route::get('import-listing', [UtilityController::class, 'importUtility']);
    });

    Route::put('/tenant-license/{id}', [TenantLicenseController::class, 'update']);

    Route::get('/tenant/{id}/license', [TenantLicenseController::class, 'getLicense']);

    //Route::apiResource('audit-logs', AuditController::class);
    $moduleId = getModuleId("audit_report");
    Route::get('audit-logs', [AuditController::class, 'index'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::post('audit-logs', [AuditController::class, 'store'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::CREATE->value);
    Route::get('audit-logs/{audit_log}', [AuditController::class, 'show'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::put('audit-logs/{audit_log}', [AuditController::class, 'update'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::patch('audit-logs/{audit_log}', [AuditController::class, 'update'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::delete('audit-logs/{audit_log}', [AuditController::class, 'destroy'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::DELETE->value);

    Route::post('save-user-provider', [SmsProviderController::class, 'saveUserProvider']);  // not in frontend yet

    Route::post('logout', [LoginController::class, 'logout']);

    Route::post('/logout-agent/{user}/{extension}', [LoginController::class, 'logoutAgent']);

    Route::apiResource('tenants', TenantController::class);

    Route::apiResource('usergroup', UserGroupController::class);

    Route::apiResource('tenantrole', TenantRoleController::class);

    Route::get('fetch-permissions', [TenantRoleController::class, 'fetchPermission']);

    // Route::get('cron-commands', [CronCommandController::class, 'index']);

    Route::apiResource('cron-configurations', CronConfigurationController::class);

    // Route::get('cron-expressions', [CronExpressionController::class, 'index']);

    Route::get('tenantrole-list', [TenantRoleController::class, 'list']);

    Route::get('tenantrole-menu', [TenantRoleController::class, 'menu']);   // not in frontend yet

    Route::get('tenant-dashboard', [TenantController::class, 'dashboard']); // not in frontend yet

    Route::get('check-tenant-creation', [TenantController::class, 'checkTenantCreationAllowed']);

    Route::apiResource('sms-providers', SmsProviderController::class);  // not in frontend yet

    Route::apiResource('api-configurations', ApiConfigurationController::class);

    //Route::apiResource('accounts', AccountController::class);
    $moduleId = getModuleId("accounts");
    Route::get('accounts', [AccountController::class, 'index'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);;
    Route::post('accounts', [AccountController::class, 'store'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::CREATE->value);
    Route::get('accounts/{account}', [AccountController::class, 'show'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::put('accounts/{account}', [AccountController::class, 'update'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::patch('accounts/{account}', [AccountController::class, 'update'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::delete('accounts/{account}', [AccountController::class, 'destroy'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::DELETE->value);

    Route::get('industry_type', [IndustryTypeController::class, 'index']);

    Route::get('business_type', [BusinessTypeController::class, 'index']);

    Route::get('countries', [CountryController::class, 'index']);

    Route::get('nameprefixes', [NamePrefixController::class, 'index']);     // not in frontend yet

    Route::get('occupations', [OccupationController::class, 'index']);

    Route::get('states', [StateController::class, 'index']);

    Route::get('cities', [CityController::class, 'index']);

    $moduleId = getModuleId("email_logs");
    
  //  Route::apiResource('email-logs', EmailLogController::class);
    Route::get('email-logs', [EmailLogController::class, 'index'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::post('email-logs', [EmailLogController::class, 'store'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::CREATE->value);
    Route::get('email-logs/{email_log}', [EmailLogController::class, 'show'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::put('email-logs/{email_log}', [EmailLogController::class, 'update'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::patch('email-logs/{email_log}', [EmailLogController::class, 'update'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::delete('email-logs/{email_log}', [EmailLogController::class, 'destroy'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::DELETE->value);
    Route::get('email_logs/csv', [EmailLogController::class, 'exportCsv'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::DOWNLOAD->value);

    Route::get('usersList', [UserListController::class, 'index']);

    Route::get('campaigns', [CampaignListController::class, 'index']);

    Route::get('ingroups', [IngroupListController::class, 'index']);

    Route::get('dashboard/ticket_details', [DashboardController::class, 'index']);

    Route::get('note', [NoteController::class, 'index']);

    Route::post('note', [NoteController::class, 'store']);

    Route::post('send-email', [EmailController::class, 'sendEmail']);

    Route::post('insert-update-custom', [UpdateCustomFieldController::class, 'index']);

    //Route::apiResource('contacts', ContactController::class);
    $moduleId = getModuleId("contacts");
    Route::get('contacts', [ContactController::class, 'index'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    // POST /contacts - Create a new contact
    Route::post('contacts', [ContactController::class, 'store'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::CREATE->value);
    // GET /contacts/{contact} - Show a specific contact
    Route::get('contacts/{contact}', [ContactController::class, 'show'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    // PUT /contacts/{contact} - Update a contact completely
    Route::put('contacts/{contact}', [ContactController::class, 'update'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    // PATCH /contacts/{contact} - Update a contact partially
    Route::patch('contacts/{contact}', [ContactController::class, 'update'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);

    Route::get('agent-activity', [AgentActivityController::class, 'getAgentActivity']);

    Route::get('mail-details', [MailDetailController::class, 'index']);

    Route::post('mail-details', [MailDetailController::class, 'store']);

    Route::apiResource('mail-templates', MailTemplateController::class);

    Route::apiResource('departments', DepartmentController::class);

    Route::apiResource('categories', CategoryController::class);

    Route::apiResource('email-configure', EmailConfigureController::class);

    Route::apiResource('products', ProductController::class);

    // Route::get('dashboard/ticket_details', [DashboardController::class, 'index']);
    // Route::apiResource('email-logs', EmailLogController::class);

    Route::apiResource('api-configuration-types', ApiConfigurationTypeController::class);
    // Route::apiResource('api-configurations', ApiConfigurationController::class);    

    Route::apiResource('sms-providers', SmsProviderController::class);

    Route::apiResource('lists', EcdialListsController::class);

    Route::get('email_logs/csv', [EmailLogController::class, 'exportCsv']);

    Route::apiResource('incident-types', IncidentTypeController::class);

    Route::apiResource('phones', PhoneController::class);

    Route::apiResource('users', UserController::class);

    Route::put('update-tenantpassword', [UserController::class, 'updatepassword']);

    Route::get('my-profile', [UserController::class, 'myprofile']);
    Route::put('my-profile/{user}', [UserController::class, 'profileUpdate']);

    Route::get('/branding', [UserController::class, 'getBranding']);

    Route::apiResource('live-agents', LiveAgentController::class);

    Route::apiResource('/dynamic-list-fields', DynamicListFieldController::class);

    Route::apiResource('/product-custom-fields', ProductCustomFieldController::class);

    Route::apiResource('/lists_fields', EcdialListFieldController::class);

    Route::apiResource('chatbots', ChatBotController::class);

    Route::get('/download-sample/{list_id}', [ImportLeadController::class, 'downloadSampleCsv']);

    Route::post('/upload-lead-csv/{list_id}', [ImportLeadController::class, 'uploadLeadCsv']);

    Route::get('/custom-leads/{list_id}', [CustomLeadController::class, 'index']);

    Route::get('/custom-leads/{list_id}/{lead_id}', [CustomLeadController::class, 'show']);

    Route::get('/custom-leads/{list_id}/mobile-history/{mobile}', [CustomLeadController::class, 'getLeadHistoryByMobile']);

    Route::post('/custom-leads/{list_id}', [CustomLeadController::class, 'create']);

    Route::put('/custom-leads/{list_id}/{lead_id}', [CustomLeadController::class, 'update']);

    Route::delete('custom-leads/{list_id}/{lead_id}', [CustomLeadController::class, 'destroy']);

    Route::get('/lead-by-call-id/{callUniqueId}', [ApiCustomCdrLeadController::class, 'getLeadByCallUniqueId']);

    // Support Ticket
    //Route::apiResource('support-ticket', SupportTicketController::class);
    $moduleId = getModuleId("support_tickets");
    Route::get('support-ticket', [SupportTicketController::class, 'index'])
        ->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::post('support-ticket', [SupportTicketController::class, 'store'])
        ->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::CREATE->value);
    Route::get('support-ticket/{support_ticket}', [SupportTicketController::class, 'show'])
         ->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::delete('support-ticket/{support_ticket}', [SupportTicketController::class, 'destroy'])
       ->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::DELETE->value);
    // Route::apiResource('support-ticket', SupportTicketController::class)->except(['update']);
    Route::post('support-ticket/{support_ticket}', [SupportTicketController::class, 'update'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::post('update-ticket-status/{supportticket}', [SupportTicketController::class, 'statusUpdate'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::post('transfer', [SupportTicketTransferController::class, 'store']);
    Route::post('merge', [SupportTicketMergeController::class, 'store']);
    Route::get('notification', [SupportTicketNotificationController::class, 'index']);

    // Ticket Report
    $moduleId = getModuleId("ticket_report");
    Route::get('ticketReport', [TicketReportController::class, 'ticketReport'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::get('ticketReport/csv', [TicketReportController::class, 'exportCsv'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::DOWNLOAD->value);

    Route::prefix('/ticket/summary')->group(function () {
        Route::get('ticket-type', [TicketReportController::class, 'ticketTypeSummary']);
        Route::get('priority', [TicketReportController::class, 'prioritySummary']);
        Route::get('status', [TicketReportController::class, 'statusSummary']);
        Route::get('weekday', [TicketReportController::class, 'weekdaySummary']);
    });

    // SLA Settings
    Route::apiResource('sla-settings', SlaSettingController::class);

    // SLA Report
    $moduleId = getModuleId("sla_report");
    Route::get('slareport', [SlaReportController::class, 'generateReport'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::get('slareport/csv', [SlaReportController::class, 'exportCsv'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::DOWNLOAD->value);
    Route::get('sla-priority', [SlaReportController::class, 'prioritySLA'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::get('sla-ticket-type', [SlaReportController::class, 'ticketTypeSLA'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::get('sla-incident', [SlaReportController::class, 'incidentSLA'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);

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

    // Route::post('call-initiate', [WebhookCallController::class, 'callInitiated']);
    // Route::post('call-connected', [WebhookCallController::class, 'callConnected']);
    // Route::post('call-hangup', [WebhookCallController::class, 'callHangup']);
    // Route::post('call-history', [WebhookCallController::class, 'callHistory']);

    Route::post('assign-chat-to-agent', [UserMessageController::class, 'assignChatToAgent']);
    Route::get('get-messages', [UserMessageController::class, 'getMessages']);
    Route::post('send-message', [UserMessageController::class, 'storeMessage']);

    $moduleId = getModuleId("agent_chat");
    Route::get('get-agent-status', [AgentMessageController::class, 'getStatus'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::get('get-agent-messages/{agent_id}', [AgentMessageController::class, 'getMessages'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    Route::post('send-agent-messages', [AgentMessageController::class, 'storeMessage'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);
    Route::post('last-read-message', [AgentMessageController::class, 'lastReadMessage'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::EDIT->value);

    Route::middleware([ModifyRequestMiddleware::class])->group(function () {
        Route::get('neox-call-incoming', [IncomingCallController::class, 'checkIncoming']);
    });

    Route::prefix('call-log-report')->group(function () {
        $moduleId = getModuleId("call_log_report");
        Route::get('list', [CallLogReportController::class, 'list'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
        Route::get('csv', [CallLogReportController::class, 'exportCsv'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::DOWNLOAD->value);
        Route::get('chart-summary', [CallLogReportController::class, 'getChartSummary'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
        Route::get('status-summary', [CallLogReportController::class, 'getStatusSummary'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
        Route::get('usergroup-summary', [CallLogReportController::class, 'getByUserGroup'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
        Route::get('user-summary', [CallLogReportController::class, 'getCallsByAgent'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    });

    Route::prefix('user-activity-report')->group(function () {
        $moduleId = getModuleId("user_activity_report");
        Route::get('list', [UserActivityReportController::class, 'list'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
        Route::get('user-activity', [UserActivityReportController::class, 'userActivity'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
        Route::get('csv', [UserActivityReportController::class, 'exportCsv'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::DOWNLOAD->value);
        Route::get('graph', [UserActivityReportController::class, 'graphData'])->middleware(CheckPermissionMiddleware::class.":$moduleId,".PermissionAction::VIEW->value);
    });

    Route::prefix('evaluation')->group(function () {
        Route::apiResource('questions', EvaluationQuestionController::class);
        Route::post('store-review', [EvaluationCallReviewController::class, 'store']);
        Route::post('get-review', [EvaluationCallReviewController::class, 'getReview']);
    });

    Route::apiResource('call-backs', CallBackController::class);
    Route::post('transcript', [TranscriptApiController::class, 'transcriptApi']);
    Route::apiResource('priorities', PriorityController::class);
    Route::apiResource('ticket-types', TicketTypeController::class);

    Route::get('super-admin-dashboard', [SuperAdminDashboardController::class, 'index']);

    Route::apiResource('ticket-status', SupportTicketStatusController::class);

    Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::post('/', [RoleController::class, 'store']);
    Route::get('{role}', [RoleController::class, 'show']);
    Route::put('{role}', [RoleController::class, 'update']);
    Route::delete('{role}', [RoleController::class, 'destroy']);
    });

    Route::post('/check-user-limit', [UserController::class, 'checkUserLimit']);
    Route::get('live-calls', [LiveCallDashboardController::class, 'index']);
    Route::post('tenant/change-status', [TenantController::class, 'changeStatus']);
    Route::post('user/change-status', [UserController::class, 'changeStatus']);
});

Route::get('countries', [CountryController::class, 'index']);
Route::get('states', [StateController::class, 'index']);
Route::get('cities', [CityController::class, 'index']);

Route::post('/ext-api/camp-list', [CampaignApiController::class, 'campaignListApi']);
Route::post('/ext-api/camp-list-all', [CampaignApiController::class, 'campaignListAllApi']);
Route::post('/ext-api/camp-list-filtered', [CampaignApiController::class, 'campaignListFilteredApi']);

Route::prefix('ext-api')->middleware([ModifyRequestMiddleware::class])->group(function () {
    Route::post('ingroup-list', [IngroupApiController::class, 'ingroupListApi']);
    Route::post('refresh-logout', [LogoutRefreshApiController::class, 'agentLogoutRefreshApi']);
});

Route::post('/ext-api/login', [LoginApiController::class, 'agentLoginApi']);

Route::post('call-initiate', [WebhookCallController::class, 'callInitiated']);  // -not to be used in frontend
Route::post('call-connected', [WebhookCallController::class, 'callConnected']); // -not to be used in frontend
Route::post('call-hangup', [WebhookCallController::class, 'callHangup']);   // -not to be used in frontend
Route::post('call-history', [WebhookCallController::class, 'callHistory']); // -not to be used in frontend

Route::apiResource('pause-code', 'App\Http\Controllers\Api\PauseCodeController');

Route::get('cron-commands', [CronCommandController::class, 'index']);
Route::get('cron-expressions', [CronExpressionController::class, 'index']);

Route::prefix('user-roles')->group(function () {
    Route::get('/',             [UserRoleController::class, 'index']);
    Route::put('{user}/roles',  [UserRoleController::class, 'update']);
});

Route::get('/tenant-modules', [TenantModuleController::class, 'index']);
Route::get('/actions', [ActionController::class, 'index']);

Route::post('/network-check', function () {
    return response()->noContent();
});