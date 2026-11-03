<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceKioskController;
use App\Http\Controllers\AttendanceManagementController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisasterAssessmentController;
use App\Http\Controllers\DisasterEventController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\EmergencyHotlineController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\NotificationClearController;
use App\Http\Controllers\ReliefVoucherController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ReportComplaintController;
use App\Http\Controllers\ReportsAnalyticsController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SummaryReportController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Landing Page and Attendance Kiosk
|--------------------------------------------------------------------------
|
| The RMS Login Carousel is the default landing page.
| The public Attendance Kiosk remains accessible separately and does not
| require an RMS account.
|
| Attendance recording still requires:
| 1. Registered and active personnel.
| 2. Personnel ID or QR identification.
| 3. Valid six-digit personnel PIN.
| 4. Explicit Time In or Time Out selection.
|
*/

Route::redirect('/', '/login')->name('home');

Route::get(
    '/attendance-kiosk',
    [AttendanceKioskController::class, 'index']
)->name('attendance.kiosk');

Route::post(
    '/attendance/identify',
    [AttendanceKioskController::class, 'identify']
)->middleware('throttle:10,1')
  ->name('attendance.kiosk.identify');

Route::post(
    '/attendance/verify-pin',
    [AttendanceKioskController::class, 'verifyPin']
)->middleware('throttle:10,1')
  ->name('attendance.kiosk.verify-pin');

Route::post(
    '/attendance/record',
    [AttendanceKioskController::class, 'recordAttendance']
)->middleware('throttle:20,1')
  ->name('attendance.kiosk.record');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
|
| Local username/password login remains available offline.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'authenticate'])
        ->middleware('throttle:20,1')
        ->name('login.authenticate');

    /*
    |--------------------------------------------------------------------------
    | Email Password Recovery
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/password-recovery',
        [AuthController::class, 'showEmailPasswordRecovery']
    )->name('password-recovery.show');

    Route::post(
        '/password-recovery/send-code',
        [AuthController::class, 'sendEmailPasswordRecoveryCode']
    )->middleware('throttle:5,1')
      ->name('password-recovery.send-code');

    Route::post(
        '/password-recovery/reset',
        [AuthController::class, 'resetPasswordWithEmailCode']
    )->middleware('throttle:10,1')
      ->name('password-recovery.reset');

    Route::get(
        '/auth/google/redirect',
        [AuthController::class, 'redirectToGoogle']
    )->name('google.redirect');

    /*
    |--------------------------------------------------------------------------
    | Captain Password Recovery
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/captain-password-recovery',
        [AuthController::class, 'showCaptainRecovery']
    )->name('captain-recovery.show');

    Route::post(
        '/captain-password-recovery/verify',
        [AuthController::class, 'verifyCaptainRecoveryCredential']
    )->middleware('throttle:10,1')
      ->name('captain-recovery.verify');

    Route::post(
        '/captain-password-recovery',
        [AuthController::class, 'resetCaptainPassword']
    )->middleware('throttle:5,1')
      ->name('captain-recovery.reset');

    /*
    |--------------------------------------------------------------------------
    | Secretary Initial-Password Setup / Authorized Recovery
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/secretary-password-setup',
        [UserManagementController::class, 'showInitialPasswordSetup']
    )->name('secretary-password-setup');

    Route::post(
        '/secretary-password-setup',
        [UserManagementController::class, 'setInitialPassword']
    )->middleware('throttle:10,1')
      ->name('secretary-password-setup.store');

    /*
    |--------------------------------------------------------------------------
    | Secretary Initial Email Verification
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/secretary-email-verification/verify',
        [UserManagementController::class, 'verifySecretaryEmail']
    )->middleware('throttle:5,1')
      ->name('secretary-email-verification.verify');

    Route::post(
        '/secretary-email-verification/resend',
        [UserManagementController::class, 'resendSecretaryEmailVerification']
    )->middleware('throttle:3,15')
      ->name('secretary-email-verification.resend');

    /*
    |--------------------------------------------------------------------------
    | Captain-Authorized Secretary Authenticator Recovery
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/secretary-authenticator-recovery',
        [TwoFactorController::class, 'showSecretaryAuthenticatorRecovery']
    )->name('secretary-authenticator-recovery.show');

    Route::post(
        '/secretary-authenticator-recovery/verify',
        [TwoFactorController::class, 'verifySecretaryAuthenticatorRecovery']
    )->middleware('throttle:5,1')
      ->name('secretary-authenticator-recovery.verify');

    Route::post(
        '/secretary-authenticator-recovery/confirm',
        [TwoFactorController::class, 'confirmSecretaryAuthenticatorRecovery']
    )->middleware('throttle:5,1')
      ->name('secretary-authenticator-recovery.confirm');

    Route::post(
        '/secretary-authenticator-recovery/finish',
        [TwoFactorController::class, 'finishSecretaryAuthenticatorRecovery']
    )->middleware('throttle:5,1')
      ->name('secretary-authenticator-recovery.finish');
});

/*
|--------------------------------------------------------------------------
| Google OAuth Callback
|--------------------------------------------------------------------------
|
| Google Sign-In must not bypass the authenticator challenge.
|
*/

