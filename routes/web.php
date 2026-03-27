<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Firebase Messaging Service Worker (must be at root scope, config injected dynamically)
Route::get('/fcm-sw.js', function () {
    $c = config('firebase.web');
    $js = "importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');\n"
        . "importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');\n\n"
        . "firebase.initializeApp(" . json_encode([
            'apiKey'            => $c['api_key'] ?? '',
            'authDomain'        => $c['auth_domain'] ?? '',
            'projectId'         => $c['project_id'] ?? '',
            'storageBucket'     => $c['storage_bucket'] ?? '',
            'messagingSenderId' => $c['messaging_sender_id'] ?? '',
            'appId'             => $c['app_id'] ?? '',
        ]) . ");\n\n"
        . "var messaging = firebase.messaging();\n\n"
        . "messaging.onBackgroundMessage(function(payload) {\n"
        . "    var data = payload.data || {};\n"
        . "    var notification = payload.notification || {};\n"
        . "    var title = notification.title || data.title || 'New Notification';\n"
        . "    var options = {\n"
        . "        body: notification.body || data.body || '',\n"
        . "        icon: '/icon-192.png',\n"
        . "        badge: '/icon-192.png',\n"
        . "        tag: data.tag || 'crm-notification',\n"
        . "        requireInteraction: true,\n"
        . "        data: { url: data.url || data.click_action || '/' }\n"
        . "    };\n"
        . "    return self.registration.showNotification(title, options);\n"
        . "});\n\n"
        . "self.addEventListener('notificationclick', function(event) {\n"
        . "    event.notification.close();\n"
        . "    var url = (event.notification.data && event.notification.data.url) || '/';\n"
        . "    event.waitUntil(\n"
        . "        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {\n"
        . "            for (var i = 0; i < clientList.length; i++) {\n"
        . "                if (clientList[i].url.indexOf(url) !== -1 && 'focus' in clientList[i]) return clientList[i].focus();\n"
        . "            }\n"
        . "            if (clients.openWindow) return clients.openWindow(url);\n"
        . "        })\n"
        . "    );\n"
        . "});\n";
    return response($js, 200)->header('Content-Type', 'application/javascript')->header('Service-Worker-Allowed', '/');
});

// Installation Routes (must be before other routes)
Route::prefix('install')->group(function () {
    Route::get('/', [\App\Http\Controllers\InstallController::class, 'index'])->name('install.index');
    Route::post('/check-requirements', [\App\Http\Controllers\InstallController::class, 'checkRequirements'])->name('install.check-requirements');
    Route::post('/test-database', [\App\Http\Controllers\InstallController::class, 'testDatabase'])->name('install.test-database');
    Route::post('/install', [\App\Http\Controllers\InstallController::class, 'install'])->name('install.install');
});

// Developer API documentation — only accessible via unique URL (no link in app)
Route::get('/developer/docs/{access_key}', [\App\Http\Controllers\DeveloperDocsController::class, 'show'])->name('developer.docs');

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return match($user->role) {
            'admin'          => redirect('/dashboard'),
            'crm'            => redirect('/crm/dashboard'),
            'sales_manager'  => redirect('/sales-manager/dashboard'),
            'sales_head'     => redirect('/sales-head/dashboard'),
            'telecaller'     => redirect('/telecaller/dashboard'),
            default          => redirect('/login'),
        };
    }
    return redirect()->route('login');
});

// Legacy home (backup)
Route::get('/legacy-home', function () {
    return view('welcome');
})->name('legacy.home');

// PWA Install Page - single URL for 1-click install (Android) + notification permission
Route::get('/install-app', function () {
    return view('install-app');
})->name('install-app');

// PWA Test Page
Route::get('/pwa-test', function () {
    return view('pwa-test');
})->name('pwa.test')->middleware('restrict.test');

// PWA Notification Test Page (new lead assigned message + View Lead / See Task)
Route::get('/pwa-notification-test', function () {
    return view('pwa-notification-test');
})->name('pwa.notification-test')->middleware('restrict.test');

// Save Icons (from client-side canvas)
Route::post('/save-icons', function (Illuminate\Http\Request $request) {
    try {
        $icon192 = $request->input('icon192');
        $icon512 = $request->input('icon512');
        
        if (!$icon192 || !$icon512) {
            return response()->json([
                'success' => false,
                'message' => 'Icon data missing. Received: ' . ($icon192 ? 'icon192' : 'no icon192') . ', ' . ($icon512 ? 'icon512' : 'no icon512')
            ], 400);
        }
        
        // Decode base64 and save
        $icon192Data = base64_decode($icon192, true);
        $icon512Data = base64_decode($icon512, true);
        
        if ($icon192Data === false || $icon512Data === false) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid base64 data'
            ], 400);
        }
        
        $publicPath = public_path();
        
        // Ensure public directory is writable
        if (!is_writable($publicPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Public directory is not writable. Please check permissions.'
            ], 500);
        }
        
        // Save icon-192.png
        $saved192 = file_put_contents($publicPath . '/icon-192.png', $icon192Data);
        
        // Save icon-512.png
        $saved512 = file_put_contents($publicPath . '/icon-512.png', $icon512Data);
        
        if ($saved192 === false || $saved512 === false) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to write icon files. Check file permissions.'
            ], 500);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Icons saved successfully',
            'files' => [
                'icon-192.png' => file_exists($publicPath . '/icon-192.png') ? 'exists' : 'missing',
                'icon-512.png' => file_exists($publicPath . '/icon-512.png') ? 'exists' : 'missing'
            ],
            'sizes' => [
                'icon-192.png' => filesize($publicPath . '/icon-192.png'),
                'icon-512.png' => filesize($publicPath . '/icon-512.png')
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->middleware('web');

// Generate Icons (Server-side)
Route::get('/generate-icons-server', function () {
    $publicPath = public_path();
    $results = [];
    
    // Check if GD library is available
    if (!extension_loaded('gd')) {
        return response()->json([
            'error' => 'GD library not installed',
            'message' => 'Please use /create-icon.html to generate icons manually',
            'url' => url('/create-icon.html')
        ], 500);
    }
    
    function createIcon($size, $filename) {
        $img = imagecreatetruecolor($size, $size);
        $green = imagecolorallocate($img, 32, 90, 68); // #205A44
        $white = imagecolorallocate($img, 255, 255, 255);
        
        imagefilledrectangle($img, 0, 0, $size, $size, $green);
        
        // Use larger font for better visibility
        $fontSize = max(1, (int)($size / 8));
        $baseY = $size * 0.35;
        $crmY = $size * 0.65;
        $lineY = $size * 0.52;
        $lineHeight = max(2, (int)($size * 0.02));
        $lineWidth = $size * 0.6;
        $lineX = ($size - $lineWidth) / 2;
        
        // Draw line
        imagefilledrectangle($img, $lineX, $lineY, $lineX + $lineWidth, $lineY + $lineHeight, $white);
        
        // Draw text using built-in font (simple but works)
        $font = 5;
        $baseText = 'BASE';
        $crmText = 'CRM';
        
        $baseX = ($size - imagefontwidth($font) * strlen($baseText)) / 2;
        $crmX = ($size - imagefontwidth($font) * strlen($crmText)) / 2;
        
        imagestring($img, $font, $baseX, $baseY, $baseText, $white);
        imagestring($img, $font, $crmX, $crmY, $crmText, $white);
        
        $success = imagepng($img, $filename);
        imagedestroy($img);
        
        return $success && file_exists($filename);
    }
    
    // Create icon-192.png
    if (createIcon(192, $publicPath . '/icon-192.png')) {
        $results['icon-192.png'] = 'created';
    } else {
        $results['icon-192.png'] = 'failed';
    }
    
    // Create icon-512.png
    if (createIcon(512, $publicPath . '/icon-512.png')) {
        $results['icon-512.png'] = 'created';
    } else {
        $results['icon-512.png'] = 'failed';
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Icons generated',
        'results' => $results,
        'next_step' => 'Refresh /pwa-test page to verify'
    ]);
});

