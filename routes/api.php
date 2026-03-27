<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FollowUpController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\SiteVisitController;
use App\Http\Controllers\Api\TelecallerController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BuilderController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectCollateralController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\UnitTypeController;
use App\Http\Controllers\Api\ProjectDetailController;
use App\Http\Controllers\Api\Crm\AuthController as CrmAuthController;
use App\Http\Controllers\Api\Crm\DashboardController as CrmDashboardController;
use App\Http\Controllers\Api\Crm\LeadController as CrmLeadController;
use App\Http\Controllers\Api\Crm\UserController as CrmUserController;
use App\Http\Controllers\Api\Crm\TransferController as CrmTransferController;
use App\Http\Controllers\Api\Crm\BlacklistController as CrmBlacklistController;
use App\Http\Controllers\Api\Crm\TargetController as CrmTargetController;
use App\Http\Controllers\Api\Crm\VerificationController as CrmVerificationController;
use App\Http\Controllers\Api\TargetController;
use App\Http\Controllers\Api\InterestedProjectNameController;
use App\Http\Controllers\Api\PabblyWebhookController;
use App\Http\Controllers\Api\IncentiveController;
use App\Http\Controllers\Api\LeadsPendingResponseController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/firebase', [\App\Http\Controllers\Api\FirebaseAuthController::class, 'login']);

// Webhook endpoints (public - rate limited to 60 requests/min per IP)
Route::middleware('throttle:60,1')->group(function () {
    // Pabbly Webhook
    Route::post('/pabbly/webhook', [PabblyWebhookController::class, 'store']);

    // Facebook Lead Ads Webhook (Meta verification + receive)
    Route::get('/webhooks/facebook/leads', [\App\Http\Controllers\Api\FacebookWebhookController::class, 'verify']);
    Route::post('/webhooks/facebook/leads', [\App\Http\Controllers\Api\FacebookWebhookController::class, 'receive']);

    // MCube Call Tracking Webhook (token validated inside controller)
    Route::post('/webhooks/mcube', [\App\Http\Controllers\Api\McubeWebhookController::class, 'receive']);

    // Google Sheets Lead API (for Google Apps Script)
    Route::post('/google-sheets/leads', [\App\Http\Controllers\Api\GoogleSheetsLeadController::class, 'store']);
});

