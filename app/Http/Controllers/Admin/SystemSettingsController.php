<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class SystemSettingsController extends Controller
{
    /**
     * Display system settings page
     */
    public function index()
    {
        $maintenanceMode = SystemSettings::isMaintenanceMode();
        $maintenanceMessage = SystemSettings::get('maintenance_message');
        $sendWelcomeEmailToNewUser = filter_var(SystemSettings::get('send_welcome_email_to_new_user', '1'), FILTER_VALIDATE_BOOLEAN);
        $notifyAdminOnNewUser = filter_var(SystemSettings::get('notify_admin_on_new_user', '1'), FILTER_VALIDATE_BOOLEAN);

        return view('admin.system-settings.index', compact(
            'maintenanceMode',
            'maintenanceMessage',
            'sendWelcomeEmailToNewUser',
            'notifyAdminOnNewUser'
        ));
    }
    
    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenanceMode(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'message' => 'nullable|string|max:500'
        ]);
        
        try {
            if ($request->enabled) {
                SystemSettings::enableMaintenanceMode($request->message);
                return response()->json([
                    'success' => true,
                    'message' => 'Maintenance mode enabled. All users have been logged out.',
                    'maintenance_mode' => true
                ]);
            } else {
                SystemSettings::disableMaintenanceMode();
                return response()->json([
                    'success' => true,
                    'message' => 'Maintenance mode disabled.',
                    'maintenance_mode' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error toggling maintenance mode: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user notification settings (welcome email + notify admin on new user)
     */
    public function updateUserNotificationSettings(Request $request)
    {
        $request->validate([
            'send_welcome_email_to_new_user' => 'required|boolean',
            'notify_admin_on_new_user' => 'required|boolean',
        ]);

        try {
            SystemSettings::set('send_welcome_email_to_new_user', $request->send_welcome_email_to_new_user ? '1' : '0');
            SystemSettings::set('notify_admin_on_new_user', $request->notify_admin_on_new_user ? '1' : '0');

            return response()->json([
                'success' => true,
                'message' => 'User notification settings updated successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating user notification settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show test email page (send sample welcome mail to any email)
     */
    public function testEmailPage()
    {
        return view('admin.system-settings.test-email');
    }

    /**
     * Send a sample welcome email to the given address (1-click test)
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $mailerName = config('mail.default') ?: env('MAIL_MAILER') ?: 'smtp';
        $mailerName = is_string($mailerName) ? $mailerName : 'smtp';

        try {
            $appName = config('app.name');
            $loginUrl = url('/login');
            $user = (object) [
                'name' => 'Test User',
                'email' => $request->email,
                'phone' => '+91 98765 43210',
                'is_active' => true,
            ];

            Mail::mailer($mailerName)->send('emails.new-user-welcome', [
                'user' => $user,
                'plainPassword' => 'Test@12345',
                'roleName' => 'Sales Executive',
                'managerName' => 'John Manager',
                'loginUrl' => $loginUrl,
                'appName' => $appName,
            ], function ($message) use ($request, $appName) {
                $message->to($request->email)
                    ->subject('[' . $appName . '] Test – Sample welcome email');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent to ' . $request->email . '. Check inbox (and spam).',
            ]);
        } catch (\Exception $e) {
            Log::error('Test email failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mail debug page: show config and send test with full error detail
     */
    public function mailDebugPage()
    {
        $default = config('mail.default');
        $mailers = config('mail.mailers');
        $mailerKeys = is_array($mailers) ? array_keys($mailers) : [];
        $from = config('mail.from');
        $smtp = $mailers['smtp'] ?? [];
        $envMail = [
            'MAIL_MAILER' => env('MAIL_MAILER') ?: ('(not set → ' . config('mail.default') . ')'),
            'MAIL_HOST' => env('MAIL_HOST') ?: '(e.g. smtp.hostinger.com)',
            'MAIL_PORT' => env('MAIL_PORT') ?: '587',
            'MAIL_USERNAME' => env('MAIL_USERNAME') ?: 'support@crm.bihtech.com',
            'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? '(set)' : '(empty)',
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION') !== null && env('MAIL_ENCRYPTION') !== '' ? env('MAIL_ENCRYPTION') : 'tls',
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS') ?: config('mail.from.address'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME') ?: config('mail.from.name'),
        ];
        return view('admin.system-settings.mail-debug', compact('default', 'mailerKeys', 'from', 'envMail', 'smtp'));
    }

    /**
     * Send test email and return full error detail for debug page
     */
    public function sendTestEmailDebug(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $mailerName = config('mail.default') ?: env('MAIL_MAILER') ?: 'smtp';
        $mailerName = is_string($mailerName) ? $mailerName : 'smtp';

        try {
            $appName = config('app.name');
            $user = (object) ['name' => 'Test User', 'email' => $request->email, 'phone' => '—', 'is_active' => true];
            Mail::mailer($mailerName)->send('emails.new-user-welcome', [
                'user' => $user,
                'plainPassword' => 'Test@12345',
                'roleName' => 'Sales Executive',
                'managerName' => 'John Manager',
                'loginUrl' => url('/login'),
                'appName' => $appName,
            ], function ($message) use ($request, $appName) {
                $message->to($request->email)->subject('[' . $appName . '] Test – Sample welcome email');
            });
            return response()->json(['success' => true, 'message' => 'Email sent to ' . $request->email]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_detail' => $e->getMessage() . "\n\n" . $e->getFile() . ':' . $e->getLine(),
            ], 500);
        }
    }

    /**
     * Upload files (zip or regular files)
     */
    public function uploadFiles(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:102400', // 100MB max per file
        ]);
        
        try {
            $uploadedFiles = [];
            $tempPath = storage_path('app/temp_uploads');
            
            // Create temp directory
            if (!File::exists($tempPath)) {
                File::makeDirectory($tempPath, 0755, true);
            }
            
            foreach ($request->file('files') as $file) {
                // Check if it's a zip file
                if (strtolower($file->getClientOriginalExtension()) === 'zip') {
                    $zipPath = $file->storeAs('temp_uploads', $file->getClientOriginalName());
                    $fullPath = storage_path('app/' . $zipPath);
                    
                    $zip = new ZipArchive;
                    if ($zip->open($fullPath) === TRUE) {
                        $extractPath = storage_path('app/temp_extract/' . time() . '_' . uniqid());
                        File::makeDirectory($extractPath, 0755, true);
                        $zip->extractTo($extractPath);
                        $zip->close();
                        
                        $uploadedFiles[] = [
                            'name' => $file->getClientOriginalName(),
                            'type' => 'zip',
                            'extracted' => $extractPath,
                            'original_path' => $fullPath
                        ];
                    } else {
                        throw new \Exception('Failed to extract zip file: ' . $file->getClientOriginalName());
                    }
                } else {
                    // Regular file
                    $path = $file->storeAs('temp_uploads', time() . '_' . $file->getClientOriginalName());
                    $uploadedFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'type' => 'file',
                        'path' => storage_path('app/' . $path)
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => count($uploadedFiles) . ' file(s) uploaded successfully. Ready to deploy.',
                'files' => $uploadedFiles
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error uploading files: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading files: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Deploy files to server
     */
    public function deployFiles(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'destination' => 'required|string|in:app,resources,public,database,config,root'
        ]);
        
        try {
            $destination = $request->destination;
            $basePath = base_path();
            $deployed = [];
            $errors = [];
            
            foreach ($request->files as $fileData) {
                try {
                    $source = $fileData['path'] ?? null;
                    $extracted = $fileData['extracted'] ?? null;
                    $fileName = $fileData['name'] ?? 'unknown';
                    
                    if ($extracted && File::exists($extracted)) {
                        // Handle extracted zip - copy directory structure
                        $destPath = $basePath . '/' . ($destination === 'root' ? '' : $destination);
                        $this->copyDirectory($extracted, $destPath);
                        $deployed[] = $fileName . ' (extracted)';
                    } elseif ($source && File::exists($source)) {
                        // Handle single file
                        $targetFileName = basename($source);
                        // Remove timestamp prefix if exists
                        if (preg_match('/^\d+_\d+_(.+)$/', $targetFileName, $matches)) {
                            $targetFileName = $matches[1];
                        }
                        $targetPath = $basePath . '/' . ($destination === 'root' ? '' : $destination . '/') . $targetFileName;
                        
                        File::ensureDirectoryExists(dirname($targetPath));
                        File::copy($source, $targetPath);
                        $deployed[] = $targetFileName;
                    }
                } catch (\Exception $e) {
                    $errors[] = 'Error deploying ' . ($fileName ?? 'file') . ': ' . $e->getMessage();
                    Log::error('Error deploying file: ' . $e->getMessage());
                }
            }
            
            // Clean up temp files
            $this->cleanupTempFiles();
            
            $message = count($deployed) . ' file(s) deployed successfully.';
            if (count($errors) > 0) {
                $message .= ' Errors: ' . implode(', ', $errors);
            }
            
            return response()->json([
                'success' => count($errors) === 0,
                'message' => $message,
                'deployed' => $deployed,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deploying files: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deploying files: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $destination)
    {
        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }
        
        $files = File::allFiles($source);
        
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $targetPath = $destination . '/' . $relativePath;
            
            // Ensure target directory exists
            File::ensureDirectoryExists(dirname($targetPath));
            
            // Copy file
            File::copy($file->getPathname(), $targetPath);
        }
    }
    
    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles()
    {
        try {
            $tempUploads = storage_path('app/temp_uploads');
            $tempExtract = storage_path('app/temp_extract');
            
            if (File::exists($tempUploads)) {
                File::deleteDirectory($tempUploads);
            }
            
            if (File::exists($tempExtract)) {
                File::deleteDirectory($tempExtract);
            }
        } catch (\Exception $e) {
            Log::warning('Error cleaning up temp files: ' . $e->getMessage());
        }
    }
    
    /**
     * Run migrations
     */
    public function runMigrations(Request $request)
    {
        try {
            $force = $request->input('force', true);
            
            // Run migrations
            Artisan::call('migrate', [
                '--force' => $force
            ]);
            
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Migrations ran successfully.',
                'output' => $output
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error running migrations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error running migrations: ' . $e->getMessage(),
                'output' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Run artisan commands
     */
    public function runCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string|in:migrate,optimize,clear-cache,config-cache,route-cache,view-cache,config-clear,route-clear,view-clear'
        ]);
        
        try {
            $command = $request->command;
            $output = '';
            
            switch ($command) {
                case 'migrate':
                    Artisan::call('migrate', ['--force' => true]);
                    break;
                case 'optimize':
                    Artisan::call('optimize');
                    break;
                case 'clear-cache':
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    $output = 'All caches cleared successfully.';
                    break;
                case 'config-cache':
                    Artisan::call('config:cache');
                    break;
                case 'route-cache':
                    Artisan::call('route:cache');
                    break;
                case 'view-cache':
                    Artisan::call('view:cache');
                    break;
                case 'config-clear':
                    Artisan::call('config:clear');
                    break;
                case 'route-clear':
                    Artisan::call('route:clear');
                    break;
                case 'view-clear':
                    Artisan::call('view:clear');
                    break;
            }
            
            if (empty($output)) {
                $output = Artisan::output();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Command executed successfully.',
                'output' => $output ?: 'Command completed.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error executing command: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error executing command: ' . $e->getMessage(),
                'output' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Test database connection with new credentials
     */
    public function testDatabaseConnection(Request $request)
    {
        // Security check: Only admin users
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin users can test database connections.',
            ], 403);
        }

        $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'database' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        try {
            // Test connection with provided credentials
            config([
                'database.connections.mysql.host' => $request->host,
                'database.connections.mysql.port' => $request->port,
                'database.connections.mysql.database' => $request->database,
                'database.connections.mysql.username' => $request->username,
                'database.connections.mysql.password' => $request->password ?? '',
            ]);

            DB::purge('mysql');
            DB::connection('mysql')->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'Database connection successful!',
            ]);
        } catch (\Exception $e) {
            Log::error('Database connection test failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update database settings
     */
    public function updateDatabaseSettings(Request $request)
    {
        // Security check: Only admin users
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin users can update database settings.',
            ], 403);
        }

        $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'database' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        try {
            // Backup current .env file
            $envPath = base_path('.env');
            if (File::exists($envPath)) {
                File::copy($envPath, $envPath . '.backup.' . date('Y-m-d_H-i-s'));
            }

            // Read current .env
            $envContent = File::get($envPath);

            // Update database settings
            $envContent = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=' . $request->host, $envContent);
            $envContent = preg_replace('/^DB_PORT=.*/m', 'DB_PORT=' . $request->port, $envContent);
            $envContent = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . $request->database, $envContent);
            $envContent = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=' . $request->username, $envContent);
            $envContent = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=' . ($request->password ?? ''), $envContent);

            // Write updated .env
            File::put($envPath, $envContent);

            // Clear config cache
            Artisan::call('config:clear');

            // Test new connection
            config([
                'database.connections.mysql.host' => $request->host,
                'database.connections.mysql.port' => $request->port,
                'database.connections.mysql.database' => $request->database,
                'database.connections.mysql.username' => $request->username,
                'database.connections.mysql.password' => $request->password ?? '',
            ]);

            DB::purge('mysql');
            DB::connection('mysql')->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'Database settings updated successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating database settings', ['error' => $e->getMessage()]);
            
            // Restore backup if exists
            $backupFiles = glob(base_path('.env.backup.*'));
            if (!empty($backupFiles)) {
                $latestBackup = end($backupFiles);
                File::copy($latestBackup, base_path('.env'));
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update database settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get environment variables
     */
    public function getEnvSettings()
    {
        // Security check: Only admin users
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin users can view environment settings.',
            ], 403);
        }

        try {
            $envPath = base_path('.env');
            if (!File::exists($envPath)) {
                return response()->json([
                    'success' => false,
                    'message' => '.env file not found',
                ], 404);
            }

            $envContent = File::get($envPath);
            $lines = explode("\n", $envContent);
            $settings = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }

                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    // Remove quotes if present
                    $value = trim($value, '"\'');
                    $settings[$key] = $value;
                }
            }

            return response()->json([
                'success' => true,
                'settings' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error reading env settings', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error reading environment settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update environment variables
     */
    public function updateEnvSettings(Request $request)
    {
        // Security check: Only admin users
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin users can update environment settings.',
            ], 403);
        }

        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000', // Limit value length
        ]);

        // Security: Block certain critical keys from being changed via web interface
        $blockedKeys = ['APP_KEY']; // Add more if needed
        foreach ($blockedKeys as $key) {
            if (isset($request->settings[$key])) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot update {$key} via web interface for security reasons.",
                ], 400);
            }
        }

        try {
            $envPath = base_path('.env');
            if (!File::exists($envPath)) {
                return response()->json([
                    'success' => false,
                    'message' => '.env file not found',
                ], 404);
            }

            // Backup current .env
            File::copy($envPath, $envPath . '.backup.' . date('Y-m-d_H-i-s'));

            // Read current .env
            $envContent = File::get($envPath);
            $lines = explode("\n", $envContent);
            $updatedLines = [];

            // Track which keys we've updated
            $updatedKeys = [];

            foreach ($lines as $line) {
                $originalLine = $line;
                $line = trim($line);

                // Keep comments and empty lines as is
                if (empty($line) || strpos($line, '#') === 0) {
                    $updatedLines[] = $originalLine;
                    continue;
                }

                // Check if this line has a key we need to update
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);

                    if (isset($request->settings[$key])) {
                        $newValue = $request->settings[$key];
                        // Add quotes if value contains spaces or special characters
                        if (preg_match('/[\s=#]/', $newValue)) {
                            $newValue = '"' . $newValue . '"';
                        }
                        $updatedLines[] = $key . '=' . $newValue;
                        $updatedKeys[] = $key;
                    } else {
                        $updatedLines[] = $originalLine;
                    }
                } else {
                    $updatedLines[] = $originalLine;
                }
            }

            // Add any new settings that weren't in the file
            foreach ($request->settings as $key => $value) {
                if (!in_array($key, $updatedKeys)) {
                    if (preg_match('/[\s=#]/', $value)) {
                        $value = '"' . $value . '"';
                    }
                    $updatedLines[] = $key . '=' . $value;
                }
            }

            // Write updated .env
            File::put($envPath, implode("\n", $updatedLines));

            // Clear config cache
            Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => 'Environment settings updated successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating env settings', ['error' => $e->getMessage()]);
            
            // Restore backup if exists
            $backupFiles = glob(base_path('.env.backup.*'));
            if (!empty($backupFiles)) {
                $latestBackup = end($backupFiles);
                File::copy($latestBackup, base_path('.env'));
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update environment settings: ' . $e->getMessage(),
            ], 500);
        }
    }
}