// Route to generate PWA icons
Route::get('/generate-icons', function () {
    return response()->json([
        'message' => 'Please visit /create-icon.html to generate icon files',
        'url' => url('/create-icon.html')
    ]);
});

// Debug/Test route
Route::get('/test-csrf', function () {
    return view('test-csrf');
})->name('test.csrf')->middleware('restrict.test');

Route::get('/test/verification-api', function () {
    return view('test-verification-api');
})->name('test.verification-api')->middleware('auth');

Route::get('/test/crm-auth', function () {
    return view('test-crm-auth');
})->name('test.crm-auth')->middleware('auth');

Route::get('/test/crm-error', function () {
    return view('test-crm-error');
})->name('test.crm-error')->middleware('auth');

Route::post('/test/generate-token', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not logged in'], 401);
    }
    
    // Create a new token
    $token = $user->createToken('test-token-' . time())->plainTextToken;
    
    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role->name ?? 'N/A',
        ]
    ]);
})->middleware('auth')->name('test.generate-token');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
Route::post('/login/firebase', [LoginController::class, 'loginWithFirebase'])->middleware('throttle:login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get'); // Fallback for expired sessions

// Password Reset via OTP
Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendOtp'])->name('password.send-otp')->middleware('throttle:5,1');
Route::get('/verify-otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showOtpForm'])->name('password.otp.form');
Route::post('/verify-otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'verifyOtp'])->name('password.verify-otp')->middleware('throttle:10,1');
Route::post('/resend-otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resendOtp'])->name('password.resend-otp')->middleware('throttle:3,1');
Route::get('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resetPassword'])->name('password.update');

// Sales Executive Routes (no auth required)
Route::get('/sales-executive/dashboard', [\App\Http\Controllers\SalesExecutiveController::class, 'dashboard'])->name('sales-executive.dashboard');
Route::get('/sales-executive/tasks', [\App\Http\Controllers\SalesExecutiveController::class, 'tasks'])->name('sales-executive.tasks');
Route::get('/sales-executive/leads', [\App\Http\Controllers\SalesExecutiveController::class, 'leads'])->name('sales-executive.leads');
Route::get('/sales-executive/reports', [\App\Http\Controllers\SalesExecutiveController::class, 'reports'])->name('sales-executive.reports');
Route::get('/sales-executive/verification-pending', [\App\Http\Controllers\SalesExecutiveController::class, 'verificationPending'])->name('sales-executive.verification-pending');
Route::get('/sales-executive/profile', [\App\Http\Controllers\SalesExecutiveController::class, 'profile'])->name('sales-executive.profile');

// Backward compatibility - redirect old telecaller routes to sales-executive
Route::get('/telecaller/dashboard', function() { return redirect()->route('sales-executive.dashboard'); })->name('telecaller.dashboard');
Route::get('/telecaller/tasks', function() { return redirect()->route('sales-executive.tasks'); })->name('telecaller.tasks');
Route::get('/telecaller/leads', function() { return redirect()->route('sales-executive.leads'); })->name('telecaller.leads');
Route::get('/telecaller/reports', function() { return redirect()->route('sales-executive.reports'); })->name('telecaller.reports');
Route::get('/telecaller/verification-pending', function() { return redirect()->route('sales-executive.verification-pending'); })->name('telecaller.verification-pending');
Route::get('/telecaller/profile', function() { return redirect()->route('sales-executive.profile'); })->name('telecaller.profile');

// Sales Head Routes (protected)
Route::middleware(['auth'])->prefix('sales-head')->name('sales-head.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SalesHeadController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/data', [\App\Http\Controllers\SalesHeadController::class, 'getDashboardData'])->name('dashboard.data');
});

