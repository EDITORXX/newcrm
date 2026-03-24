<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemSettings extends Model
{
    protected $fillable = ['key', 'value'];
    
    public $timestamps = true;
    
    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    
    /**
     * Set a setting value by key
     */
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
    
    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode()
    {
        return self::get('maintenance_mode', '0') === '1';
    }
    
    /**
     * Enable maintenance mode and logout all non-admin users
     */
    public static function enableMaintenanceMode($message = null)
    {
        self::set('maintenance_mode', '1');
        if ($message) {
            self::set('maintenance_message', $message);
        }
        
        // Logout all non-admin users by invalidating their sessions
        self::logoutAllNonAdminUsers();
    }
    
    /**
     * Logout all non-admin users only (keep admin sessions active)
     */
    public static function logoutAllNonAdminUsers()
    {
        try {
            // Get all active sessions from database (if using database driver)
            if (config('session.driver') === 'database') {
                $sessions = DB::table('sessions')->get();
                
                foreach ($sessions as $session) {
                    try {
                        // Decode session data
                        $payload = unserialize(base64_decode($session->payload));
                        
                        // Check if session has authenticated user
                        $loginKey = 'login_web_' . sha1('Illuminate\Auth\Guard');
                        if (isset($payload[$loginKey])) {
                            $userId = $payload[$loginKey];
                            
                            // Get user and check if they are admin
                            $user = \App\Models\User::find($userId);
                            if ($user && !$user->isAdmin()) {
                                // Delete session for non-admin users only
                                DB::table('sessions')->where('id', $session->id)->delete();
                            }
                        }
                    } catch (\Exception $e) {
                        // Skip invalid sessions
                        continue;
                    }
                }
            }
            
            // For file-based sessions, delete all session files
            // Admin sessions will be re-validated on next request
            $sessionPath = storage_path('framework/sessions');
            if (file_exists($sessionPath) && is_dir($sessionPath)) {
                $files = glob($sessionPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && basename($file) !== '.gitignore') {
                        // For file sessions, we can't easily check if user is admin
                        // So we delete all and let middleware handle admin re-authentication
                        @unlink($file);
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but continue
            Log::error('Error logging out non-admin users: ' . $e->getMessage());
        }
    }
    
    /**
     * Disable maintenance mode
     */
    public static function disableMaintenanceMode()
    {
        self::set('maintenance_mode', '0');
    }
    
    /**
     * Logout all users by invalidating their sessions
     */
    public static function logoutAllUsers()
    {
        try {
            // Get all active sessions from database (if using database driver)
            if (config('session.driver') === 'database') {
                $sessions = DB::table('sessions')->get();
                
                foreach ($sessions as $session) {
                    try {
                        // Decode session data
                        $payload = unserialize(base64_decode($session->payload));
                        
                        // Check if session has authenticated user
                        if (isset($payload['login_web_' . sha1('Illuminate\Auth\Guard')])) {
                            DB::table('sessions')->where('id', $session->id)->delete();
                        }
                    } catch (\Exception $e) {
                        // Skip invalid sessions
                        continue;
                    }
                }
            }
            
            // Also clear session files if using file driver
            $sessionPath = storage_path('framework/sessions');
            if (file_exists($sessionPath) && is_dir($sessionPath)) {
                $files = glob($sessionPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && basename($file) !== '.gitignore') {
                        @unlink($file);
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but continue
            \Log::error('Error logging out all users: ' . $e->getMessage());
        }
    }
}
