<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Realtor CRM</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            height: 100vh;
        }

        /* Mobile view - Enable scrolling */
        @media (max-width: 968px) {
            body {
                height: auto;
                min-height: 100vh;
                overflow: auto;
                overflow-x: hidden;
            }

            .login-container {
                height: auto;
                min-height: 100vh;
            }
        }

        /* Left Section - Gradient with Analytics */
        .left-section {
            flex: 0 0 40%;
            background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 50px;
            color: white;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .analytics-widgets {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            width: 100%;
            max-width: 500px;
            margin-bottom: 40px;
            z-index: 1;
        }

        .widget-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .widget-card.large {
            grid-column: 1 / -1;
        }

        .widget-title {
            font-size: 14px;
            font-weight: 500;
            opacity: 0.9;
            margin-bottom: 16px;
        }

        .chart-controls {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .chart-btn {
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .chart-btn.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }

        .bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            height: 120px;
        }

        .bar {
            flex: 1;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 4px 4px 0 0;
            min-height: 20px;
            transition: all 0.3s;
        }

        .bar:nth-child(1) { height: 60%; }
        .bar:nth-child(2) { height: 80%; }
        .bar:nth-child(3) { height: 45%; }
        .bar:nth-child(4) { height: 90%; }
        .bar:nth-child(5) { height: 70%; }
        .bar:nth-child(6) { height: 55%; }
        .bar:nth-child(7) { height: 75%; }

        .bar-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 11px;
            opacity: 0.8;
        }

        .progress-circle {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            position: relative;
        }

        .circle-bg {
            fill: none;
            stroke: rgba(255, 255, 255, 0.2);
            stroke-width: 8;
        }

        .circle-progress {
            fill: none;
            stroke: white;
            stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 251.2;
            stroke-dashoffset: 145.7;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dashoffset 0.5s;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: 700;
        }

        .left-content {
            z-index: 1;
            text-align: center;
        }

        .headline {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .subtext {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 500px;
        }

        /* Right Section - Login Form */
        .right-section {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow-y: auto;
        }

        .login-form-container {
            width: 100%;
            max-width: 420px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(6, 58, 28, 0.3);
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #063A1C;
            margin-bottom: 8px;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            color: #063A1C;
            margin-bottom: 8px;
        }

        .welcome-subtitle {
            font-size: 14px;
            color: #B3B5B4;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #2d3748;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #E5DED4;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus {
            outline: none;
            border-color: #205A44;
            box-shadow: 0 0 0 3px rgba(32, 90, 68, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #205A44;
        }

        .btn-signin {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(6, 58, 28, 0.4);
        }

        .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 58, 28, 0.5);
        }

        .btn-signin:active {
            transform: translateY(0);
        }

        .btn-install-app {
            width: 100%;
            padding: 12px 14px;
            margin-top: 12px;
            background: transparent;
            color: #205A44;
            border: 2px solid #205A44;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-install-app:hover {
            background: rgba(32, 90, 68, 0.08);
            transform: translateY(-1px);
        }
        .btn-install-app:active { transform: translateY(0); }
        .btn-install-app:disabled { opacity: 0.7; cursor: not-allowed; }
        #installAppStatus { font-size: 13px; color: #205A44; margin-top: 10px; min-height: 18px; line-height: 1.4; padding: 0 4px; }
        #installAppStatus.error { color: #dc2626; }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c33;
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .login-container {
                flex-direction: column;
            }

            .left-section {
                flex: 0 0 auto;
                min-height: 40vh;
                padding: 40px 30px;
            }

            .headline {
                font-size: 28px;
            }

            .analytics-widgets {
                max-width: 100%;
            }

            .right-section {
                flex: 1;
                min-height: auto;
            }
        }

        @media (max-width: 640px) {
            .left-section {
                padding: 30px 20px;
            }

            .headline {
                font-size: 24px;
            }

            .subtext {
                font-size: 14px;
            }

            .analytics-widgets {
                grid-template-columns: 1fr;
            }

            .widget-card.large {
                grid-column: 1;
            }

            .right-section {
                padding: 20px;
                min-height: auto;
            }

            .welcome-title {
                font-size: 24px;
            }

            .login-form-container {
                padding-bottom: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Section - Gradient with Analytics -->
        <div class="left-section">
            <div class="analytics-widgets">
                <div class="widget-card large">
                    <div class="widget-title">Weekly Sales</div>
                    <div class="chart-controls">
                        <button class="chart-btn active">Weekly</button>
                        <button class="chart-btn">Monthly</button>
                        <button class="chart-btn">Yearly</button>
                    </div>
                    <div class="bar-chart">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                    <div class="bar-labels">
                        <span>MON</span>
                        <span>TUE</span>
                        <span>WED</span>
                        <span>THU</span>
                        <span>FRI</span>
                        <span>SAT</span>
                        <span>SUN</span>
                    </div>
                </div>
                <div class="widget-card">
                    <div class="widget-title">Total Performance</div>
                    <div class="progress-circle">
                        <svg width="100" height="100">
                            <circle class="circle-bg" cx="50" cy="50" r="40"></circle>
                            <circle class="circle-progress" cx="50" cy="50" r="40"></circle>
                        </svg>
                        <div class="progress-text">42%</div>
                    </div>
                </div>
            </div>

            <div class="left-content">
                <h1 class="headline">Effortlessly manage your real estate business</h1>
                <p class="subtext">Manage leads, properties, clients and deals all in one powerful Realtor CRM.</p>
            </div>
        </div>

        <!-- Right Section - Login Form -->
        <div class="right-section">
            <div class="login-form-container">
                <div class="logo-section">
                    <div class="logo-icon">B</div>
                    <div class="logo-text">Brickly CRM</div>
                </div>

                <h2 class="welcome-title">Welcome Back</h2>
                <p class="welcome-subtitle">Login to manage your real estate operations</p>

                @php
                    use App\Models\SystemSettings;
                    $isMaintenanceMode = SystemSettings::isMaintenanceMode();
                @endphp

                @if($isMaintenanceMode)
                    <div class="maintenance-warning" style="background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 20px; margin-right: 12px;"></i>
                        <div>
                            <strong style="display: block; margin-bottom: 4px;">System Under Maintenance</strong>
                            <span style="font-size: 14px;">{{ SystemSettings::get('maintenance_message', 'System is under maintenance. Only admin can login.') }}</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="error-message">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus
                                placeholder="Enter your email"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                placeholder="Enter your password"
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 14px;">
                        <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="remember" style="width: auto; margin-right: 8px; cursor: pointer;">
                            <span style="color: #4a5568;">Remember me</span>
                        </label>
                        <a href="{{ route('password.forgot') }}" style="color: #205A44; text-decoration: none;">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-signin">Login</button>
                </form>

                <div style="display: flex; align-items: center; margin: 16px 0 12px; gap: 12px;">
                    <div style="flex: 1; height: 1px; background: #E5DED4;"></div>
                    <span style="color: #B3B5B4; font-size: 13px; white-space: nowrap;">or</span>
                    <div style="flex: 1; height: 1px; background: #E5DED4;"></div>
                </div>

                <button type="button" id="googleSignInBtn" onclick="signInWithGoogle()" style="width: 100%; padding: 12px 14px; background: white; color: #3c4043; border: 2px solid #E5DED4; border-radius: 12px; font-size: 15px; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s; font-family: 'Inter', sans-serif;">
                    <svg width="20" height="20" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59a14.5 14.5 0 0 1 0-9.18l-7.98-6.19a24.003 24.003 0 0 0 0 21.56l7.98-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                    Sign in with Google
                </button>

                <button type="button" class="btn-install-app" id="installAppBtn">
                    <i class="fas fa-download"></i> Install App
                </button>
                <div id="installAppStatus" role="status" aria-live="polite"></div>
            </div>
        </div>
    </div>

    <script>
        // Setup CSRF token for all AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            // Update form CSRF token
            const csrfInput = document.querySelector('input[name="_token"]');
            if (csrfInput) {
                csrfInput.value = csrfToken;
            }
        }

        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'password') {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });

        // Refresh CSRF token before form submission to prevent 419 errors
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', function(e) {
            // Ensure CSRF token is up to date
            const csrfInput = form.querySelector('input[name="_token"]');
            if (csrfInput && csrfToken) {
                csrfInput.value = csrfToken;
            }
            
            // Allow normal form submission
            // If 419 error occurs, Laravel will show error page which user can refresh
        });

        // Chart button interactions
        document.querySelectorAll('.chart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // PWA Install – 1-click on login page (NO redirect, install inline only)
        (function() {
            var installBtn = document.getElementById('installAppBtn');
            var statusEl = document.getElementById('installAppStatus');
            var deferredPrompt = null;
            var swReady = false;

            function setStatus(msg, isError) {
                if (!statusEl) return;
                statusEl.textContent = msg || '';
                statusEl.className = isError ? 'error' : '';
                statusEl.id = 'installAppStatus';
            }

            // 1. Register service worker first (required for PWA)
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js?v=' + Date.now()).then(function(reg) {
                    swReady = true;
                    console.log('SW registered for PWA install', reg.scope);
                }).catch(function(err) {
                    console.warn('SW registration failed:', err);
                });
            }

            // 2. Capture beforeinstallprompt (Chrome/Edge fires this when PWA is installable)
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPrompt = e;
                console.log('PWA install prompt captured');
                setStatus('');
                if (installBtn) {
                    installBtn.innerHTML = '<i class="fas fa-download"></i> Install App';
                    installBtn.disabled = false;
                }
            });

            // 3. App installed
            window.addEventListener('appinstalled', function() {
                deferredPrompt = null;
                if (installBtn) {
                    installBtn.disabled = true;
                    installBtn.innerHTML = '<i class="fas fa-check-circle"></i> Installed!';
                }
                setStatus('App installed successfully! Open it from your home screen.');
            });

            // 4. Click handler – NO redirect, install or show instructions
            if (installBtn) {
                installBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // If we have the install prompt, trigger it
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.then(function(choice) {
                            if (choice.outcome === 'accepted') {
                                setStatus('Installing app...');
                                installBtn.disabled = true;
                                installBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Installing...';
                            } else {
                                setStatus('Install cancelled. You can try again.');
                            }
                            deferredPrompt = null;
                        });
                        return false;
                    }

                    // No prompt available – show platform-specific instructions
                    var ua = navigator.userAgent || '';
                    if (/Android/i.test(ua)) {
                        setStatus('Tap the browser menu (⋮) at top-right → "Install app" or "Add to Home screen"');
                    } else if (/iPhone|iPad|iPod/i.test(ua)) {
                        setStatus('Tap the Share button (⎋) → "Add to Home Screen"');
                    } else if (/Chrome/i.test(ua)) {
                        setStatus('Click the install icon (⊕) in the address bar, or Menu → "Install app"');
                    } else if (/Edge/i.test(ua)) {
                        setStatus('Click Menu (···) → "Apps" → "Install this site as an app"');
                    } else {
                        setStatus('Use your browser menu to find "Install" or "Add to Home Screen"');
                    }

                    return false;
                });
            }
        })();

        // Firebase Google Sign-In
        (function() {
            var firebaseConfig = {
                apiKey: "{{ config('firebase.web.api_key') }}",
                authDomain: "{{ config('firebase.web.auth_domain') }}",
                projectId: "{{ config('firebase.web.project_id') }}",
                storageBucket: "{{ config('firebase.web.storage_bucket') }}",
                messagingSenderId: "{{ config('firebase.web.messaging_sender_id') }}",
                appId: "{{ config('firebase.web.app_id') }}"
            };
            if (firebaseConfig.apiKey) {
                var s1 = document.createElement('script');
                s1.src = 'https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js';
                s1.onload = function() {
                    var s2 = document.createElement('script');
                    s2.src = 'https://www.gstatic.com/firebasejs/10.14.1/firebase-auth-compat.js';
                    s2.onload = function() {
                        firebase.initializeApp(firebaseConfig);
                        window._firebaseReady = true;
                    };
                    document.head.appendChild(s2);
                };
                document.head.appendChild(s1);
            }
        })();

        function signInWithGoogle() {
            var btn = document.getElementById('googleSignInBtn');
            if (!window._firebaseReady) {
                alert('Firebase not loaded yet. Please wait a moment and try again.');
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

            var provider = new firebase.auth.GoogleAuthProvider();
            firebase.auth().signInWithPopup(provider).then(function(result) {
                return result.user.getIdToken();
            }).then(function(idToken) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("/login/firebase") }}';
                var csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                var tokenInput = document.createElement('input');
                tokenInput.type = 'hidden'; tokenInput.name = 'id_token'; tokenInput.value = idToken;
                form.appendChild(tokenInput);
                document.body.appendChild(form);
                form.submit();
            }).catch(function(error) {
                console.error('Google Sign-In error:', error);
                btn.disabled = false;
                btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59a14.5 14.5 0 0 1 0-9.18l-7.98-6.19a24.003 24.003 0 0 0 0 21.56l7.98-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg> Sign in with Google';
                if (error.code !== 'auth/popup-closed-by-user') {
                    alert('Sign-in failed: ' + (error.message || 'Unknown error'));
                }
            });
        }
    </script>
</body>
</html>