// Sales Manager Routes (protected - Sales Head will be redirected)
Route::middleware(['auth'])->prefix('sales-manager')->name('sales-manager.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SalesManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/team', [\App\Http\Controllers\SalesManagerController::class, 'team'])->name('team');
    Route::get('/leads', [\App\Http\Controllers\SalesManagerController::class, 'leads'])->name('leads');
    Route::get('/lead-downloads', [\App\Http\Controllers\SalesManagerLeadDownloadController::class, 'index'])->name('lead-downloads.index');
    Route::post('/lead-downloads', [\App\Http\Controllers\SalesManagerLeadDownloadController::class, 'store'])->name('lead-downloads.store');
    Route::get('/lead-downloads/{leadDownloadRequest}/download', [\App\Http\Controllers\SalesManagerLeadDownloadController::class, 'download'])->name('lead-downloads.download');
    Route::get('/prospects', [\App\Http\Controllers\SalesManagerController::class, 'prospects'])->name('prospects');
    Route::get('/prospects/{id}', [\App\Http\Controllers\SalesManagerController::class, 'showProspect'])->name('prospects.show');
    Route::get('/prospects/{id}/edit', [\App\Http\Controllers\SalesManagerController::class, 'editProspect'])->name('prospects.edit');
    Route::put('/prospects/{id}', [\App\Http\Controllers\SalesManagerController::class, 'updateProspect'])->name('prospects.update');
    Route::delete('/prospects/{id}', [\App\Http\Controllers\SalesManagerController::class, 'destroyProspect'])->name('prospects.destroy');
    Route::get('/tasks', [\App\Http\Controllers\SalesManagerController::class, 'tasks'])->name('tasks');
    Route::get('/reports', [\App\Http\Controllers\SalesManagerController::class, 'reports'])->name('reports');
    Route::get('/profile', [\App\Http\Controllers\SalesManagerController::class, 'profile'])->name('profile');
    Route::get('/settings', [\App\Http\Controllers\SalesManagerController::class, 'settings'])->name('settings');
    Route::post('/settings/dashboard-visibility', [\App\Http\Controllers\SalesManagerController::class, 'updateDashboardSettings'])->name('settings.update');
    Route::get('/meetings', [\App\Http\Controllers\SalesManagerController::class, 'meetings'])->name('meetings');
    Route::get('/meetings/create', [\App\Http\Controllers\SalesManagerController::class, 'createMeeting'])->name('meetings.create');
        Route::get('/site-visits', [\App\Http\Controllers\SalesManagerController::class, 'siteVisits'])->name('site-visits');
        Route::get('/site-visits/create', [\App\Http\Controllers\SalesManagerController::class, 'createSiteVisit'])->name('site-visits.create');
    Route::get('/closed', [\App\Http\Controllers\SalesManagerController::class, 'closedLeads'])->name('closed');
});

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    // CRM dashboard (Sales Executive Performance): Sale Head ko chhod kar sabko dikhega (CRM bhi dashboard dikhega)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }
        
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Sales Executive / Telecaller: Telecaller-style dashboard
        if ($user->isTelecaller()) {
            return redirect()->route('sales-executive.dashboard');
        }
        // Sale Head: apna dashboard
        if ($user->isSalesHead()) {
            return redirect()->route('sales-head.dashboard');
        }
        // Assistant Sales Manager: Sales Manager dashboard (CRM dashboard nahi)
        if ($user->isAssistantSalesManager()) {
            return redirect()->route('sales-manager.dashboard');
        }
        // Manager (senior_manager): Sales Manager dashboard, CRM nahi
        if ($user->isSeniorManager()) {
            return redirect()->route('sales-manager.dashboard');
        }
        // Senior Manager (sales_manager, not Sales Head): Sales Manager dashboard
        if ($user->isSalesManager()) {
            return redirect()->route('sales-manager.dashboard');
        }
        // CRM + Admin + baaki sab: CRM dashboard open
        return view('crm.dashboard');
    })->name('dashboard');

    // Test: Lead assigned notification (1-click test for popup + email)
    Route::get('/test/lead-notification', [\App\Http\Controllers\TestLeadNotificationController::class, 'index'])->name('test.lead-notification')->middleware('role:admin,crm');
    Route::post('/test/lead-notification/simulate', [\App\Http\Controllers\TestLeadNotificationController::class, 'simulate'])->name('test.lead-notification.simulate')->middleware('role:admin,crm');

    // Test: PWA Push – select user and send test notification (Admin/CRM only)
    Route::get('/test/pwa-push', [\App\Http\Controllers\TestPwaPushController::class, 'index'])->name('test.pwa-push')->middleware('role:admin,crm');
    Route::get('/test/fcm-diagnose', [\App\Http\Controllers\TestPwaPushController::class, 'fcmDiagnose'])->name('test.fcm-diagnose')->middleware('role:admin,crm');
    Route::post('/test/fcm-generate-sw', [\App\Http\Controllers\TestPwaPushController::class, 'generateSw'])->name('test.fcm-generate-sw')->middleware('role:admin,crm');
    Route::post('/test/fcm-direct-send', [\App\Http\Controllers\TestPwaPushController::class, 'fcmDirectSend'])->name('test.fcm-direct-send')->middleware('role:admin,crm');
    Route::get('/test/pwa-push/diagnose', [\App\Http\Controllers\TestPwaPushController::class, 'diagnose'])->name('test.pwa-diagnose')->middleware('role:admin,crm');
    Route::post('/test/pwa-push/send', [\App\Http\Controllers\TestPwaPushController::class, 'send'])->name('test.pwa-push.send')->middleware('role:admin,crm');
    
    // Users Management
    Route::post('users/{user}/send-credentials-email', [\App\Http\Controllers\UserController::class, 'sendCredentialsEmail'])->name('users.send-credentials-email');
    Route::resource('users', \App\Http\Controllers\UserController::class);
    
    // Builders Management (CRM/Admin only)
    Route::resource('builders', \App\Http\Controllers\BuilderController::class)->middleware('role:crm,admin');
    
    // Closers Management (Admin/CRM/Sales Manager/Sales Head)
    Route::get('/closers', [\App\Http\Controllers\CloserController::class, 'index'])->name('closers.index')->middleware(['auth', 'role:admin,crm,sales_manager,sales_head']);
    
    // Unified routes for Prospects, Meetings, and Site Visits (Admin/CRM/Sales Manager/Sales Head)
    Route::middleware(['auth', 'role:admin,crm,sales_manager,sales_head'])->group(function () {
        Route::get('/prospects', [\App\Http\Controllers\SalesManagerController::class, 'prospects'])->name('prospects.index');
        Route::get('/meetings', [\App\Http\Controllers\SalesManagerController::class, 'meetings'])->name('meetings.index');
        Route::get('/meetings/create', [\App\Http\Controllers\SalesManagerController::class, 'createMeeting'])->name('meetings.create');
        Route::get('/site-visits', [\App\Http\Controllers\SalesManagerController::class, 'siteVisits'])->name('site-visits.index');
        Route::get('/site-visits/create', [\App\Http\Controllers\SalesManagerController::class, 'createSiteVisit'])->name('site-visits.create');
    });

    // Projects Management - View accessible to all, CUD only for Admin/CRM
    // Specific routes must come before parameterized routes
    Route::get('/projects', [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects-list', [\App\Http\Controllers\ProjectController::class, 'list'])->name('projects.list');
    
    Route::middleware('role:crm,admin')->group(function () {
        Route::get('/projects/create', [\App\Http\Controllers\ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [\App\Http\Controllers\ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}/edit', [\App\Http\Controllers\ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'destroy'])->name('projects.destroy');
    });
    
    // Parameterized routes come after specific routes
    Route::get('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');
    
    // Project pricing and unit types (for web forms)
    Route::post('/projects/{project}/pricing', [\App\Http\Controllers\Api\PricingController::class, 'update'])->middleware(['auth', 'role:crm,admin'])->name('projects.pricing.update');
    Route::post('/projects/{project}/unit-types', [\App\Http\Controllers\Api\UnitTypeController::class, 'store'])->middleware(['auth', 'role:crm,admin'])->name('projects.unit-types.store');
    Route::post('/projects/{project}/collaterals', [\App\Http\Controllers\Api\ProjectCollateralController::class, 'store'])->middleware(['auth', 'role:crm,admin'])->name('projects.collaterals.store');
    
    // Verifications (CRM/Admin/Sales Head)
    Route::middleware(['role:crm,admin,sales_head'])->prefix('crm')->name('crm.')->group(function () {
        Route::get('/verifications', [\App\Http\Controllers\Crm\VerificationController::class, 'index'])->name('verifications');

        // Target Management (CRM/Admin/Sales Head) - CRM-friendly URLs
        Route::resource('targets', \App\Http\Controllers\Admin\TargetController::class);
        Route::post('targets/bulk-set', [\App\Http\Controllers\Admin\TargetController::class, 'bulkSet'])->name('targets.bulk-set');
    });

    Route::middleware(['role:admin,crm'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/verifications', [\App\Http\Controllers\Crm\VerificationController::class, 'index'])->name('verifications');
    });

    // Finance Manager Routes
    Route::middleware(['auth', 'role:finance_manager'])->prefix('finance-manager')->name('finance-manager.')->group(function () {
        Route::get('/dashboard', function () {
            return view('finance-manager.dashboard');
        })->name('dashboard');
        Route::get('/incentives', function () {
            return view('finance-manager.incentives');
        })->name('incentives');
    });
    
    // Broadcast (Admin/CRM only)
    Route::middleware(['auth', 'role:admin,crm'])->group(function () {
        Route::get('/admin/broadcast', function () {
            return view('admin.broadcast');
        })->name('admin.broadcast');
    });

    // CRM danger: delete all leads (password required)
    Route::middleware(['auth', 'role:admin,crm'])->post('/crm/danger/delete-all-leads', [\App\Http\Controllers\Crm\CrmDangerController::class, 'deleteAllLeads'])->name('crm.danger.delete-all-leads');

    // Admin Dashboard (Admin only)
    // Integration Routes (Admin & CRM)
    Route::middleware(['auth', 'role:admin,crm'])->prefix('integrations')->name('integrations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\IntegrationController::class, 'index'])->name('index');
        Route::get('/sheet-integration', function () {
            return view('integrations.sheet-integration');
        })->name('sheet-integration');
        Route::get('/sheet-sync', function () {
            return view('integrations.sheet-sync');
        })->name('sheet-sync');
        Route::get('/email', function () {
            return view('integrations.coming-soon', ['integration' => 'Email']);
        })->name('email');
        Route::get('/calendar', function () {
            return view('integrations.coming-soon', ['integration' => 'Calendar']);
        })->name('calendar');
        // WhatsApp Integration Routes
        Route::get('/whatsapp', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'index'])->name('whatsapp');
        Route::post('/whatsapp/update', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'updateSettings'])->name('whatsapp.update');
        Route::post('/whatsapp/verify', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'verifyConnection'])->name('whatsapp.verify');
        Route::post('/whatsapp/test', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'testMessage'])->name('whatsapp.test');
        
        // WhatsApp Template Management Routes
        Route::post('/whatsapp/templates/create', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'createTemplate'])->name('whatsapp.templates.create');
        Route::get('/whatsapp/templates/{id}', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'getTemplate'])->name('whatsapp.templates.show');
        Route::delete('/whatsapp/templates/{id}', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'deleteTemplate'])->name('whatsapp.templates.delete');
        
        // WhatsApp Groups Routes (Optional)
        Route::get('/whatsapp/groups', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'getGroups'])->name('whatsapp.groups.index');
        Route::post('/whatsapp/groups', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'createGroup'])->name('whatsapp.groups.create');
        Route::put('/whatsapp/groups/{id}', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'updateGroup'])->name('whatsapp.groups.update');
        Route::delete('/whatsapp/groups/{id}', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'removeGroup'])->name('whatsapp.groups.delete');
        
        // WhatsApp Contacts Routes (Optional)
        Route::post('/whatsapp/contacts/import', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'importContact'])->name('whatsapp.contacts.import');
        Route::put('/whatsapp/contacts/{id}', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'updateContact'])->name('whatsapp.contacts.update');
        Route::delete('/whatsapp/contacts/{id}', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'removeContact'])->name('whatsapp.contacts.delete');
        Route::post('/whatsapp/contacts/bulk', [\App\Http\Controllers\Admin\WhatsAppIntegrationController::class, 'addContacts'])->name('whatsapp.contacts.bulk');
        
        // WhatsApp Debug Routes
        Route::get('/whatsapp/debug', [\App\Http\Controllers\Admin\WhatsAppDebugController::class, 'testConnection'])->name('whatsapp.debug');
        Route::post('/whatsapp/debug/post', [\App\Http\Controllers\Admin\WhatsAppDebugController::class, 'testPostEndpoint'])->name('whatsapp.debug.post');
        Route::post('/whatsapp/debug/curl', [\App\Http\Controllers\Admin\WhatsAppDebugController::class, 'testRawCurl'])->name('whatsapp.debug.curl');

        // WhatsApp Quick Test Routes
        Route::get('/whatsapp/quick-test', [\App\Http\Controllers\Admin\WhatsAppTestController::class, 'quickTest'])->name('whatsapp.quick-test');
        Route::post('/whatsapp/quick-test/send', [\App\Http\Controllers\Admin\WhatsAppTestController::class, 'sendQuickTest'])->name('whatsapp.quick-test.send');
        
        // Pabbly Integration Routes
        Route::get('/pabbly', [\App\Http\Controllers\Admin\PabblyIntegrationController::class, 'index'])->name('pabbly');
        Route::post('/pabbly/update', [\App\Http\Controllers\Admin\PabblyIntegrationController::class, 'updateSettings'])->name('pabbly.update');
        Route::post('/pabbly/test', [\App\Http\Controllers\Admin\PabblyIntegrationController::class, 'testWebhook'])->name('pabbly.test');
        Route::get('/pabbly/logs', [\App\Http\Controllers\Admin\PabblyIntegrationController::class, 'getWebhookLogs'])->name('pabbly.logs');

        // MCube Integration Routes
        Route::prefix('mcube')->name('mcube.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\McubeIntegrationController::class, 'index'])->name('index');
            Route::post('/settings', [\App\Http\Controllers\Admin\McubeIntegrationController::class, 'updateSettings'])->name('settings.update');
            Route::get('/generate-token', [\App\Http\Controllers\Admin\McubeIntegrationController::class, 'generateToken'])->name('generate-token');
            Route::post('/test', [\App\Http\Controllers\Admin\McubeIntegrationController::class, 'testWebhook'])->name('test');
        });
        
        // Google Sheets Integration Route (redirects to lead import page)
        Route::get('/google-sheets', function () {
            return redirect()->route('lead-import.index');
        })->name('google-sheets');
        
        // Form Integration Routes (Google Sheets Form Integration)
        Route::prefix('form-integration')->name('form-integration.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'create'])->name('create');
            Route::post('/step1', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'storeStep1'])->name('store-step1');
            Route::get('/step2/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'step2'])->name('step2');
            Route::post('/step2/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'storeStep2'])->name('store-step2');
            Route::get('/step3/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'step3'])->name('step3');
            Route::post('/step3/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'storeStep3'])->name('store-step3');
            Route::get('/step4/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'step4'])->name('step4');
            Route::post('/step4/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'storeStep4'])->name('store-step4');
            Route::get('/step5/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'step5'])->name('step5');
            Route::post('/step5/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'storeStep5'])->name('store-step5');
            Route::get('/step6/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'step6'])->name('step6');
            Route::post('/auto-detect-columns', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'autoDetectColumns'])->name('auto-detect-columns');
            Route::get('/generate-script/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'generateScript'])->name('generate-script');
            Route::post('/test/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'test'])->name('test');
            Route::get('/template', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'getFormTemplate'])->name('template');
            Route::post('/toggle/{id}', [\App\Http\Controllers\Admin\FormIntegrationController::class, 'toggle'])->name('toggle');
        });
        
        // Meta Sheet Integration Routes (Meta/Facebook only)
        Route::prefix('meta-sheet')->name('meta-sheet.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MetaSheetController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\MetaSheetController::class, 'create'])->name('create');
            Route::post('/step1', [\App\Http\Controllers\Admin\MetaSheetController::class, 'storeStep1'])->name('store-step1');
            Route::get('/step2/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'step2'])->name('step2');
            Route::post('/step2/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'storeStep2'])->name('store-step2');
            Route::get('/step3/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'step3'])->name('step3');
            Route::post('/step3/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'storeStep3'])->name('store-step3');
            Route::get('/step4/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'step4'])->name('step4');
            Route::post('/step4/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'storeStep4'])->name('store-step4');
            Route::get('/step5/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'step5'])->name('step5');
            Route::post('/step5/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'storeStep5'])->name('store-step5');
            Route::get('/step6/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'step6'])->name('step6');
            Route::post('/auto-detect-columns', [\App\Http\Controllers\Admin\MetaSheetController::class, 'autoDetectColumns'])->name('auto-detect-columns');
            Route::post('/create-custom-field', [\App\Http\Controllers\Admin\MetaSheetController::class, 'createCustomField'])->name('create-custom-field');
            Route::post('/save-draft/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'saveDraft'])->name('save-draft');
            Route::get('/generate-script/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'generateScript'])->name('generate-script');
            Route::post('/test/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'test'])->name('test');
            Route::post('/sync/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'sync'])->name('sync');
            Route::post('/delete/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'delete'])->name('delete');
            Route::post('/toggle/{id}', [\App\Http\Controllers\Admin\MetaSheetController::class, 'toggle'])->name('toggle');
        });

        // Google Sheet import cron monitor
        Route::get('/google-sheet-import-monitor', [\App\Http\Controllers\Admin\GoogleSheetImportMonitorController::class, 'index'])
            ->name('google-sheet-import-monitor');
        
        Route::get('/facebook', function () {
            return view('integrations.coming-soon', ['integration' => 'Facebook Meta']);
        })->name('facebook');

        // Facebook Lead Ads (standalone - direct webhook + Graph API; does not touch Meta Sheet / Form Integration)
        Route::prefix('facebook-lead-ads')->name('facebook-lead-ads.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'index'])->name('index');
            Route::get('/settings', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'settings'])->name('settings');
            Route::post('/settings', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'updateSettings'])->name('settings.update');
            Route::post('/test-connection', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'testConnection'])->name('test-connection');
            Route::get('/forms', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'forms'])->name('forms');
            Route::get('/mapping/{formId}', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'mapping'])->name('mapping');
            Route::post('/save-mapping', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'saveMapping'])->name('save-mapping');
            Route::post('/custom-field', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'storeCustomField'])->name('custom-field');
            Route::post('/add-page', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'addPage'])->name('add-page');
            Route::post('/remove-page', [\App\Http\Controllers\Admin\FacebookLeadAdsController::class, 'removePage'])->name('remove-page');
        });
        Route::get('/magic-bricks', function () {
            return view('integrations.coming-soon', ['integration' => 'Magic Bricks']);
        })->name('magic-bricks');
        Route::get('/housing', function () {
            return view('integrations.coming-soon', ['integration' => 'Housing']);
        })->name('housing');
        Route::get('/99acres', function () {
            return view('integrations.coming-soon', ['integration' => '99acres']);
        })->name('99acres');
        Route::get('/configuration', function () {
            return view('integrations.coming-soon', ['integration' => 'Configuration']);
        })->name('configuration');
    });
    
    // Dynamic Forms Management (Admin only)
    Route::middleware(['role:admin'])->prefix('admin/forms')->name('admin.forms.')->group(function () {
        Route::get('/test-field-type', function () {
            return view('admin.forms.test-field-type');
        })->name('test-field-type');
        Route::get('/existing-preview/{formPath}', [\App\Http\Controllers\Admin\DynamicFormController::class, 'previewExistingForm'])->name('existing-preview')->where('formPath', '.+');
        Route::get('/', [\App\Http\Controllers\Admin\DynamicFormController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\DynamicFormController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\DynamicFormController::class, 'store'])->name('store');
        Route::get('/{dynamicForm}/edit', [\App\Http\Controllers\Admin\DynamicFormController::class, 'edit'])->name('edit');
        Route::put('/{dynamicForm}', [\App\Http\Controllers\Admin\DynamicFormController::class, 'update'])->name('update');
        Route::delete('/{dynamicForm}', [\App\Http\Controllers\Admin\DynamicFormController::class, 'destroy'])->name('destroy');
    });

    // Lead Form Builder (Admin only)
    Route::middleware(['role:admin'])->prefix('admin/lead-form-builder')->name('admin.lead-form-builder.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LeadFormBuilderController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\LeadFormBuilderController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\LeadFormBuilderController::class, 'store'])->name('store');
        Route::get('/{leadFormField}', [\App\Http\Controllers\Admin\LeadFormBuilderController::class, 'show'])->name('show');
        Route::get('/{leadFormField}/edit', [\App\Http\Controllers\Admin\LeadFormBuilderController::class, 'edit'])->name('edit');
        Route::put('/{leadFormField}', [\App\Http\Controllers\Admin\LeadFormBuilderController::class, 'update'])->name('update');
        Route::delete('/{leadFormField}', [\App\Http\Controllers\Admin\LeadFormBuilderController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [\App\Http\Controllers\Admin\LeadFormBuilderController::class, 'reorder'])->name('reorder');
        Route::post('/{leadFormField}/toggle-active', [\App\Http\Controllers\Admin\LeadFormBuilderController::class, 'toggleActive'])->name('toggle-active');
    });

    // Automation Rules (Admin only)
    Route::middleware(['auth', 'role:admin'])->prefix('admin/automation')->name('admin.automation.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AutomationController::class, 'index'])->name('index');
        Route::get('/cnp', [\App\Http\Controllers\Admin\AsmCnpAutomationController::class, 'index'])->name('cnp.index');
        Route::post('/cnp', [\App\Http\Controllers\Admin\AsmCnpAutomationController::class, 'update'])->name('cnp.update');
        Route::get('/create', [\App\Http\Controllers\Admin\AutomationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AutomationController::class, 'store'])->name('store');
        Route::get('/{rule}/edit', [\App\Http\Controllers\Admin\AutomationController::class, 'edit'])->name('edit');
        Route::put('/{rule}', [\App\Http\Controllers\Admin\AutomationController::class, 'update'])->name('update');
        Route::delete('/{rule}', [\App\Http\Controllers\Admin\AutomationController::class, 'destroy'])->name('destroy');
        Route::post('/{rule}/toggle', [\App\Http\Controllers\Admin\AutomationController::class, 'toggle'])->name('toggle');
        Route::get('/{rule}/history', [\App\Http\Controllers\Admin\AutomationController::class, 'history'])->name('history');
    });

    // Deployment Routes (Admin only)
    Route::middleware(['auth', 'role:admin'])->prefix('admin/deploy')->name('admin.deploy.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DeploymentController::class, 'index'])->name('index');
        Route::post('/deploy', [\App\Http\Controllers\Admin\DeploymentController::class, 'deploy'])->name('deploy');
        Route::get('/status', [\App\Http\Controllers\Admin\DeploymentController::class, 'checkGitStatus'])->name('status');
        Route::get('/logs', [\App\Http\Controllers\Admin\DeploymentController::class, 'getLogs'])->name('logs');
    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/data', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'getDashboardData'])->name('dashboard.data');
        Route::get('/profile', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'profile'])->name('profile');
        Route::get('/lead-download-requests', [\App\Http\Controllers\Admin\LeadDownloadRequestController::class, 'index'])->name('lead-download-requests.index');
        Route::post('/lead-download-requests/{leadDownloadRequest}/approve', [\App\Http\Controllers\Admin\LeadDownloadRequestController::class, 'approve'])->name('lead-download-requests.approve');
        Route::post('/lead-download-requests/{leadDownloadRequest}/reject', [\App\Http\Controllers\Admin\LeadDownloadRequestController::class, 'reject'])->name('lead-download-requests.reject');
        
        // Flow Testing (Admin and CRM)
        Route::get('/flow-test', [\App\Http\Controllers\Admin\FlowTestController::class, 'index'])->name('flow-test');
        
        // Company Settings Routes
        Route::prefix('company-settings')->name('company-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'index'])->name('index');
            Route::post('/company-profile', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'updateCompanyProfile'])->name('company-profile.update');
            Route::post('/branding', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'updateBranding'])->name('branding.update');
            Route::post('/apply-template', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'applyTemplate'])->name('apply-template');
            Route::post('/upload-file', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'uploadFile'])->name('upload-file');
            Route::delete('/file/{id}', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'deleteFile'])->name('file.delete');
            Route::get('/api/settings', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'getSettings'])->name('api.settings');
            Route::get('/preview', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'previewBranding'])->name('preview');
        });
        
        // System Settings Routes
        Route::prefix('system-settings')->name('system-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('index');
            Route::post('/maintenance/toggle', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'toggleMaintenanceMode'])->name('maintenance.toggle');
            Route::post('/user-notifications/update', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updateUserNotificationSettings'])->name('user-notifications.update');
            Route::get('/test-email', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'testEmailPage'])->name('test-email');
            Route::post('/test-email', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'sendTestEmail'])->name('test-email.send');
            Route::get('/mail-debug', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'mailDebugPage'])->name('mail-debug');
            Route::post('/mail-debug/send', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'sendTestEmailDebug'])->name('mail-debug.send');
            Route::post('/files/upload', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'uploadFiles'])->name('files.upload');
            Route::post('/files/deploy', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'deployFiles'])->name('files.deploy');
            Route::post('/migrations/run', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'runMigrations'])->name('migrations.run');
            Route::post('/command/run', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'runCommand'])->name('command.run');
            // Database and Environment Settings
            Route::post('/database/test', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'testDatabaseConnection'])->name('database.test');
            Route::post('/database/update', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updateDatabaseSettings'])->name('database.update');
            Route::get('/env/get', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'getEnvSettings'])->name('env.get');
            Route::post('/env/update', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updateEnvSettings'])->name('env.update');
        });
        
    });
    
    // Target Management (Admin/CRM/Sales Head)
    Route::middleware(['role:admin,crm,sales_head'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('targets', \App\Http\Controllers\Admin\TargetController::class);
        Route::post('targets/bulk-set', [\App\Http\Controllers\Admin\TargetController::class, 'bulkSet'])->name('targets.bulk-set');
        Route::get('/dead-leads', [\App\Http\Controllers\Admin\DeadLeadsController::class, 'index'])->name('dead-leads');
    });
    
    // CRM Automation Routes (CRM/Admin only)
    Route::middleware(['role:crm,admin'])->prefix('crm/automation')->name('crm.automation.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Crm\AutomationController::class, 'index'])->name('index');
        Route::get('/leads/create', [\App\Http\Controllers\Crm\LeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [\App\Http\Controllers\Crm\LeadController::class, 'store'])->name('leads.store');
        Route::get('/rules', [\App\Http\Controllers\Crm\AutomationController::class, 'index'])->name('rules');
        Route::post('/rules', [\App\Http\Controllers\Crm\AutomationController::class, 'index'])->name('rules.store');
        Route::post('/import/csv', [\App\Http\Controllers\Crm\AutomationController::class, 'index'])->name('import.csv');
        Route::get('/import/csv/preview', [\App\Http\Controllers\Crm\AutomationController::class, 'index'])->name('import.csv.preview');
    });

    // Leads Management
    Route::resource('leads', \App\Http\Controllers\LeadController::class);
    Route::get('/leads/{lead}/short-details', [\App\Http\Controllers\LeadController::class, 'shortDetails'])->name('leads.short-details');
    
    // Call Logs Routes
    Route::prefix('calls')->name('calls.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CallLogController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\CallLogController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\CallLogController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\CallLogController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\CallLogController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\CallLogController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\CallLogController::class, 'destroy'])->name('destroy');
        Route::get('/statistics', [\App\Http\Controllers\CallLogController::class, 'statistics'])->name('statistics');
        Route::get('/statistics/data', [\App\Http\Controllers\CallLogController::class, 'getStatistics'])->name('statistics.data');
        Route::get('/export/csv', [\App\Http\Controllers\CallLogController::class, 'exportCsv'])->name('export.csv');
    });
    
    // Export Routes (Admin, CRM, Sales Manager, Sales Head)
    Route::middleware(['role:admin,crm,sales_manager,sales_head'])->prefix('export')->name('export.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ExportController::class, 'index'])->name('index');
        Route::post('/leads', [\App\Http\Controllers\ExportController::class, 'exportLeads'])->name('leads');
        Route::post('/prospects', [\App\Http\Controllers\ExportController::class, 'exportProspects'])->name('prospects');
        Route::post('/meetings', [\App\Http\Controllers\ExportController::class, 'exportMeetings'])->name('meetings');
        Route::post('/site-visits', [\App\Http\Controllers\ExportController::class, 'exportSiteVisits'])->name('site-visits');
        Route::post('/closed-leads', [\App\Http\Controllers\ExportController::class, 'exportClosedLeads'])->name('closed-leads');
        Route::post('/dead-leads', [\App\Http\Controllers\ExportController::class, 'exportDeadLeads'])->name('dead-leads');
        Route::post('/by-project', [\App\Http\Controllers\ExportController::class, 'exportByProject'])->name('by-project');
    });
    
    // Lead Import Routes (CRM and Admin only)
    Route::middleware(['role:crm,admin'])->prefix('lead-import')->name('lead-import.')->group(function () {
        // Dashboard
        Route::get('/', [\App\Http\Controllers\LeadImportController::class, 'index'])->name('index');
        
        // Google Sheets Config
        Route::get('/google-sheets/config', [\App\Http\Controllers\LeadImportController::class, 'getGoogleSheetsConfig'])->name('google-sheets.config');
        Route::post('/google-sheets/config', [\App\Http\Controllers\LeadImportController::class, 'saveGoogleSheetsConfig'])->name('google-sheets.config.save');
        Route::get('/google-sheets/configs', [\App\Http\Controllers\LeadImportController::class, 'getAllGoogleSheetsConfigs'])->name('google-sheets.configs');
        Route::delete('/google-sheets/config/{id}', [\App\Http\Controllers\LeadImportController::class, 'deleteGoogleSheetsConfig'])->name('google-sheets.config.delete');
        Route::post('/google-sheets/fetch-headers', [\App\Http\Controllers\LeadImportController::class, 'fetchSheetHeaders'])->name('google-sheets.fetch-headers');

        // Sync
        Route::post('/google-sheets/sync', [\App\Http\Controllers\LeadImportController::class, 'syncGoogleSheets'])->name('google-sheets.sync');
        
        // CSV Import (existing functionality moved here)
        Route::get('/csv', [\App\Http\Controllers\LeadImportController::class, 'showCsvForm'])->name('csv');
        Route::post('/csv', [\App\Http\Controllers\LeadImportController::class, 'importCsv'])->name('csv.import');
        Route::post('/csv/preview', [\App\Http\Controllers\LeadImportController::class, 'previewCsv'])->name('csv.preview');
        
        // History
        Route::get('/history', [\App\Http\Controllers\LeadImportController::class, 'history'])->name('history');
    });
    
});