Route::get(
    '/auth/google/callback',
    [AuthController::class, 'handleGoogleCallback']
)->name('google.callback');

/*
|--------------------------------------------------------------------------
| Logged In, But 2FA Not Necessarily Completed
|--------------------------------------------------------------------------
|
| These routes must not require the 'twofactor' middleware because the
| user may still be completing the authenticator challenge or a
| restricted authenticator-recovery process.
|
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::middleware('active')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Authenticator Replacement / Restricted Recovery
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/settings/authenticator-replacement',
            [TwoFactorController::class, 'showAuthenticatorRecovery']
        )->name('settings.authenticator-replacement.page');

        Route::post(
            '/settings/authenticator-replacement/start',
            [TwoFactorController::class, 'startAuthenticatorReplacement']
        )->middleware('throttle:5,1')
          ->name('settings.authenticator-replacement.start');

        Route::post(
            '/settings/authenticator-replacement/verify',
            [TwoFactorController::class, 'verifyAuthenticatorReplacement']
        )->middleware('throttle:5,1')
          ->name('settings.authenticator-replacement.verify');

        Route::post(
            '/settings/authenticator-replacement/finish',
            [TwoFactorController::class, 'finishAuthenticatorReplacement']
        )->middleware('throttle:5,1')
          ->name('settings.authenticator-replacement.finish');

        Route::prefix('two-factor')
            ->name('two-factor.')
            ->group(function () {
                Route::get(
                    '/setup',
                    [TwoFactorController::class, 'showSetup']
                )->name('setup');

                Route::post(
                    '/confirm-setup',
                    [TwoFactorController::class, 'confirmSetup']
                )->middleware('throttle:10,1')
                  ->name('confirm-setup');

                Route::get(
                    '/recovery-codes',
                    [TwoFactorController::class, 'showRecoveryCodes']
                )->name('recovery-codes');

                Route::post(
                    '/finish-setup',
                    [TwoFactorController::class, 'finishSetup']
                )->name('finish-setup');

                Route::get(
                    '/challenge',
                    [TwoFactorController::class, 'showChallenge']
                )->name('challenge');

                Route::post(
                    '/verify',
                    [TwoFactorController::class, 'verifyChallenge']
                )->middleware('throttle:10,1')
                  ->name('verify');

                /*
                |--------------------------------------------------------------------------
                | Email-Based 2FA Recovery
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/email-recovery/send',
                    [TwoFactorController::class, 'sendEmailRecoveryCode']
                )->middleware('throttle:3,15')
                  ->name('email-recovery.send');

                Route::post(
                    '/email-recovery/verify',
                    [TwoFactorController::class, 'verifyEmailRecoveryCode']
                )->middleware('throttle:5,5')
                  ->name('email-recovery.verify');
            });
    });
});

/*
|--------------------------------------------------------------------------
| Fully Authenticated: Active Account + 2FA Completed
|--------------------------------------------------------------------------
|
| Middleware order: auth -> active -> twofactor.
|
*/

