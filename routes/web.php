<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\EmergencyHotlineController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\NotificationClearController;
use App\Http\Controllers\ReportComplaintController;
use App\Http\Controllers\ReportsAnalyticsController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\DisasterEventController;
use App\Http\Controllers\DisasterAssessmentController;
use App\Http\Controllers\ReliefVoucherController;
use App\Http\Controllers\SummaryReportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    Route::get('/login', [
        AuthController::class,
        'showLogin',
    ])->name('login');

    Route::post('/login', [
        AuthController::class,
        'authenticate',
    ])->name('login.authenticate');

});


/*
|--------------------------------------------------------------------------
| Authenticated System Routes
|--------------------------------------------------------------------------
|
| Only authenticated users may access the Record Management System.
|
| The actual system roles are:
|
| - Barangay Captain
| - Barangay Secretary
|
| Treasurer and Staff are NOT system users and therefore do not receive
| route access.
|
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Core System Access
    |--------------------------------------------------------------------------
    |
    | Captain and Secretary are valid operational system users.
    |
    */

    Route::middleware('role:captain,secretary')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', [
            AuthController::class,
            'logout',
        ])->name('logout');


        /*
        |--------------------------------------------------------------------------
        | Schedule & Reminders
        |--------------------------------------------------------------------------
        |
        | Both Barangay Captain and Barangay Secretary may:
        |
        | - Create reminders
        | - View reminders
        | - Edit reminders
        | - Complete reminders
        | - Cancel reminders
        | - Delete reminders
        |
        | The global due-reminder endpoint is also available to both roles.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Due Reminder Endpoint
        |--------------------------------------------------------------------------
        |
        | Used by layouts/app.blade.php to check whether a reminder should
        | trigger the global popup notification.
        |
        | This route is intentionally placed BEFORE the resource route so
        | "due" is always treated as a fixed route and not as a reminder ID.
        |
        */

        Route::get('/reminders/due', [
            ReminderController::class,
            'due',
        ])->name('reminders.due');


        /*
        |--------------------------------------------------------------------------
        | Notification Bell: Clear / Clear All
        |--------------------------------------------------------------------------
        |
        | Used by the notification bell in layouts/app.blade.php.
        |
        | Clearing ONLY hides an item from the current user's bell. It never
        | deletes, completes, cancels, or edits the underlying Reminder.
        |
        */

        Route::post('/notifications/clear', [
            NotificationClearController::class,
            'clear',
        ])->name('notifications.clear');

        Route::post('/notifications/clear-all', [
            NotificationClearController::class,
            'clearAll',
        ])->name('notifications.clear-all');


        /*
        |--------------------------------------------------------------------------
        | Reminder Resource Routes
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'reminders',
            ReminderController::class
        )->except([
            'destroy',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Delete Reminder
        |--------------------------------------------------------------------------
        |
        | Kept separate from the resource route so the controller can
        | preserve the existing audit logging behavior.
        |
        */

        Route::delete(
            '/reminders/{reminder}',
            [
                ReminderController::class,
                'destroy',
            ]
        )->name('reminders.destroy');


        /*
        |--------------------------------------------------------------------------
        | Complete Reminder
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/reminders/{reminder}/complete',
            [
                ReminderController::class,
                'complete',
            ]
        )->name('reminders.complete');


        /*
        |--------------------------------------------------------------------------
        | Cancel Reminder
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/reminders/{reminder}/cancel',
            [
                ReminderController::class,
                'cancel',
            ]
        )->name('reminders.cancel');


        /*
        |--------------------------------------------------------------------------
        | Reports & Analytics
        |--------------------------------------------------------------------------
        */

        Route::get('/reports-analytics', [
            ReportsAnalyticsController::class,
            'index',
        ])->name('reports-analytics.index');


        /*
        |--------------------------------------------------------------------------
        | Emergency Hotline Directory
        |--------------------------------------------------------------------------
        */

        Route::prefix('emergency-hotlines')
            ->name('emergency-hotlines.')
            ->group(function () {

                Route::get('/', [
                    EmergencyHotlineController::class,
                    'index',
                ])->name('index');

                Route::get('/create', [
                    EmergencyHotlineController::class,
                    'create',
                ])->name('create');

                Route::post('/', [
                    EmergencyHotlineController::class,
                    'store',
                ])->name('store');

                Route::get('/{emergencyHotline}/edit', [
                    EmergencyHotlineController::class,
                    'edit',
                ])->name('edit');

                Route::put('/{emergencyHotline}', [
                    EmergencyHotlineController::class,
                    'update',
                ])->name('update');

                Route::patch('/{emergencyHotline}/activate', [
                    EmergencyHotlineController::class,
                    'activate',
                ])->name('activate');

                Route::patch('/{emergencyHotline}/deactivate', [
                    EmergencyHotlineController::class,
                    'deactivate',
                ])->name('deactivate');

                Route::get('/{emergencyHotline}', [
                    EmergencyHotlineController::class,
                    'show',
                ])->name('show');

                Route::delete('/{emergencyHotline}', [
                    EmergencyHotlineController::class,
                    'destroy',
                ])->name('destroy');

            });


        /*
        |--------------------------------------------------------------------------
        | Residents & Households
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'residents',
            ResidentController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Household Masterlist
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/households/masterlist',
            [
                HouseholdController::class,
                'masterlist',
            ]
        )->name('households.masterlist');


        /*
        |--------------------------------------------------------------------------
        | Household Map
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/households/map',
            [
                HouseholdController::class,
                'map',
            ]
        )->name('households.map');


        /*
        |--------------------------------------------------------------------------
        | Household Map Data
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/households/map/data',
            [
                HouseholdController::class,
                'mapData',
            ]
        )->name('households.map.data');


        /*
        |--------------------------------------------------------------------------
        | Add Household Member
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/households/{household}/members',
            [
                HouseholdController::class,
                'addMember',
            ]
        )->name('households.members.add');


        /*
        |--------------------------------------------------------------------------
        | Remove Household Member
        |--------------------------------------------------------------------------
        */

        Route::delete(
            '/households/{household}/members/{member}',
            [
                HouseholdController::class,
                'removeMember',
            ]
        )->name('households.members.remove');


        /*
        |--------------------------------------------------------------------------
        | Archive Household
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/households/{household}/archive',
            [
                HouseholdController::class,
                'archive',
            ]
        )->name('households.archive');


        /*
        |--------------------------------------------------------------------------
        | Restore Household
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/households/{household}/restore',
            [
                HouseholdController::class,
                'restore',
            ]
        )->name('households.restore');


        /*
        |--------------------------------------------------------------------------
        | Households
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'households',
            HouseholdController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Barangay Documents - General Access
        |--------------------------------------------------------------------------
        |
        | Captain and Secretary may access document requests.
        |
        */

        Route::resource(
            'documents',
            DocumentRequestController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Barangay Documents - Processing
        |--------------------------------------------------------------------------
        |
        | Only Captain and Secretary are system users, so both are permitted
        | to process document requests.
        |
        | The controller performs an additional canProcessDocuments() check.
        |
        */

        Route::post(
            '/documents/{document}/advance',
            [
                DocumentRequestController::class,
                'advance',
            ]
        )->name('documents.advance');


        /*
        |--------------------------------------------------------------------------
        | Barangay Documents - Preview
        |--------------------------------------------------------------------------
        |
        | Opens the generated official document in a separate browser tab.
        |
        */

        Route::get(
            '/documents/{document}/preview',
            [
                DocumentRequestController::class,
                'preview',
            ]
        )->name('documents.preview');


        /*
        |--------------------------------------------------------------------------
        | Barangay Documents - Printing
        |--------------------------------------------------------------------------
        |
        | Opens the generated official document in a separate browser tab
        | and advances APPROVED documents to PRINTED when applicable.
        |
        */

        Route::get(
            '/documents/{document}/print',
            [
                DocumentRequestController::class,
                'print',
            ]
        )->name('documents.print');


        /*
        |--------------------------------------------------------------------------
        | Official Document Templates
        |--------------------------------------------------------------------------
        |
        | Official barangay document templates are stored in private
        | application storage.
        |
        | Both Captain and Secretary may manage official templates.
        |
        */

        Route::middleware('role:captain,secretary')->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Template Registry
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/document-templates',
                [
                    DocumentTemplateController::class,
                    'index',
                ]
            )->name('document-templates.index');


            /*
            |--------------------------------------------------------------------------
            | Upload Official Template
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/document-templates/create',
                [
                    DocumentTemplateController::class,
                    'create',
                ]
            )->name('document-templates.create');


            /*
            |--------------------------------------------------------------------------
            | Store Official Template
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/document-templates',
                [
                    DocumentTemplateController::class,
                    'store',
                ]
            )->name('document-templates.store');


            /*
            |--------------------------------------------------------------------------
            | Deactivate Official Template
            |--------------------------------------------------------------------------
            */

            Route::patch(
                '/document-templates/{documentTemplate}/deactivate',
                [
                    DocumentTemplateController::class,
                    'deactivate',
                ]
            )->name('document-templates.deactivate');

        });


        /*
        |--------------------------------------------------------------------------
        | Reports & Complaints
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'reports-complaints',
            ReportComplaintController::class
        )->except([
            'destroy',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Resolve Report / Complaint
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/reports-complaints/{reports_complaint}/resolve',
            [
                ReportComplaintController::class,
                'resolve',
            ]
        )->name('reports-complaints.resolve');


        /*
        |--------------------------------------------------------------------------
        | Close Report / Complaint
        |--------------------------------------------------------------------------
        |
        | A case can only be Closed after it has been marked as Resolved.
        |
        */

        Route::patch(
            '/reports-complaints/{reports_complaint}/close',
            [
                ReportComplaintController::class,
                'close',
            ]
        )->name('reports-complaints.close');


        /*
        |--------------------------------------------------------------------------
        | Print Subpoena
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reports-complaints/{reports_complaint}/subpoena/print',
            [
                ReportComplaintController::class,
                'printSubpoena',
            ]
        )->name('reports-complaints.subpoena.print');


        /*
        |--------------------------------------------------------------------------
        | Disaster Events
        |--------------------------------------------------------------------------
        |
        | Supporting disaster records.
        | These are NOT required to start a Relief Assistance operation.
        |
        */

        Route::resource(
            'disaster-events',
            DisasterEventController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Disaster Assessments
        |--------------------------------------------------------------------------
        |
        | Supporting disaster documentation.
        | These are NOT required before starting Relief Assistance.
        |
        */

        Route::resource(
            'disaster-assessments',
            DisasterAssessmentController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Verify Disaster Assessment
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/disaster-assessments/{disaster_assessment}/verify',
            [
                DisasterAssessmentController::class,
                'verify',
            ]
        )->name('disaster-assessments.verify');


        /*
        |--------------------------------------------------------------------------
        | RELIEF ASSISTANCE / RELIEF VOUCHERS
        |--------------------------------------------------------------------------
        |
        | FINAL WORKFLOW
        |
        | Relief Assistance
        |        ↓
        | Open Operation
        |        ↓
        | Filter Existing Records
        |        ↓
        | Select Beneficiaries
        |        ↓
        | Bulk Print Selected Beneficiaries
        |        ↓
        | Generate QR Vouchers
        |        ↓
        | Print QR Vouchers
        |        ↓
        | QR Verification
        |        ↓
        | Claim
        |
        | IMPORTANT:
        |
        | Filters are only search/filter tools.
        | They do NOT automatically approve beneficiaries.
        |
        */


        /*
        |--------------------------------------------------------------------------
        | Relief Assistance Main Interface
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/relief-vouchers',
            [
                ReliefVoucherController::class,
                'index',
            ]
        )->name('relief-vouchers.index');


        /*
        |--------------------------------------------------------------------------
        | Create Relief Assistance Operation
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/relief-vouchers/create',
            [
                ReliefVoucherController::class,
                'createOperation',
            ]
        )->name('relief-assistance.create');


        /*
        |--------------------------------------------------------------------------
        | Store Relief Assistance Operation
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/relief-vouchers',
            [
                ReliefVoucherController::class,
                'storeOperation',
            ]
        )->name('relief-assistance.store');


        /*
        |--------------------------------------------------------------------------
        | Household / Beneficiary Selection
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/relief-vouchers/masterlist',
            [
                ReliefVoucherController::class,
                'masterlist',
            ]
        )->name('relief-assistance.masterlist');


        /*
        |--------------------------------------------------------------------------
        | Bulk Print Selected Beneficiaries
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/relief-vouchers/masterlist/print',
            [
                ReliefVoucherController::class,
                'masterlistPrint',
            ]
        )->name('relief-assistance.masterlist.print');


        /*
        |--------------------------------------------------------------------------
        | Relief Operation Details
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/relief-vouchers/operations/{operation}',
            [
                ReliefVoucherController::class,
                'operationShow',
            ]
        )->name('relief-assistance.show');


        /*
        |--------------------------------------------------------------------------
        | Edit Relief Assistance Operation
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/relief-vouchers/operations/{operation}/edit',
            [
                ReliefVoucherController::class,
                'operationEdit',
            ]
        )->name('relief-assistance.edit');


        /*
        |--------------------------------------------------------------------------
        | Update Relief Assistance Operation
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/relief-vouchers/operations/{operation}',
            [
                ReliefVoucherController::class,
                'operationUpdate',
            ]
        )->name('relief-assistance.update');


        /*
        |--------------------------------------------------------------------------
        | Generate QR Vouchers
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | The Blade beneficiary interface calls:
        |
        |     route('relief-vouchers.generate')
        |
        | Therefore the route name MUST remain:
        |
        |     relief-vouchers.generate
        |
        */

        Route::post(
            '/relief-vouchers/generate',
            [
                ReliefVoucherController::class,
                'generate',
            ]
        )->name('relief-vouchers.generate');


        /*
        |--------------------------------------------------------------------------
        | QR Verification Page
        |--------------------------------------------------------------------------
        |
        | This route appears before:
        |
        | /relief-vouchers/{reliefVoucher}
        |
        | so "qr-verification" is not interpreted as a voucher ID.
        |
        */

        Route::get(
            '/relief-vouchers/qr-verification',
            [
                ReliefVoucherController::class,
                'qrVerification',
            ]
        )->name('relief-vouchers.qr-verification');


        /*
        |--------------------------------------------------------------------------
        | QR Verification
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/relief-vouchers/verify-qr',
            [
                ReliefVoucherController::class,
                'verifyQr',
            ]
        )->name('relief-vouchers.verify-qr');


        /*
        |--------------------------------------------------------------------------
        | Compatibility: Old Beneficiary Workflow
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/relief-vouchers/verification',
            [
                ReliefVoucherController::class,
                'saveVerification',
            ]
        )->name('relief-assistance.verification.save');


        Route::get(
            '/relief-vouchers/operations/{operation}/beneficiaries',
            [
                ReliefVoucherController::class,
                'beneficiaries',
            ]
        )->name('relief-assistance.beneficiaries');


        Route::post(
            '/relief-vouchers/operations/{operation}/beneficiaries/confirm',
            [
                ReliefVoucherController::class,
                'confirmBeneficiaries',
            ]
        )->name('relief-assistance.beneficiaries.confirm');


        Route::get(
            '/relief-vouchers/operations/{operation}/beneficiaries/print',
            [
                ReliefVoucherController::class,
                'beneficiariesPrint',
            ]
        )->name('relief-assistance.beneficiaries.print');


        Route::get(
            '/relief-vouchers/operations/{operation}/allocation',
            [
                ReliefVoucherController::class,
                'allocation',
            ]
        )->name('relief-assistance.allocation');


        Route::post(
            '/relief-vouchers/operations/{operation}/allocation',
            [
                ReliefVoucherController::class,
                'saveAllocation',
            ]
        )->name('relief-assistance.allocation.save');


        /*
        |--------------------------------------------------------------------------
        | Compatibility: Voucher Records
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/relief-vouchers/vouchers',
            [
                ReliefVoucherController::class,
                'vouchers',
            ]
        )->name('relief-assistance.vouchers');


        /*
        |--------------------------------------------------------------------------
        | View Individual Relief Voucher
        |--------------------------------------------------------------------------
        |
        | Keep this AFTER fixed Relief Voucher routes.
        |
        */

        Route::get(
            '/relief-vouchers/{reliefVoucher}',
            [
                ReliefVoucherController::class,
                'show',
            ]
        )->name('relief-vouchers.show');


        /*
        |--------------------------------------------------------------------------
        | Print Individual Relief Voucher
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/relief-vouchers/{reliefVoucher}/print',
            [
                ReliefVoucherController::class,
                'print',
            ]
        )->name('relief-vouchers.print');


        /*
        |--------------------------------------------------------------------------
        | Issue Voucher
        |--------------------------------------------------------------------------
        |
        | Compatibility route.
        | New vouchers are currently created as issued.
        |
        */

        Route::patch(
            '/relief-vouchers/{reliefVoucher}/issue',
            [
                ReliefVoucherController::class,
                'issue',
            ]
        )->name('relief-vouchers.issue');


        /*
        |--------------------------------------------------------------------------
        | Distribute Voucher
        |--------------------------------------------------------------------------
        |
        | Compatibility route.
        |
        */

        Route::patch(
            '/relief-vouchers/{reliefVoucher}/distribute',
            [
                ReliefVoucherController::class,
                'distribute',
            ]
        )->name('relief-vouchers.distribute');


        /*
        |--------------------------------------------------------------------------
        | Cancel Voucher
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/relief-vouchers/{reliefVoucher}/cancel',
            [
                ReliefVoucherController::class,
                'cancel',
            ]
        )->name('relief-vouchers.cancel');


        /*
        |--------------------------------------------------------------------------
        | Claim Voucher
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/relief-vouchers/{reliefVoucher}/claim',
            [
                ReliefVoucherController::class,
                'claim',
            ]
        )->name('relief-vouchers.claim');

    });


    /*
    |--------------------------------------------------------------------------
    | CAPTAIN-ONLY ADMINISTRATION
    |--------------------------------------------------------------------------
    |
    | These functions contain system-wide administrative information or
    | account/security controls.
    |
    | Secretary does not receive access to these routes.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Audit Trail
    |--------------------------------------------------------------------------
    |
    | The Audit Trail contains system-wide personnel activity records.
    |
    */

    Route::middleware('role:captain')->group(function () {

        Route::get('/audit-logs', [
            AuditLogController::class,
            'index',
        ])->name('audit-logs.index');


        /*
        |--------------------------------------------------------------------------
        | Summary Reports
        |--------------------------------------------------------------------------
        |
        | Official administrative summary reports.
        |
        */

        Route::get('/summary-reports', [
            SummaryReportController::class,
            'index',
        ])->name('summary-reports.index');


        /*
        |--------------------------------------------------------------------------
        | Print Summary Reports
        |--------------------------------------------------------------------------
        */

        Route::get('/summary-reports/print', [
            SummaryReportController::class,
            'print',
        ])->name('summary-reports.print');


        /*
        |--------------------------------------------------------------------------
        | User Management
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/users',
            [
                UserManagementController::class,
                'index',
            ]
        )->name('users.index');


        Route::get(
            '/users/create',
            [
                UserManagementController::class,
                'create',
            ]
        )->name('users.create');


        Route::post(
            '/users',
            [
                UserManagementController::class,
                'store',
            ]
        )->name('users.store');


        Route::get(
            '/users/{user}',
            [
                UserManagementController::class,
                'show',
            ]
        )->name('users.show');


        Route::get(
            '/users/{user}/edit',
            [
                UserManagementController::class,
                'edit',
            ]
        )->name('users.edit');


        Route::put(
            '/users/{user}',
            [
                UserManagementController::class,
                'update',
            ]
        )->name('users.update');


        Route::match(
            ['put', 'patch'],
            '/users/{user}/password',
            [
                UserManagementController::class,
                'updatePassword',
            ]
        )->name('users.password.update');


        Route::patch(
            '/users/{user}/activate',
            [
                UserManagementController::class,
                'activate',
            ]
        )->name('users.activate');


        Route::patch(
            '/users/{user}/deactivate',
            [
                UserManagementController::class,
                'deactivate',
            ]
        )->name('users.deactivate');


        /*
        |--------------------------------------------------------------------------
        | Settings Page
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/settings',
            [
                SettingsController::class,
                'index',
            ]
        )->name('settings.index');


        /*
        |--------------------------------------------------------------------------
        | Update Profile Settings
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/settings/profile',
            [
                SettingsController::class,
                'updateProfile',
            ]
        )->name('settings.profile.update');


        /*
        |--------------------------------------------------------------------------
        | Update Theme Settings
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/settings/theme',
            [
                SettingsController::class,
                'updateTheme',
            ]
        )->name('settings.theme.update');


        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        */

        Route::put(
            '/settings/password',
            [
                SettingsController::class,
                'updatePassword',
            ]
        )->name('settings.password.update');


        /*
        |--------------------------------------------------------------------------
        | Remove Avatar
        |--------------------------------------------------------------------------
        */

        Route::delete(
            '/settings/avatar',
            [
                SettingsController::class,
                'removeAvatar',
            ]
        )->name('settings.avatar.remove');

    });

});