// Admin Impersonation (Session based)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/impersonate/{user}', function (\App\Models\User $user) {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Aap khud ko impersonate nahi kar sakte!');
        }
        // Admin ka original ID save karo
        session(['impersonating_original_id' => auth()->id()]);
        // Us user ke roop mein login karo
        auth()->login($user);
        // Role ke hisaab se redirect
        $slug = $user->role->slug ?? $user->role ?? '';
        return match(true) {
            $slug === 'crm'                                          => redirect('/crm/dashboard'),
            $slug === 'sales_head'                                   => redirect('/sales-head/dashboard'),
            in_array($slug, ['sales_manager','senior_manager',
                'assistant_sales_manager'])                          => redirect('/sales-manager/dashboard'),
            $slug === 'sales_executive'                              => redirect('/telecaller/dashboard'),
            $slug === 'hr_manager'                                   => redirect('/dashboard'),
            $slug === 'finance_manager'                              => redirect('/dashboard'),
            default                                                  => redirect('/dashboard'),
        };
    })->name('impersonate.start');

});

// Stop impersonation - admin middleware nahi chahiye kyunki current user admin nahi hota
Route::middleware(['auth'])->get('/impersonate/stop', function () {
    $originalId = session('impersonating_original_id');
    if ($originalId) {
        $admin = \App\Models\User::find($originalId);
        if ($admin) {
            auth()->login($admin);
            session()->forget('impersonating_original_id');
        }
    }
    return redirect('/users');
})->name('impersonate.stop');