Route::middleware(['auth', 'active', 'twofactor'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Captain and Secretary Operational Access
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:captain,secretary')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Attendance Monitoring
        |--------------------------------------------------------------------------
        |
        | Available only to authenticated, active Captain and Secretary
        | accounts that have completed two-factor authentication.
        |
        */

        Route::prefix('attendance')
            ->name('attendance.')
            ->group(function () {

                Route::get(
                    '/',
                    [AttendanceManagementController::class, 'index']
                )->name('index');

                Route::get(
                    '/records',
                    [AttendanceManagementController::class, 'records']
                )->name('records');

                Route::get(
                    '/personnel',
                    [AttendanceManagementController::class, 'personnelIndex']
                )->name('personnel.index');

                Route::get(
                    '/personnel/create',
                    [AttendanceManagementController::class, 'createPersonnel']
                )->name('personnel.create');

                Route::post(
                    '/personnel',
                    [AttendanceManagementController::class, 'storePersonnel']
                )->name('personnel.store');

                Route::get(
                    '/personnel/{personnel}/edit',
                    [AttendanceManagementController::class, 'editPersonnel']
                )->name('personnel.edit');

                Route::get(
                    '/personnel/{personnel}',
                    [AttendanceManagementController::class, 'showPersonnel']
                )->name('personnel.show');

                Route::put(
                    '/personnel/{personnel}',
                    [AttendanceManagementController::class, 'updatePersonnel']
                )->name('personnel.update');

                Route::patch(
                    '/personnel/{personnel}/deactivate',
                    [AttendanceManagementController::class, 'deactivatePersonnel']
                )->name('personnel.deactivate');

                Route::post(
                    '/personnel/{personnel}/qr',
                    [AttendanceManagementController::class, 'issueQr']
                )->name('personnel.qr.issue');

                Route::get(
                    '/reports',
                    [AttendanceManagementController::class, 'reports']
                )->name('reports');
            });

        /*
        |--------------------------------------------------------------------------
        | Settings Email Verification
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/settings/email-verification/send',
            [SettingsController::class, 'sendEmailVerificationCode']
        )->middleware('throttle:3,15')
          ->name('settings.email-verification.send');

        Route::post(
            '/settings/email-verification/verify',
            [SettingsController::class, 'verifyEmailAddress']
        )->middleware('throttle:5,10')
          ->name('settings.email-verification.verify');

        /* Reminders and notification bell */

        Route::get('/reminders/due', [ReminderController::class, 'due'])
            ->name('reminders.due');

        Route::post(
            '/notifications/clear',
            [NotificationClearController::class, 'clear']
        )->name('notifications.clear');

        Route::post(
            '/notifications/clear-all',
            [NotificationClearController::class, 'clearAll']
        )->name('notifications.clear-all');

        Route::resource('reminders', ReminderController::class)
            ->except(['destroy']);

        Route::delete(
            '/reminders/{reminder}',
            [ReminderController::class, 'destroy']
        )->name('reminders.destroy');

        Route::patch(
            '/reminders/{reminder}/complete',
            [ReminderController::class, 'complete']
        )->name('reminders.complete');

        Route::patch(
            '/reminders/{reminder}/cancel',
            [ReminderController::class, 'cancel']
        )->name('reminders.cancel');

        /* Reports and analytics */

        Route::get(
            '/reports-analytics',
            [ReportsAnalyticsController::class, 'index']
        )->name('reports-analytics.index');

        /* Emergency hotline directory */

        Route::prefix('emergency-hotlines')
            ->name('emergency-hotlines.')
            ->group(function () {
                Route::get(
                    '/',
                    [EmergencyHotlineController::class, 'index']
                )->name('index');

                Route::get(
                    '/create',
                    [EmergencyHotlineController::class, 'create']
                )->name('create');

                Route::post(
                    '/',
                    [EmergencyHotlineController::class, 'store']
                )->name('store');

                Route::get(
                    '/{emergencyHotline}/edit',
                    [EmergencyHotlineController::class, 'edit']
                )->name('edit');

                Route::put(
                    '/{emergencyHotline}',
                    [EmergencyHotlineController::class, 'update']
                )->name('update');

                Route::patch(
                    '/{emergencyHotline}/activate',
                    [EmergencyHotlineController::class, 'activate']
                )->name('activate');

                Route::patch(
                    '/{emergencyHotline}/deactivate',
                    [EmergencyHotlineController::class, 'deactivate']
                )->name('deactivate');

                Route::get(
                    '/{emergencyHotline}',
                    [EmergencyHotlineController::class, 'show']
                )->name('show');

                Route::delete(
                    '/{emergencyHotline}',
                    [EmergencyHotlineController::class, 'destroy']
                )->name('destroy');
            });

        /* Residents */

        Route::resource('residents', ResidentController::class);

        /* Household routes: fixed routes before resource routes */

        Route::get(
            '/households/masterlist',
            [HouseholdController::class, 'masterlist']
        )->name('households.masterlist');

        Route::get(
            '/households/map',
            [HouseholdController::class, 'map']
        )->name('households.map');

        Route::get(
            '/households/map/data',
            [HouseholdController::class, 'mapData']
        )->name('households.map.data');

        Route::post(
            '/households/{household}/members',
            [HouseholdController::class, 'addMember']
        )->name('households.members.add');

        Route::delete(
            '/households/{household}/members/{member}',
            [HouseholdController::class, 'removeMember']
        )->name('households.members.remove');

        Route::patch(
            '/households/{household}/archive',
            [HouseholdController::class, 'archive']
        )->name('households.archive');

        Route::patch(
            '/households/{household}/restore',
            [HouseholdController::class, 'restore']
        )->name('households.restore');

        Route::resource('households', HouseholdController::class);

        /* Barangay documents */

        Route::resource('documents', DocumentRequestController::class);

        Route::post(
            '/documents/{document}/advance',
            [DocumentRequestController::class, 'advance']
        )->name('documents.advance');

        Route::get(
            '/documents/{document}/preview',
            [DocumentRequestController::class, 'preview']
        )->name('documents.preview');

        Route::get(
            '/documents/{document}/print',
            [DocumentRequestController::class, 'print']
        )->name('documents.print');

        /* Official document templates */

        Route::get(
            '/document-templates',
            [DocumentTemplateController::class, 'index']
        )->name('document-templates.index');

        Route::get(
            '/document-templates/create',
            [DocumentTemplateController::class, 'create']
        )->name('document-templates.create');

        Route::post(
            '/document-templates',
            [DocumentTemplateController::class, 'store']
        )->name('document-templates.store');

        Route::patch(
            '/document-templates/{documentTemplate}/deactivate',
            [DocumentTemplateController::class, 'deactivate']
        )->name('document-templates.deactivate');

        /* Reports and complaints */

        Route::resource('reports-complaints', ReportComplaintController::class)
            ->except(['destroy']);

        Route::patch(
            '/reports-complaints/{reports_complaint}/resolve',
            [ReportComplaintController::class, 'resolve']
        )->name('reports-complaints.resolve');

        Route::patch(
            '/reports-complaints/{reports_complaint}/close',
            [ReportComplaintController::class, 'close']
        )->name('reports-complaints.close');

        Route::get(
            '/reports-complaints/{reports_complaint}/subpoena/print',
            [ReportComplaintController::class, 'printSubpoena']
        )->name('reports-complaints.subpoena.print');

        /* Disaster events and assessments */

        Route::resource('disaster-events', DisasterEventController::class);

        Route::resource(
            'disaster-assessments',
            DisasterAssessmentController::class
        );

        Route::patch(
            '/disaster-assessments/{disaster_assessment}/verify',
            [DisasterAssessmentController::class, 'verify']
        )->name('disaster-assessments.verify');

        /* Relief assistance operations */

        Route::get(
            '/relief-vouchers',
            [ReliefVoucherController::class, 'index']
        )->name('relief-vouchers.index');

        Route::get(
            '/relief-vouchers/create',
            [ReliefVoucherController::class, 'createOperation']
        )->name('relief-assistance.create');

        Route::post(
            '/relief-vouchers',
            [ReliefVoucherController::class, 'storeOperation']
        )->name('relief-assistance.store');

        Route::get(
            '/relief-vouchers/masterlist',
            [ReliefVoucherController::class, 'masterlist']
        )->name('relief-assistance.masterlist');

        Route::get(
            '/relief-vouchers/masterlist/print',
            [ReliefVoucherController::class, 'masterlistPrint']
        )->name('relief-assistance.masterlist.print');

        Route::get(
            '/relief-vouchers/operations/{operation}',
            [ReliefVoucherController::class, 'operationShow']
        )->name('relief-assistance.show');

        Route::get(
            '/relief-vouchers/operations/{operation}/edit',
            [ReliefVoucherController::class, 'operationEdit']
        )->name('relief-assistance.edit');

        Route::put(
            '/relief-vouchers/operations/{operation}',
            [ReliefVoucherController::class, 'operationUpdate']
        )->name('relief-assistance.update');

        Route::post(
            '/relief-vouchers/generate',
            [ReliefVoucherController::class, 'generate']
        )->name('relief-vouchers.generate');

        Route::get(
            '/relief-vouchers/qr-verification',
            [ReliefVoucherController::class, 'qrVerification']
        )->name('relief-vouchers.qr-verification');

        Route::post(
            '/relief-vouchers/verify-qr',
            [ReliefVoucherController::class, 'verifyQr']
        )->name('relief-vouchers.verify-qr');

        Route::post(
            '/relief-vouchers/verification',
            [ReliefVoucherController::class, 'saveVerification']
        )->name('relief-assistance.verification.save');

        Route::get(
            '/relief-vouchers/operations/{operation}/beneficiaries',
            [ReliefVoucherController::class, 'beneficiaries']
        )->name('relief-assistance.beneficiaries');

        Route::post(
            '/relief-vouchers/operations/{operation}/beneficiaries/confirm',
            [ReliefVoucherController::class, 'confirmBeneficiaries']
        )->name('relief-assistance.beneficiaries.confirm');

        Route::get(
            '/relief-vouchers/operations/{operation}/beneficiaries/print',
            [ReliefVoucherController::class, 'beneficiariesPrint']
        )->name('relief-assistance.beneficiaries.print');

        Route::get(
            '/relief-vouchers/operations/{operation}/allocation',
            [ReliefVoucherController::class, 'allocation']
        )->name('relief-assistance.allocation');

        Route::post(
            '/relief-vouchers/operations/{operation}/allocation',
            [ReliefVoucherController::class, 'saveAllocation']
        )->name('relief-assistance.allocation.save');

        Route::get(
            '/relief-vouchers/vouchers',
            [ReliefVoucherController::class, 'vouchers']
        )->name('relief-assistance.vouchers');

        Route::get(
            '/relief-vouchers/{reliefVoucher}',
            [ReliefVoucherController::class, 'show']
        )->name('relief-vouchers.show');

        Route::get(
            '/relief-vouchers/{reliefVoucher}/print',
            [ReliefVoucherController::class, 'print']
        )->name('relief-vouchers.print');

        Route::patch(
            '/relief-vouchers/{reliefVoucher}/issue',
            [ReliefVoucherController::class, 'issue']
        )->name('relief-vouchers.issue');

        Route::patch(
            '/relief-vouchers/{reliefVoucher}/return',
            [ReliefVoucherController::class, 'returnToGenerated']
        )->name('relief-vouchers.return');

        Route::patch(
            '/relief-vouchers/{reliefVoucher}/distribute',
            [ReliefVoucherController::class, 'distribute']
        )->name('relief-vouchers.distribute');

        Route::patch(
            '/relief-vouchers/{reliefVoucher}/cancel',
            [ReliefVoucherController::class, 'cancel']
        )->name('relief-vouchers.cancel');

        Route::patch(
            '/relief-vouchers/{reliefVoucher}/claim',
            [ReliefVoucherController::class, 'claim']
        )->name('relief-vouchers.claim');

        /* Personal settings */

        Route::get('/settings', [SettingsController::class, 'index'])
            ->name('settings.index');

        Route::post(
            '/settings/google/link',
            [SettingsController::class, 'redirectToGoogleLink']
        )->name('settings.google.link');

        Route::put(
            '/settings/profile',
            [SettingsController::class, 'updateProfile']
        )->name('settings.profile.update');

        Route::put(
            '/settings/theme',
            [SettingsController::class, 'updateTheme']
        )->name('settings.theme.update');

        Route::put(
            '/settings/password',
            [SettingsController::class, 'updatePassword']
        )->name('settings.password.update');

        Route::delete(
            '/settings/avatar',
            [SettingsController::class, 'removeAvatar']
        )->name('settings.avatar.remove');
    });

    /*
    |--------------------------------------------------------------------------
    | Captain-Only Administration
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:captain')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Captain-Only Attendance Actions
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/attendance/personnel/{personnel}/reset-pin',
            [AttendanceManagementController::class, 'resetPin']
        )->name('attendance.personnel.reset-pin');

        Route::post(
            '/attendance/records/{attendanceRecord}/authorize-departure',
            [AttendanceManagementController::class, 'authorizeDeparture']
        )->name('attendance.records.authorize-departure');

        Route::get(
            '/audit-logs',
            [AuditLogController::class, 'index']
        )->name('audit-logs.index');

        Route::get(
            '/summary-reports',
            [SummaryReportController::class, 'index']
        )->name('summary-reports.index');

        Route::get(
            '/summary-reports/print',
            [SummaryReportController::class, 'print']
        )->name('summary-reports.print');

        /*
        |--------------------------------------------------------------------------
        | Captain One-Time Recovery Credential
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/settings/captain-recovery/generate',
            [SettingsController::class, 'generateCaptainRecoveryCredential']
        )->middleware('throttle:5,1')
          ->name('settings.captain-recovery.generate');

        Route::get('/users', [UserManagementController::class, 'index'])
            ->name('users.index');

        Route::get('/users/create', [UserManagementController::class, 'create'])
            ->name('users.create');

        Route::post('/users', [UserManagementController::class, 'store'])
            ->name('users.store');

        Route::get('/users/{user}', [UserManagementController::class, 'show'])
            ->name('users.show');

        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])
            ->name('users.edit');

        Route::put('/users/{user}', [UserManagementController::class, 'update'])
            ->name('users.update');

        /*
        |--------------------------------------------------------------------------
        | Captain-Authorized Secretary Password Recovery
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/users/{user}/authorize-password-reset',
            [UserManagementController::class, 'authorizeSecretaryPasswordReset']
        )->middleware('throttle:10,1')
          ->name('users.password-reset.authorize');

        /*
        |--------------------------------------------------------------------------
        | Captain-Authorized Secretary Authenticator Recovery
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/users/{user}/authorize-authenticator-recovery',
            [UserManagementController::class, 'authorizeSecretaryAuthenticatorRecovery']
        )->middleware('throttle:5,1')
          ->name('users.authenticator-recovery.authorize');

        Route::match(
            ['put', 'patch'],
            '/users/{user}/password',
            [UserManagementController::class, 'updatePassword']
        )->name('users.password.update');

        Route::patch(
            '/users/{user}/activate',
            [UserManagementController::class, 'activate']
        )->name('users.activate');

        Route::patch(
            '/users/{user}/deactivate',
            [UserManagementController::class, 'deactivate']
        )->name('users.deactivate');
    });
});