// Telecaller public routes
Route::post('/telecaller/login', [TelecallerController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Notifications (for all authenticated roles - used by chatbot/notification bell)
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/notifications/unread', [\App\Http\Controllers\Api\NotificationController::class, 'getUnread']);
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/{notification}/click', [\App\Http\Controllers\Api\NotificationController::class, 'markAsClicked']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);

    // PWA Web Push subscription (for lead-assigned etc. notifications)
    Route::post('/push-subscription', [\App\Http\Controllers\Api\PushSubscriptionController::class, 'store']);
    Route::delete('/push-subscription', [\App\Http\Controllers\Api\PushSubscriptionController::class, 'destroy']);

    // FCM Token subscription (Firebase Cloud Messaging)
    Route::post('/fcm-subscription', [\App\Http\Controllers\Api\FcmTokenController::class, 'store']);
    Route::delete('/fcm-subscription', [\App\Http\Controllers\Api\FcmTokenController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Targets
    Route::get('/targets/my-targets', [TargetController::class, 'myTargets']);
    Route::get('/targets/team-progress', [TargetController::class, 'teamProgress'])->middleware('role:sales_manager');
    Route::get('/targets/overview', [TargetController::class, 'overview'])->middleware('role:admin,crm');

    // Leads
    Route::apiResource('leads', LeadController::class)->names(['index' => 'api.leads.index', 'store' => 'api.leads.store', 'show' => 'api.leads.show', 'update' => 'api.leads.update', 'destroy' => 'api.leads.destroy']);
    Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssign']);
    Route::post('/leads/transfer-all-from-user', [LeadController::class, 'transferAllFromUser']);
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign']);
    Route::get('/leads/{lead}/requirement-form', [\App\Http\Controllers\Api\SalesManagerController::class, 'getLeadRequirementForm']);
    Route::post('/leads/{lead}/update-requirements', [\App\Http\Controllers\Api\SalesManagerController::class, 'updateLeadRequirements']);

    // Site Visits
    Route::apiResource('site-visits', SiteVisitController::class)->names('api.site-visits');

    // Follow-ups
    Route::apiResource('follow-ups', FollowUpController::class)->names('api.follow-ups');

    // Interested Project Names
    Route::get('/interested-project-names', [InterestedProjectNameController::class, 'index']);

    // Telecallers list
    Route::get('/telecallers', [TelecallerController::class, 'getTelecallers']);

    // Users (Admin only)
    Route::apiResource('users', UserController::class)->middleware('permission:manage_users')->names([
        'index' => 'api.users.index',
        'show' => 'api.users.show',
        'store' => 'api.users.store',
        'update' => 'api.users.update',
        'destroy' => 'api.users.destroy',
    ]);

    // Admin Impersonation
    Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate'])->middleware('permission:manage_users');
    Route::post('/impersonate/stop', [UserController::class, 'stopImpersonation'])->middleware('permission:manage_users');

    // Telecaller / Sales Executive routes (both roles use same API)
    Route::prefix('telecaller')->middleware('role:sales_executive,admin,crm,sales_manager,senior_manager,assistant_sales_manager,sales_head')->group(function () {
        // Auth routes
        Route::get('/whoami', [TelecallerController::class, 'whoami']);
        Route::post('/logout', [TelecallerController::class, 'logout']);
        
        // Dashboard & Stats
        Route::get('/stats', [TelecallerController::class, 'getStats']);
        Route::get('/top-performers', [TelecallerController::class, 'getTopPerformers']);
        
        // Dashboard API endpoints
        Route::get('/dashboard', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'index']);
        Route::get('/dashboard/stats', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'stats']);
        Route::get('/dashboard/urgent-tasks', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'urgentTasks']);
        Route::get('/dashboard/schedule', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'schedule']);
        Route::get('/dashboard/performance', [\App\Http\Controllers\Api\TelecallerDashboardController::class, 'performance']);
        Route::get('/leads-pending-response', [LeadsPendingResponseController::class, 'forCurrentUser']);
        
        // Leads & Calls
        Route::get('/leads', [TelecallerController::class, 'getLeads']);
        Route::get('/calling-queue', [TelecallerController::class, 'getCallingQueue']);
        Route::get('/completed-calls', [TelecallerController::class, 'getCompletedCalls']);
        Route::get('/follow-up-calls', [TelecallerController::class, 'getFollowUpCalls']);
        Route::get('/cnp-calls', [TelecallerController::class, 'getCnpCalls']);
        Route::get('/prospects', [TelecallerController::class, 'getProspects']);
        Route::get('/prospects/list', [TelecallerController::class, 'getProspects']); // Alias for verification pending
        
        // Tasks
        Route::get('/tasks', [TelecallerController::class, 'getTasks']);
        Route::get('/tasks/stats', [TelecallerController::class, 'getTaskStats']);
        Route::post('/tasks/schedule-call', [\App\Http\Controllers\Api\SalesManagerController::class, 'scheduleCallTask']);
        Route::post('/tasks/{task}/initiate-call', [TelecallerController::class, 'initiateCall']);
        Route::post('/tasks/{task}/call-outcome', [TelecallerController::class, 'callOutcome']);
        Route::post('/tasks/{taskId}/outcome', [TelecallerController::class, 'recordOutcome']);
        Route::get('/tasks/{task}/lead-form', [TelecallerController::class, 'getLeadFormForModal']);
        Route::post('/tasks/{taskId}/submit-for-verification', [TelecallerController::class, 'submitLeadFormForVerification']);
        
        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('/notifications/unread', [\App\Http\Controllers\Api\NotificationController::class, 'getUnread']);
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/{notification}/click', [\App\Http\Controllers\Api\NotificationController::class, 'markAsClicked']);
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
        
        // Broadcasts
        Route::get('/broadcast/unread', [\App\Http\Controllers\Api\BroadcastController::class, 'getUnreadBroadcasts']);
        Route::post('/broadcast/{broadcast}/read', [\App\Http\Controllers\Api\BroadcastController::class, 'markAsRead']);
        
        // Admin/CRM Broadcast sending
        Route::middleware('role:admin,crm')->group(function () {
            Route::post('/broadcast/send', [\App\Http\Controllers\Api\BroadcastController::class, 'sendBroadcast']);
        });
        
        // Actions
        Route::post('/update-call-status', [TelecallerController::class, 'updateCallStatus']);
        Route::post('/mark-cnp', [TelecallerController::class, 'markCnp']);
        Route::post('/mark-broker', [TelecallerController::class, 'markBroker']);
        Route::post('/schedule-follow-up', [TelecallerController::class, 'scheduleFollowUp']);
        Route::post('/create-prospect', [TelecallerController::class, 'createProspect']);
        Route::post('/prospects/create', [TelecallerController::class, 'createProspectFromTask']);
        Route::post('/recall-assignment', [TelecallerController::class, 'recallAssignment']);
        Route::post('/blacklist-number', [TelecallerController::class, 'blacklistNumber']);
        
        // Supporting
        Route::get('/users', [TelecallerController::class, 'getUsers']);
        
        // Profile
        Route::get('/profile', [TelecallerController::class, 'getProfile']);
        Route::put('/profile', [TelecallerController::class, 'updateProfile']);
        Route::post('/profile/picture', [TelecallerController::class, 'uploadProfilePicture']);
        Route::post('/profile/password', [TelecallerController::class, 'changePassword']);
        Route::post('/profile/availability', [TelecallerController::class, 'updateAvailability']);
        
        // Call Tracking (Legacy - kept for backward compatibility)
        Route::post('/call-logs', [TelecallerController::class, 'saveCallLog']);
        Route::get('/call-logs', [TelecallerController::class, 'getCallLogs']);
        Route::get('/call-statistics', [TelecallerController::class, 'getCallStatistics']);
        
        // Site Visit Incentives (for Telecallers)
        Route::get('/site-visits/eligible-for-incentive', [TelecallerController::class, 'getEligibleSiteVisitsForIncentive']);
        Route::post('/site-visits/{siteVisit}/request-incentive', [TelecallerController::class, 'requestSiteVisitIncentive']);
        Route::get('/incentives', [IncentiveController::class, 'index']);
        Route::get('/incentives/{incentive}', [IncentiveController::class, 'show']);
    });

    // Enhanced Call Logs API (for all roles)
    Route::prefix('call-logs')->name('api.call-logs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\CallLogController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\CallLogController::class, 'store']);
        Route::post('/bulk-sync', [\App\Http\Controllers\Api\CallLogController::class, 'bulkSync']);
        Route::get('/statistics', [\App\Http\Controllers\Api\CallLogController::class, 'getStatistics']);
        Route::get('/team-statistics', [\App\Http\Controllers\Api\CallLogController::class, 'getTeamStatistics']);
        Route::get('/dashboard-stats', [\App\Http\Controllers\Api\CallLogController::class, 'getDashboardStats']);
        Route::get('/{id}', [\App\Http\Controllers\Api\CallLogController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\CallLogController::class, 'update']);
    });

    // Sales Manager routes (Admin, CRM, Sales Head, Senior Manager, Manager, Assistant Sales Manager)
    Route::prefix('sales-manager')->middleware('role:admin,crm,sales_head,sales_manager,senior_manager,assistant_sales_manager')->group(function () {
        // Profile
        Route::get('/leads-pending-response', [LeadsPendingResponseController::class, 'forCurrentUser']);
        Route::get('/profile', [\App\Http\Controllers\Api\SalesManagerController::class, 'getProfile']);
        Route::put('/profile', [\App\Http\Controllers\Api\SalesManagerController::class, 'updateProfile']);
        Route::post('/profile/picture', [\App\Http\Controllers\Api\SalesManagerController::class, 'uploadProfilePicture']);
        Route::post('/profile/password', [\App\Http\Controllers\Api\SalesManagerController::class, 'changePassword']);
        Route::get('/dashboard-settings', [\App\Http\Controllers\Api\SalesManagerController::class, 'getDashboardSettings']);
        Route::post('/dashboard-settings', [\App\Http\Controllers\Api\SalesManagerController::class, 'updateDashboardSettings']);
        
        // Team management
        Route::get('/team/member/{memberId}', [\App\Http\Controllers\Api\SalesManagerController::class, 'getTeamMemberDetails']);
        Route::get('/team/performance', [\App\Http\Controllers\Api\SalesManagerController::class, 'getTeamPerformance']);
        
        // Achievements
        Route::get('/achievements', [\App\Http\Controllers\Api\SalesManagerController::class, 'getAchievements']);
        
        // Prospects
        Route::get('/prospects', [\App\Http\Controllers\Api\SalesManagerController::class, 'getProspects']);
        Route::post('/prospects', [\App\Http\Controllers\Api\SalesManagerController::class, 'createProspect']);
        Route::get('/prospects/pending', [\App\Http\Controllers\Api\Crm\VerificationController::class, 'getPending']);
        Route::post('/prospects/{prospect}/verify', [\App\Http\Controllers\Api\Crm\VerificationController::class, 'verify']);
        Route::post('/prospects/{prospect}/reject', [\App\Http\Controllers\Api\Crm\VerificationController::class, 'reject']);

        // Favorite leads
        Route::get('/favorite-leads', [\App\Http\Controllers\Api\SalesManagerController::class, 'getFavoriteLeads']);
        Route::post('/leads/{lead}/favorite', [\App\Http\Controllers\Api\SalesManagerController::class, 'addFavoriteLead']);
        Route::delete('/leads/{lead}/favorite', [\App\Http\Controllers\Api\SalesManagerController::class, 'removeFavoriteLead']);
        
        // Tasks
        Route::get('/tasks', [\App\Http\Controllers\Api\SalesManagerController::class, 'getTasks']);
        Route::get('/tasks/{task}', [\App\Http\Controllers\Api\SalesManagerController::class, 'getTask']);
        Route::post('/tasks/schedule-call', [\App\Http\Controllers\Api\SalesManagerController::class, 'scheduleCallTask']);
        Route::post('/tasks/{task}/update-lead', [\App\Http\Controllers\Api\SalesManagerController::class, 'updateLeadFromTask']);
        Route::get('/tasks/{task}/lead-requirement-form', [\App\Http\Controllers\Api\SalesManagerController::class, 'getLeadRequirementFormForTask']);
        Route::post('/tasks/{task}/outcome', [\App\Http\Controllers\Api\SalesManagerController::class, 'submitTaskOutcome']);
        Route::post('/tasks/{task}/verify', [\App\Http\Controllers\Api\SalesManagerController::class, 'verifyProspectFromTask']);
        Route::post('/tasks/{task}/reject', [\App\Http\Controllers\Api\SalesManagerController::class, 'rejectProspectFromTask']);
        Route::post('/tasks/{task}/cnp', [\App\Http\Controllers\Api\SalesManagerController::class, 'markAsCNP']);
        Route::post('/tasks/{task}/complete', [\App\Http\Controllers\Api\SalesManagerController::class, 'completeTask']);
        Route::post('/tasks/remove-all-overdue', [\App\Http\Controllers\Api\SalesManagerController::class, 'removeAllOverdueTasks']);
        
        // Meetings
        Route::get('/meetings', [\App\Http\Controllers\Api\MeetingController::class, 'index']);
        Route::get('/meetings/lead-options', [\App\Http\Controllers\Api\MeetingController::class, 'leadOptions']);
        Route::post('/meetings/quick', [\App\Http\Controllers\Api\MeetingController::class, 'quickStore']);
        Route::post('/meetings/quick-schedule-with-reminder', [\App\Http\Controllers\Api\MeetingController::class, 'quickScheduleWithReminder']);
        Route::post('/meetings', [\App\Http\Controllers\Api\MeetingController::class, 'store']);
        Route::get('/meetings/{meeting}', [\App\Http\Controllers\Api\MeetingController::class, 'show']);
        Route::put('/meetings/{meeting}', [\App\Http\Controllers\Api\MeetingController::class, 'update']);
        Route::post('/meetings/{meeting}/complete', [\App\Http\Controllers\Api\MeetingController::class, 'complete']);
        Route::post('/meetings/{meeting}/complete-pre-call', [\App\Http\Controllers\Api\MeetingController::class, 'completePreCall']);
        Route::post('/meetings/{meeting}/cancel', [\App\Http\Controllers\Api\MeetingController::class, 'cancelMeeting']);
        Route::post('/meetings/{meeting}/reschedule', [\App\Http\Controllers\Api\MeetingController::class, 'reschedule']);
        Route::post('/meetings/{meeting}/convert-to-site-visit', [\App\Http\Controllers\Api\MeetingController::class, 'convertToSiteVisit']);
        Route::post('/meetings/{meeting}/mark-dead', [\App\Http\Controllers\Api\MeetingController::class, 'markDead']);
        Route::post('/meetings/{meeting}/verify', [\App\Http\Controllers\Api\MeetingController::class, 'verify']);
        Route::post('/meetings/{meeting}/reject', [\App\Http\Controllers\Api\MeetingController::class, 'reject']);
        Route::get('/leads/{leadId}/meeting-history', [\App\Http\Controllers\Api\MeetingController::class, 'getMeetingHistory']);
        
        // Site Visits
        Route::get('/site-visits', [\App\Http\Controllers\Api\SiteVisitController::class, 'index']);
        Route::post('/site-visits', [\App\Http\Controllers\Api\SiteVisitController::class, 'store']);
        Route::post('/site-visits/{siteVisit}/complete', [\App\Http\Controllers\Api\SiteVisitController::class, 'complete']);
        Route::post('/site-visits/{siteVisit}/reschedule', [\App\Http\Controllers\Api\SiteVisitController::class, 'reschedule']);
        Route::post('/site-visits/{siteVisit}/request-close', [\App\Http\Controllers\Api\SiteVisitController::class, 'requestClose']);
        Route::post('/site-visits/{siteVisit}/convert-to-closer', [\App\Http\Controllers\Api\SiteVisitController::class, 'convertToCloser']);
        Route::post('/site-visits/{siteVisit}/request-closer', [\App\Http\Controllers\Api\SiteVisitController::class, 'requestCloser']);
        Route::post('/site-visits/{siteVisit}/submit-kyc', [\App\Http\Controllers\Api\SiteVisitController::class, 'submitKyc']);
        Route::post('/site-visits/{siteVisit}/mark-dead', [\App\Http\Controllers\Api\SiteVisitController::class, 'markDead']);
        
        // Incentives (for Managers/Sales Executives - closer incentives)
        Route::get('/incentives', [IncentiveController::class, 'index']);
        Route::get('/incentives/{incentive}', [IncentiveController::class, 'show']);
        // Request incentive after closing is verified
        Route::post('/site-visits/{siteVisit}/request-incentive', [IncentiveController::class, 'requestIncentive']);
    });

    // CRM routes
    Route::prefix('crm')->group(function () {
        // Authentication
        Route::post('/login', [CrmAuthController::class, 'login']);
        
        // Dashboard API: all roles except CRM Admin and Sale Head (sbka dikhega unko chhod kar)
        Route::middleware(['auth:sanctum', 'crm_dashboard_access'])->group(function () {
            Route::get('/whoami', [CrmAuthController::class, 'whoami']);
            Route::get('/dashboard/stats', [CrmDashboardController::class, 'getStats']);
            Route::get('/dashboard/filter-roles', [CrmDashboardController::class, 'getPerformanceFilterRoles']);
            Route::get('/dashboard/telecaller-stats', [CrmDashboardController::class, 'getTelecallerStats']);
            Route::get('/dashboard/leads-pending-response', [CrmDashboardController::class, 'getLeadsPendingResponse']);
            Route::get('/dashboard/average-response-time', [CrmDashboardController::class, 'getAverageResponseTime']);
            Route::get('/dashboard/daily-prospects', [CrmDashboardController::class, 'getDailyProspects']);
        });
        
        Route::middleware(['auth:sanctum', 'crm'])->group(function () {
            // Auth routes (logout etc. – CRM/Admin only for other CRM features)
            Route::post('/logout', [CrmAuthController::class, 'logout']);
            
            // Leads
            Route::post('/add-lead', [CrmLeadController::class, 'addLead']);
            Route::get('/imported-leads', [CrmLeadController::class, 'getImportedLeads']);
            Route::post('/assign-leads', [CrmLeadController::class, 'assignLeads']);
            
            // Users
            Route::get('/users', [CrmUserController::class, 'index']);
            Route::get('/roles', [CrmUserController::class, 'getRoles']);
            Route::post('/users', [CrmUserController::class, 'store']);
            Route::put('/users/{id}', [CrmUserController::class, 'update']);
            Route::delete('/users/{id}', [CrmUserController::class, 'destroy']);
            
            // Transfer
            Route::post('/transfer-leads', [CrmTransferController::class, 'transfer']);
            
            // Blacklist
            Route::get('/blacklist', [CrmBlacklistController::class, 'index']);
            Route::post('/blacklist', [CrmBlacklistController::class, 'store']);
            Route::delete('/blacklist/{id}', [CrmBlacklistController::class, 'destroy']);
            
            // Targets
            Route::get('/targets', [CrmTargetController::class, 'index']);
            Route::post('/targets', [CrmTargetController::class, 'store']);
            Route::put('/targets/{id}', [CrmTargetController::class, 'update']);
            
            // Verifications
            Route::get('/pending-verifications', [CrmVerificationController::class, 'getPending']);
            Route::get('/verifications/pending-prospects', [CrmVerificationController::class, 'getPending']);
            Route::post('/verify-prospect/{prospect}', [CrmVerificationController::class, 'verify']);
            Route::post('/reject-prospect/{prospect}', [CrmVerificationController::class, 'reject']);
            
            // Meeting & Site Visit Verifications
            Route::post('/meetings/{meeting}/verify', [\App\Http\Controllers\Api\MeetingController::class, 'verify']);
            Route::post('/meetings/{meeting}/reject', [\App\Http\Controllers\Api\MeetingController::class, 'reject']);
            Route::post('/site-visits/{siteVisit}/verify', [\App\Http\Controllers\Api\SiteVisitController::class, 'verify']);
            Route::post('/site-visits/{siteVisit}/reject', [\App\Http\Controllers\Api\SiteVisitController::class, 'reject']);
            Route::post('/site-visits/{siteVisit}/verify-closer', [\App\Http\Controllers\Api\SiteVisitController::class, 'verifyCloser']);
            Route::post('/site-visits/{siteVisit}/reject-closer', [\App\Http\Controllers\Api\SiteVisitController::class, 'rejectCloser']);
            
            // Closing Verification (CRM) - New flow
            Route::post('/site-visits/{siteVisit}/verify-closing', [\App\Http\Controllers\Api\SiteVisitController::class, 'verifyClosing']);
            Route::post('/site-visits/{siteVisit}/reject-closing', [\App\Http\Controllers\Api\SiteVisitController::class, 'rejectClosing']);
            
            // Incentive Verifications (CRM) - Deprecated, kept for backward compatibility
            Route::post('/incentives/{incentive}/verify', [IncentiveController::class, 'verifyByCrm']);
            Route::post('/incentives/{incentive}/reject', [IncentiveController::class, 'rejectByCrm']);
        });
    });

    // Sales Head routes (for incentive verification - DEPRECATED)
    Route::prefix('sales-head')->middleware(['auth:sanctum', 'role:sales_head'])->group(function () {
        // Incentive Verifications (Sales Head) - Deprecated, kept for backward compatibility
        Route::post('/incentives/{incentive}/verify', [IncentiveController::class, 'verifyBySalesHead']);
        Route::post('/incentives/{incentive}/reject', [IncentiveController::class, 'rejectBySalesHead']);
    });

    // Finance Manager routes (for incentive verification)
    Route::prefix('finance-manager')->middleware(['auth:sanctum', 'role:finance_manager'])->group(function () {
        // Incentive Verifications (Finance Manager)
        Route::get('/incentives', [IncentiveController::class, 'index']);
        Route::get('/incentives/{incentive}', [IncentiveController::class, 'show']);
        Route::post('/incentives/{incentive}/verify', [IncentiveController::class, 'verifyByFinanceManager']);
        Route::post('/incentives/{incentive}/reject', [IncentiveController::class, 'rejectByFinanceManager']);
    });

    // Flow Testing routes (Admin and CRM)
    Route::prefix('admin/flow-test')->middleware(['auth:sanctum', 'role:admin,crm'])->group(function () {
        Route::get('/stages', [\App\Http\Controllers\Admin\FlowTestController::class, 'getFlowStages']);
        Route::post('/login-as/{userId}', [\App\Http\Controllers\Admin\FlowTestController::class, 'loginAsUser']);
        Route::post('/restore-original-user', [\App\Http\Controllers\Admin\FlowTestController::class, 'restoreOriginalUser']);
        Route::post('/stages/{stageId}/test', [\App\Http\Controllers\Admin\FlowTestController::class, 'testStage']);
        Route::post('/stages/{stageId}/validate', [\App\Http\Controllers\Admin\FlowTestController::class, 'validateStage']);
        Route::get('/stages/{stageId}/data', [\App\Http\Controllers\Admin\FlowTestController::class, 'getStageData']);
        Route::post('/stages/{stageId}/fix', [\App\Http\Controllers\Admin\FlowTestController::class, 'fixErrors']);
        Route::get('/users-by-role', [\App\Http\Controllers\Admin\FlowTestController::class, 'getUsersByRole']);
        Route::post('/reset', [\App\Http\Controllers\Admin\FlowTestController::class, 'resetFlow']);
    });

    // Admin routes (for verification)
    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin,crm'])->group(function () {
        // Dead Leads/Items
        Route::get('/dead-leads', function (Request $request) {
            $query = \App\Models\Lead::where('is_dead', true)
                ->with(['markedDeadBy', 'creator']);
            
            if ($request->has('dead_at_stage')) {
                $query->where('dead_at_stage', $request->dead_at_stage);
            }
            
            $leads = $query->latest('marked_dead_at')->paginate($request->get('per_page', 50));
            return response()->json($leads);
        });
        
        Route::get('/dead-meetings', function (Request $request) {
            $query = \App\Models\Meeting::where('is_dead', true)
                ->with(['markedDeadBy', 'creator', 'lead']);
            
            $meetings = $query->latest('marked_dead_at')->paginate($request->get('per_page', 50));
            return response()->json($meetings);
        });
        
        Route::get('/dead-site-visits', function (Request $request) {
            $query = \App\Models\SiteVisit::where('is_dead', true)
                ->with(['markedDeadBy', 'creator', 'lead']);
            
            $visits = $query->latest('marked_dead_at')->paginate($request->get('per_page', 50));
            return response()->json($visits);
        });
        
        // Meeting and Site Visit details for CRM/Admin
        Route::get('/meetings/{meeting}', function (Request $request, $meetingId) {
            try {
                $meeting = \App\Models\Meeting::with(['lead', 'prospect', 'creator', 'assignedTo', 'verifiedBy'])->findOrFail($meetingId);
                return response()->json([
                    'data' => $meeting,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Meeting not found',
                    'message' => $e->getMessage(),
                ], 404);
            }
        });

        Route::get('/site-visits/{siteVisit}', function (Request $request, $siteVisitId) {
            try {
                $siteVisit = \App\Models\SiteVisit::with(['lead', 'creator', 'assignedTo'])->findOrFail($siteVisitId);
                return response()->json([
                    'data' => $siteVisit,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Site visit not found',
                    'message' => $e->getMessage(),
                ], 404);
            }
        });

        // Prospect details for CRM/Admin
        Route::get('/prospects/{prospect}', function (Request $request, $prospectId) {
            try {
                $prospect = \App\Models\Prospect::with([
                    'lead', 
                    'createdBy', 
                    'assignedManager', 
                    'telecaller', 
                    'manager', 
                    'verifiedBy',
                    'assignment'
                ])->findOrFail($prospectId);
                return response()->json([
                    'data' => $prospect,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Prospect not found',
                    'message' => $e->getMessage(),
                ], 404);
            }
        });

        Route::get('/verifications/pending', function (Request $request) {
            try {
                $user = $request->user();
                if (!$user) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }

                // Get all pending meetings - verification_status 'pending' (or null for legacy)
                // Optimize: Eager load relationships and select only needed columns
                $meetings = \App\Models\Meeting::where('status', 'completed')
                    ->where(function ($q) {
                        $q->where('verification_status', 'pending')
                          ->orWhereNull('verification_status');
                    })
                    ->orderBy('completed_at', 'desc')
                    ->with([
                        'lead:id,name,phone',
                        'prospect:id,customer_name',
                        'creator:id,name,manager_id',
                        'assignedTo:id,name'
                    ])
                    ->select([
                        'id', 'customer_name', 'phone', 'scheduled_at', 'completed_at',
                        'status', 'verification_status', 'budget_range', 'property_type',
                        'meeting_notes', 'employee', 'occupation', 'date_of_visit',
                        'project', 'payment_mode', 'tentative_period', 'lead_type',
                        'team_leader', 'lead_id', 'prospect_id', 'created_by', 'assigned_to',
                        'photos', 'completion_proof_photos'
                    ])
                    ->get()
                    ->map(function($meeting) use ($user) {
                    $creator = $meeting->creator;
                    $canVerify = $user->isAdmin() || $user->isCrm();
                    if (!$canVerify && $creator) {
                        $canVerify = $user->isSeniorOf($creator);
                    }
                    return [
                        'id' => $meeting->id,
                        'customer_name' => $meeting->customer_name,
                        'phone' => $meeting->phone,
                        'scheduled_at' => $meeting->scheduled_at ? $meeting->scheduled_at->toIso8601String() : null,
                        'completed_at' => $meeting->completed_at ? $meeting->completed_at->toIso8601String() : null,
                        'status' => $meeting->status,
                        'verification_status' => $meeting->verification_status,
                        'budget_range' => $meeting->budget_range,
                        'property_type' => $meeting->property_type,
                        'meeting_notes' => $meeting->meeting_notes,
                        'employee' => $meeting->employee,
                        'occupation' => $meeting->occupation,
                        'date_of_visit' => $meeting->date_of_visit ? $meeting->date_of_visit->toIso8601String() : null,
                        'project' => $meeting->project,
                        'payment_mode' => $meeting->payment_mode,
                        'tentative_period' => $meeting->tentative_period,
                        'lead_type' => $meeting->lead_type,
                        'team_leader' => $meeting->team_leader,
                        'photos' => $meeting->photos ?? [],
                        'completion_proof_photos' => $meeting->completion_proof_photos ?? [],
                        'lead' => $meeting->lead ? ['id' => $meeting->lead->id, 'name' => $meeting->lead->name, 'phone' => $meeting->lead->phone] : null,
                        'prospect' => $meeting->prospect ? ['id' => $meeting->prospect->id, 'customer_name' => $meeting->prospect->customer_name] : null,
                        'creator' => $meeting->creator ? ['id' => $meeting->creator->id, 'name' => $meeting->creator->name] : null,
                        'assignedTo' => $meeting->assignedTo ? ['id' => $meeting->assignedTo->id, 'name' => $meeting->assignedTo->name] : null,
                        'can_verify' => $canVerify,
                    ];
                });
                
                // Get all pending site visits - verification_status 'pending' (or null for legacy)
                // Exclude site visits that are in "closer" flow (they appear under Closer Requests tab)
                // Optimize: Eager load relationships and select only needed columns
                $siteVisits = \App\Models\SiteVisit::where('status', 'completed')
                    ->where(function ($q) {
                        $q->where('verification_status', 'pending')
                          ->orWhereNull('verification_status');
                    })
                    ->where(function($query) {
                        $query->whereNull('closer_status')
                              ->orWhere('closer_status', '!=', 'pending');
                    })
                    ->orderBy('completed_at', 'desc')
                    ->with([
                        'lead:id,name,phone',
                        'creator:id,name,manager_id',
                        'assignedTo:id,name'
                    ])
                    ->select([
                        'id', 'customer_name', 'phone', 'scheduled_at', 'completed_at',
                        'status', 'verification_status', 'property_name', 'property_address',
                        'budget_range', 'visit_notes', 'closer_status', 'project',
                        'property_type', 'lead_type', 'lead_id', 'created_by', 'assigned_to',
                        'photos', 'completion_proof_photos'
                    ])
                    ->get()
                    ->map(function($visit) use ($user) {
                    $creator = $visit->creator;
                    $canVerify = $user->isAdmin() || $user->isCrm();
                    if (!$canVerify && $creator) {
                        $canVerify = $user->isSeniorOf($creator);
                    }
                    return [
                        'id' => $visit->id,
                        'customer_name' => $visit->customer_name,
                        'phone' => $visit->phone,
                        'scheduled_at' => $visit->scheduled_at ? $visit->scheduled_at->toIso8601String() : null,
                        'completed_at' => $visit->completed_at ? $visit->completed_at->toIso8601String() : null,
                        'status' => $visit->status,
                        'verification_status' => $visit->verification_status,
                        'property_name' => $visit->property_name,
                        'property_address' => $visit->property_address,
                        'budget_range' => $visit->budget_range,
                        'visit_notes' => $visit->visit_notes,
                        'closer_status' => $visit->closer_status,
                        'project' => $visit->project,
                        'property_type' => $visit->property_type,
                        'lead_type' => $visit->lead_type,
                        'employee' => $visit->employee,
                        'photos' => $visit->photos ?? [],
                        'completion_proof_photos' => $visit->completion_proof_photos ?? [],
                        'closer_request_proof_photos' => $visit->closer_request_proof_photos ?? [],
                        'lead' => $visit->lead ? ['id' => $visit->lead->id, 'name' => $visit->lead->name, 'phone' => $visit->lead->phone] : null,
                        'creator' => $visit->creator ? ['id' => $visit->creator->id, 'name' => $visit->creator->name] : null,
                        'assignedTo' => $visit->assignedTo ? ['id' => $visit->assignedTo->id, 'name' => $visit->assignedTo->name] : null,
                        'can_verify' => $canVerify,
                    ];
                });
                
                return response()->json([
                    'meetings' => array_values($meetings->toArray()),
                    'site_visits' => array_values($siteVisits->toArray()),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error loading pending verifications: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Failed to load pending verifications',
                    'message' => $e->getMessage(),
                    'meetings' => [],
                    'site_visits' => [],
                ], 500);
            }
        });
        
        Route::get('/verifications/pending-closers', function (Request $request) {
            try {
                $user = $request->user();
                if (!$user) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                $closers = \App\Models\SiteVisit::where('closer_status', 'pending')
                    ->where('verification_status', 'verified')
                    ->with([
                        'lead:id,name,phone',
                        'creator:id,name',
                        'assignedTo:id,name'
                    ])
                    ->select([
                        'id', 'customer_name', 'phone', 'scheduled_at', 'completed_at',
                        'status', 'verification_status', 'property_name', 'budget_range',
                        'visit_notes', 'closer_status', 'lead_id', 'created_by', 'assigned_to',
                        'photos', 'completion_proof_photos', 'closer_request_proof_photos', 'incentive_amount',
                        'closing_verification_status'
                    ])
                    ->with('incentives')
                    ->get()
                    ->map(function($visit) use ($user) {
                        $incentive = $visit->incentives()->where('type', 'closer')->first();
                        return [
                            'id' => $visit->id,
                            'customer_name' => $visit->customer_name,
                            'phone' => $visit->phone,
                            'scheduled_at' => $visit->scheduled_at ? $visit->scheduled_at->toIso8601String() : null,
                            'completed_at' => $visit->completed_at ? $visit->completed_at->toIso8601String() : null,
                            'status' => $visit->status,
                            'verification_status' => $visit->verification_status,
                            'property_name' => $visit->property_name,
                            'property_address' => $visit->property_address,
                            'budget_range' => $visit->budget_range,
                            'visit_notes' => $visit->visit_notes,
                            'closer_status' => $visit->closer_status,
                            'closing_verification_status' => $visit->closing_verification_status ?? null,
                            'incentive_amount' => $visit->incentive_amount ?? ($incentive ? $incentive->amount : 0),
                            'incentive_id' => $incentive ? $incentive->id : null,
                            'photos' => $visit->photos ?? [],
                            'completion_proof_photos' => $visit->completion_proof_photos ?? [],
                            'closer_request_proof_photos' => $visit->closer_request_proof_photos ?? [],
                            'lead' => $visit->lead ? ['id' => $visit->lead->id, 'name' => $visit->lead->name, 'phone' => $visit->lead->phone] : null,
                            'creator' => $visit->creator ? ['id' => $visit->creator->id, 'name' => $visit->creator->name] : null,
                            'assignedTo' => $visit->assignedTo ? ['id' => $visit->assignedTo->id, 'name' => $visit->assignedTo->name] : null,
                            'can_verify' => $user->isSalesHead() || $user->isCrm() || $user->isAdmin(),
                            'can_verify_closing' => $user->isCrm() || $user->isAdmin(),
                        ];
                    });
                
                return response()->json([
                    'data' => array_values($closers->toArray()),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error loading pending closers: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Failed to load pending closers',
                    'message' => $e->getMessage(),
                    'data' => [],
                ], 500);
            }
        });
        
        Route::get('/verifications/verified', function (Request $request) {
            try {
                $user = $request->user();
                if (!$user) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }

                // Get all verified meetings
                $meetings = \App\Models\Meeting::where('verification_status', 'verified')
                    ->where('status', 'completed')
                    ->with([
                        'lead:id,name,phone',
                        'prospect:id,customer_name',
                        'creator:id,name',
                        'assignedTo:id,name',
                        'verifiedBy:id,name'
                    ])
                    ->select([
                        'id', 'customer_name', 'phone', 'scheduled_at', 'completed_at',
                        'status', 'verification_status', 'budget_range', 'property_type',
                        'meeting_notes', 'employee', 'occupation', 'date_of_visit',
                        'project', 'payment_mode', 'tentative_period', 'lead_type',
                        'team_leader', 'lead_id', 'prospect_id', 'created_by', 'assigned_to',
                        'verified_by', 'verified_at', 'photos', 'completion_proof_photos'
                    ])
                    ->orderBy('verified_at', 'desc')
                    ->get()
                    ->map(function($meeting) {
                        return [
                            'id' => $meeting->id,
                            'customer_name' => $meeting->customer_name,
                            'phone' => $meeting->phone,
                            'scheduled_at' => $meeting->scheduled_at ? $meeting->scheduled_at->toIso8601String() : null,
                            'completed_at' => $meeting->completed_at ? $meeting->completed_at->toIso8601String() : null,
                            'verified_at' => $meeting->verified_at ? $meeting->verified_at->toIso8601String() : null,
                            'status' => $meeting->status,
                            'verification_status' => $meeting->verification_status,
                            'budget_range' => $meeting->budget_range,
                            'property_type' => $meeting->property_type,
                            'meeting_notes' => $meeting->meeting_notes,
                            'employee' => $meeting->employee,
                            'occupation' => $meeting->occupation,
                            'date_of_visit' => $meeting->date_of_visit ? $meeting->date_of_visit->toIso8601String() : null,
                            'project' => $meeting->project,
                            'payment_mode' => $meeting->payment_mode,
                            'tentative_period' => $meeting->tentative_period,
                            'lead_type' => $meeting->lead_type,
                            'team_leader' => $meeting->team_leader,
                            'photos' => $meeting->photos ?? [],
                            'completion_proof_photos' => $meeting->completion_proof_photos ?? [],
                            'lead' => $meeting->lead ? ['id' => $meeting->lead->id, 'name' => $meeting->lead->name, 'phone' => $meeting->lead->phone] : null,
                            'prospect' => $meeting->prospect ? ['id' => $meeting->prospect->id, 'customer_name' => $meeting->prospect->customer_name] : null,
                            'creator' => $meeting->creator ? ['id' => $meeting->creator->id, 'name' => $meeting->creator->name] : null,
                            'assignedTo' => $meeting->assignedTo ? ['id' => $meeting->assignedTo->id, 'name' => $meeting->assignedTo->name] : null,
                            'verifiedBy' => $meeting->verifiedBy ? ['id' => $meeting->verifiedBy->id, 'name' => $meeting->verifiedBy->name] : null,
                        ];
                    });
                
                // Get verified site visits (not closers) - verification_status = 'verified' but closer_status is NOT 'verified'
                $siteVisits = \App\Models\SiteVisit::where('verification_status', 'verified')
                    ->where('status', 'completed')
                    ->where(function($query) {
                        $query->whereNull('closer_status')
                              ->orWhere('closer_status', '!=', 'verified');
                    })
                    ->with([
                        'lead:id,name,phone',
                        'creator:id,name',
                        'assignedTo:id,name',
                        'verifiedBy:id,name'
                    ])
                    ->select([
                        'id', 'customer_name', 'phone', 'scheduled_at', 'completed_at',
                        'status', 'verification_status', 'property_name', 'property_address',
                        'budget_range', 'visit_notes', 'closer_status', 'project',
                        'property_type', 'lead_type', 'lead_id', 'created_by', 'assigned_to',
                        'verified_by', 'verified_at', 'photos', 'completion_proof_photos'
                    ])
                    ->orderBy('verified_at', 'desc')
                    ->get()
                    ->map(function($visit) {
                        return [
                            'id' => $visit->id,
                            'customer_name' => $visit->customer_name,
                            'phone' => $visit->phone,
                            'scheduled_at' => $visit->scheduled_at ? $visit->scheduled_at->toIso8601String() : null,
                            'completed_at' => $visit->completed_at ? $visit->completed_at->toIso8601String() : null,
                            'verified_at' => $visit->verified_at ? $visit->verified_at->toIso8601String() : null,
                            'status' => $visit->status,
                            'verification_status' => $visit->verification_status,
                            'property_name' => $visit->property_name,
                            'property_address' => $visit->property_address,
                            'budget_range' => $visit->budget_range,
                            'visit_notes' => $visit->visit_notes,
                            'closer_status' => $visit->closer_status,
                            'project' => $visit->project,
                            'property_type' => $visit->property_type,
                            'lead_type' => $visit->lead_type,
                            'employee' => $visit->employee,
                            'photos' => $visit->photos ?? [],
                            'completion_proof_photos' => $visit->completion_proof_photos ?? [],
                            'lead' => $visit->lead ? ['id' => $visit->lead->id, 'name' => $visit->lead->name, 'phone' => $visit->lead->phone] : null,
                            'creator' => $visit->creator ? ['id' => $visit->creator->id, 'name' => $visit->creator->name] : null,
                            'assignedTo' => $visit->assignedTo ? ['id' => $visit->assignedTo->id, 'name' => $visit->assignedTo->name] : null,
                            'verifiedBy' => $visit->verifiedBy ? ['id' => $visit->verifiedBy->id, 'name' => $visit->verifiedBy->name] : null,
                        ];
                    });
                
                // Get verified closers - site visits with closer_status = 'verified' AND verification_status = 'verified'
                $closers = \App\Models\SiteVisit::where('closer_status', 'verified')
                    ->where('verification_status', 'verified')
                    ->where('status', 'completed')
                    ->with([
                        'lead:id,name,phone',
                        'creator:id,name',
                        'assignedTo:id,name',
                        'verifiedBy:id,name',
                        'closerVerifiedBy:id,name'
                    ])
                    ->select([
                        'id', 'customer_name', 'phone', 'scheduled_at', 'completed_at',
                        'status', 'verification_status', 'property_name', 'property_address',
                        'budget_range', 'visit_notes', 'closer_status', 'project',
                        'property_type', 'lead_type', 'lead_id', 'created_by', 'assigned_to',
                        'verified_by', 'verified_at', 'closer_verified_by', 'closer_verified_at',
                        'photos', 'completion_proof_photos', 'closer_request_proof_photos'
                    ])
                    ->orderBy('closer_verified_at', 'desc')
                    ->get()
                    ->map(function($visit) {
                        return [
                            'id' => $visit->id,
                            'customer_name' => $visit->customer_name,
                            'phone' => $visit->phone,
                            'scheduled_at' => $visit->scheduled_at ? $visit->scheduled_at->toIso8601String() : null,
                            'completed_at' => $visit->completed_at ? $visit->completed_at->toIso8601String() : null,
                            'verified_at' => $visit->verified_at ? $visit->verified_at->toIso8601String() : null,
                            'closer_verified_at' => $visit->closer_verified_at ? $visit->closer_verified_at->toIso8601String() : null,
                            'status' => $visit->status,
                            'verification_status' => $visit->verification_status,
                            'closer_status' => $visit->closer_status,
                            'property_name' => $visit->property_name,
                            'property_address' => $visit->property_address,
                            'budget_range' => $visit->budget_range,
                            'visit_notes' => $visit->visit_notes,
                            'project' => $visit->project,
                            'property_type' => $visit->property_type,
                            'lead_type' => $visit->lead_type,
                            'employee' => $visit->employee,
                            'photos' => $visit->photos ?? [],
                            'completion_proof_photos' => $visit->completion_proof_photos ?? [],
                            'closer_request_proof_photos' => $visit->closer_request_proof_photos ?? [],
                            'lead' => $visit->lead ? ['id' => $visit->lead->id, 'name' => $visit->lead->name, 'phone' => $visit->lead->phone] : null,
                            'creator' => $visit->creator ? ['id' => $visit->creator->id, 'name' => $visit->creator->name] : null,
                            'assignedTo' => $visit->assignedTo ? ['id' => $visit->assignedTo->id, 'name' => $visit->assignedTo->name] : null,
                            'verifiedBy' => $visit->verifiedBy ? ['id' => $visit->verifiedBy->id, 'name' => $visit->verifiedBy->name] : null,
                            'closerVerifiedBy' => $visit->closerVerifiedBy ? ['id' => $visit->closerVerifiedBy->id, 'name' => $visit->closerVerifiedBy->name] : null,
                        ];
                    });
                
                $totalCount = $meetings->count() + $siteVisits->count() + $closers->count();
                
                return response()->json([
                    'meetings' => array_values($meetings->toArray()),
                    'site_visits' => array_values($siteVisits->toArray()),
                    'closers' => array_values($closers->toArray()),
                    'total_count' => $totalCount,
                ]);
            } catch (\Exception $e) {
                \Log::error('Error loading verified items: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Failed to load verified items',
                    'message' => $e->getMessage(),
                    'meetings' => [],
                    'site_visits' => [],
                    'closers' => [],
                    'total_count' => 0,
                ], 500);
            }
        });

        Route::get('/verifications/pending-incentives', function (Request $request) {
            try {
                $user = $request->user();
                if (!$user) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }

                $incentives = \App\Models\Incentive::where('status', 'pending_finance_manager')
                    ->with([
                        'siteVisit.lead:id,name,phone',
                        'user:id,name',
                    ])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($incentive) use ($user) {
                        return [
                            'id' => $incentive->id,
                            'type' => $incentive->type,
                            'amount' => $incentive->amount,
                            'status' => $incentive->status,
                            'created_at' => optional($incentive->created_at)->toIso8601String(),
                            'site_visit_id' => $incentive->site_visit_id,
                            'user' => $incentive->user ? [
                                'id' => $incentive->user->id,
                                'name' => $incentive->user->name,
                            ] : null,
                            'site_visit' => $incentive->siteVisit ? [
                                'id' => $incentive->siteVisit->id,
                                'customer_name' => $incentive->siteVisit->customer_name,
                                'phone' => $incentive->siteVisit->phone,
                                'closing_verification_status' => $incentive->siteVisit->closing_verification_status,
                                'lead' => $incentive->siteVisit->lead ? [
                                    'id' => $incentive->siteVisit->lead->id,
                                    'name' => $incentive->siteVisit->lead->name,
                                    'phone' => $incentive->siteVisit->lead->phone,
                                ] : null,
                            ] : null,
                            'can_verify' => $user->isAdmin() || $user->isCrm(),
                        ];
                    });

                return response()->json([
                    'data' => array_values($incentives->toArray()),
                ]);
            } catch (\Exception $e) {
                \Log::error('Error loading pending incentives: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return response()->json([
                    'error' => 'Failed to load pending incentives',
                    'message' => $e->getMessage(),
                    'data' => [],
                ], 500);
            }
        });
    });

    // Builder routes
    Route::prefix('builders')->group(function () {
        Route::get('/', [BuilderController::class, 'index']);
        Route::post('/', [BuilderController::class, 'store'])->middleware('role:admin,crm');
        Route::get('/{builder}', [BuilderController::class, 'show']);
        Route::put('/{builder}', [BuilderController::class, 'update'])->middleware('role:admin,crm');
        Route::delete('/{builder}', [BuilderController::class, 'destroy'])->middleware('role:admin,crm');
        
        // Builder logo upload
        Route::post('/{builder}/logo', [BuilderController::class, 'uploadLogo'])->middleware('role:admin,crm');
        
        // Builder contacts
        Route::post('/{builder}/contacts', [BuilderController::class, 'addContact'])->middleware('role:admin,crm');
        Route::put('/{builder}/contacts/{contact}', [BuilderController::class, 'updateContact'])->middleware('role:admin,crm');
        Route::delete('/{builder}/contacts/{contact}', [BuilderController::class, 'deleteContact'])->middleware('role:admin,crm');
        
        // Builder projects (nested)
        Route::get('/{builder}/projects', [ProjectController::class, 'index']);
        Route::post('/{builder}/projects', [ProjectController::class, 'store'])->middleware('role:admin,crm');
    });

    // Project routes
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('/{project}', [ProjectController::class, 'show']);
        Route::put('/{project}', [ProjectController::class, 'update'])->middleware('role:admin,crm');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->middleware('role:admin,crm');
        
        // Project detail (with contacts and collaterals)
        Route::get('/{project}/detail', [ProjectDetailController::class, 'show']);
        
        // Project collaterals
        Route::get('/{project}/collaterals', [ProjectCollateralController::class, 'index']);
        Route::get('/{project}/collaterals/buttons', [ProjectCollateralController::class, 'buttons']);
        Route::post('/{project}/collaterals', [ProjectCollateralController::class, 'store'])->middleware('role:admin,crm');
        
        // Pricing
        Route::get('/{project}/pricing', [PricingController::class, 'show']);
        Route::put('/{project}/pricing', [PricingController::class, 'update'])->middleware('role:admin,crm');
        
        // Unit types
        Route::get('/{project}/unit-types', [UnitTypeController::class, 'index']);
        Route::post('/{project}/unit-types', [UnitTypeController::class, 'store'])->middleware('role:admin,crm');
    });

    // Collateral routes (standalone)
    Route::prefix('collaterals')->group(function () {
        Route::put('/{collateral}', [ProjectCollateralController::class, 'update'])->middleware('role:admin,crm');
        Route::delete('/{collateral}', [ProjectCollateralController::class, 'destroy'])->middleware('role:admin,crm');
    });

    // Unit type routes (standalone)
    Route::prefix('unit-types')->group(function () {
        Route::put('/{unitType}', [UnitTypeController::class, 'update'])->middleware('role:admin,crm');
        Route::delete('/{unitType}', [UnitTypeController::class, 'destroy'])->middleware('role:admin,crm');
    });

    // Dynamic Forms API
    Route::prefix('forms')->group(function () {
        Route::get('/{identifier}', [\App\Http\Controllers\Api\DynamicFormController::class, 'getForm']);
        Route::get('/{identifier}/render', [\App\Http\Controllers\Api\DynamicFormController::class, 'renderForm']);
        Route::post('/{identifier}/submit', [\App\Http\Controllers\Api\DynamicFormController::class, 'submitForm']);
    });
});