// Lead Assignment Routes
Route::middleware(['auth', 'role:crm,admin'])->prefix('lead-assignment')->name('lead-assignment.')->group(function () {
    Route::get('/', [\App\Http\Controllers\LeadAssignmentController::class, 'index'])->name('index');
    Route::get('/unassigned', [\App\Http\Controllers\LeadAssignmentController::class, 'getUnassignedLeads'])->name('unassigned');
    Route::post('/assign', [\App\Http\Controllers\LeadAssignmentController::class, 'assignLeads'])->name('assign');
    Route::post('/delete', [\App\Http\Controllers\LeadAssignmentController::class, 'deleteLeads'])->name('delete');
    Route::get('/telecaller-stats', [\App\Http\Controllers\LeadAssignmentController::class, 'getTelecallerStats'])->name('telecaller-stats');
    
    // Telecaller Limits
    Route::get('/telecaller-limits', [\App\Http\Controllers\TelecallerLimitController::class, 'index'])->name('telecaller-limits');
    Route::post('/telecaller-limits/save', [\App\Http\Controllers\TelecallerLimitController::class, 'saveDailyLimit'])->name('telecaller-limits.save');
    Route::get('/telecaller-limits/api', [\App\Http\Controllers\TelecallerLimitController::class, 'getDailyLimits'])->name('telecaller-limits.api');
    
    // Sheet Assignments
    Route::get('/sheet-assignments', [\App\Http\Controllers\SheetAssignmentController::class, 'index'])->name('sheet-assignments');
    Route::post('/sheet-assignments/assign', [\App\Http\Controllers\SheetAssignmentController::class, 'assignSheetToTelecaller'])->name('sheet-assignments.assign');
    Route::post('/sheet-assignments/config', [\App\Http\Controllers\SheetAssignmentController::class, 'updateSheetConfig'])->name('sheet-assignments.config');
    Route::post('/sheet-assignments/toggle-auto', [\App\Http\Controllers\SheetAssignmentController::class, 'toggleAutoAssign'])->name('sheet-assignments.toggle-auto');
    
    // Telecaller Status
    Route::get('/telecaller-status', [\App\Http\Controllers\TelecallerStatusController::class, 'index'])->name('telecaller-status');
    Route::post('/telecaller-status/update', [\App\Http\Controllers\TelecallerStatusController::class, 'updateStatus'])->name('telecaller-status.update');
    Route::get('/telecaller-status/api', [\App\Http\Controllers\TelecallerStatusController::class, 'getStatus'])->name('telecaller-status.api');
});

// Task Routes (public - no auth required)
Route::prefix('tasks')->name('tasks.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\TaskController::class, 'index'])->name('index');
    Route::get('/{task}', [\App\Http\Controllers\TaskController::class, 'show'])->name('show');
    Route::put('/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('update');
    Route::delete('/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('destroy');
    Route::post('/{task}/complete', [\App\Http\Controllers\TaskController::class, 'complete'])->name('complete');
    Route::post('/{task}/update-lead', [\App\Http\Controllers\TaskController::class, 'updateLeadAfterCall'])->name('update-lead');
    Route::post('/{task}/reschedule', [\App\Http\Controllers\TaskController::class, 'reschedule'])->name('reschedule');
    Route::post('/{task}/duplicate', [\App\Http\Controllers\TaskController::class, 'duplicate'])->name('duplicate');
    Route::post('/{task}/cancel', [\App\Http\Controllers\TaskController::class, 'cancel'])->name('cancel');
    Route::get('/{task}/activities', [\App\Http\Controllers\TaskController::class, 'activities'])->name('activities');
    
    // Attachments
    Route::post('/{task}/attachments', [\App\Http\Controllers\TaskController::class, 'uploadAttachment'])->name('attachments.upload');
    Route::delete('/{task}/attachments/{attachment}', [\App\Http\Controllers\TaskController::class, 'deleteAttachment'])->name('attachments.delete');
    Route::get('/{task}/attachments/{attachment}/download', [\App\Http\Controllers\TaskController::class, 'downloadAttachment'])->name('attachments.download');
});

// API routes for tasks
Route::prefix('api/tasks')->middleware('auth:sanctum')->group(function () {
    Route::put('/{task}/status', [\App\Http\Controllers\Api\TaskController::class, 'updateStatus']);
    Route::put('/{task}/reschedule', [\App\Http\Controllers\Api\TaskController::class, 'reschedule']);
    Route::get('/kanban', [\App\Http\Controllers\Api\TaskController::class, 'kanban']);
    Route::get('/calendar', [\App\Http\Controllers\Api\TaskController::class, 'calendar']);
    Route::post('/bulk-action', [\App\Http\Controllers\Api\TaskController::class, 'bulkAction']);
});

// Support Ticket Routes (all authenticated users)
Route::middleware(['auth'])->prefix('support')->name('support.')->group(function () {
    Route::get('/',         [\App\Http\Controllers\SupportTicketController::class, 'index'])->name('index');
    Route::get('/create',   [\App\Http\Controllers\SupportTicketController::class, 'create'])->name('create');
    Route::post('/',        [\App\Http\Controllers\SupportTicketController::class, 'store'])->name('store');
    Route::get('/{ticket}', [\App\Http\Controllers\SupportTicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [\App\Http\Controllers\SupportTicketController::class, 'reply'])->name('reply');
});

// Admin Support Ticket Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin/support')->name('admin.support.')->group(function () {
    Route::get('/',                         [\App\Http\Controllers\Admin\SupportController::class, 'index'])->name('index');
    Route::get('/{ticket}',                 [\App\Http\Controllers\Admin\SupportController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply',          [\App\Http\Controllers\Admin\SupportController::class, 'reply'])->name('reply');
    Route::patch('/{ticket}/status',        [\App\Http\Controllers\Admin\SupportController::class, 'updateStatus'])->name('update-status');
    Route::delete('/{ticket}',              [\App\Http\Controllers\Admin\SupportController::class, 'destroy'])->name('destroy');
});

// WhatsApp Chat Routes
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [\App\Http\Controllers\WhatsAppChatController::class, 'index'])->name('index');
    Route::get('/conversations', [\App\Http\Controllers\WhatsAppChatController::class, 'getConversations'])->name('conversations.index');
    Route::post('/conversations', [\App\Http\Controllers\WhatsAppChatController::class, 'createConversation'])->name('conversations.create');
    Route::get('/conversations/{id}', [\App\Http\Controllers\WhatsAppChatController::class, 'getConversation'])->name('conversations.show');
    Route::post('/messages', [\App\Http\Controllers\WhatsAppChatController::class, 'sendMessage'])->name('messages.send');
    Route::post('/messages/template', [\App\Http\Controllers\WhatsAppChatController::class, 'sendTemplateMessage'])->name('messages.template');
    Route::get('/templates', [\App\Http\Controllers\WhatsAppChatController::class, 'getTemplates'])->name('templates.index');
    Route::post('/templates/sync', [\App\Http\Controllers\WhatsAppChatController::class, 'syncTemplates'])->name('templates.sync');
    Route::post('/conversations/{id}/sync-messages', [\App\Http\Controllers\WhatsAppChatController::class, 'syncMessages'])->name('conversations.sync-messages');
    Route::put('/conversations/{id}/read', [\App\Http\Controllers\WhatsAppChatController::class, 'markAsRead'])->name('conversations.read');
    Route::delete('/conversations/{id}', [\App\Http\Controllers\WhatsAppChatController::class, 'deleteConversation'])->name('conversations.delete');
});
