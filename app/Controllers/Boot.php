<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
|--------------------------------------------------------------------------
| BOOTSTRAP THE APPLICATION
|--------------------------------------------------------------------------
| This file lets you define events that occur before or after a request.
| This file is read automatically.
*/

/*
|--------------------------------------------------------------------------
| Pre System
|--------------------------------------------------------------------------
| Called very early in the execution cycle. Only the benchmark and events
| class have been loaded at this point. No routing has occurred.
*/
Events::on('pre_system', static function () {
    if (ENVIRONMENT !== 'production') {
        // Development environment setup for church system
        if (isset($_SERVER['CI_DEBUG']) && $_SERVER['CI_DEBUG'] === '1') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }
        
        // Custom debug bar for church management system
        if (class_exists('Debugbar')) {
            \Debugbar::enable();
        }
    }
    
    // Set timezone for church activities (adjust to your location)
    date_default_timezone_set('Asia/Jakarta'); // Contoh: Gereja di Indonesia
    
    // Define church system constants
    if (!defined('CHURCH_SYSTEM_VERSION')) {
        define('CHURCH_SYSTEM_VERSION', '1.0.0');
        define('CHURCH_NAME', 'Gereja Kristen Jawa Penaruban');
        define('CHURCH_MINISTRY_PATH', WRITEPATH . 'uploads/ministry/');
        define('CHURCH_SCHEDULE_PATH', WRITEPATH . 'uploads/schedules/');
        define('CHURCH_REPORT_PATH', WRITEPATH . 'uploads/reports/');
        define('CHURCH_COMMISSION_PREFIX', 'KOM-');
    }
});

/*
|--------------------------------------------------------------------------
| Pre Controller
|--------------------------------------------------------------------------
| Called immediately prior to any controller being executed. All base
| classes, security, and routing have been initialized.
*/
Events::on('pre_controller', static function () {
    // Initialize church session data if needed
    $session = \Config\Services::session();
    
    // Set default church-related session data
    if (!$session->has('church_context')) {
        $session->set('church_context', [
            'current_ministry_year' => date('Y'),
            'active_commission' => null,
            'user_permissions' => [],
            'liturgical_season' => getLiturgicalSeason() // Custom function
        ]);
    }
    
    // Load church configuration
    helper('church');
    
    // Check for maintenance mode for church system
    $maintenance = env('church.MAINTENANCE_MODE', false);
    if ($maintenance && !is_admin_user()) {
        header('HTTP/1.1 503 Service Unavailable');
        echo view('errors/maintenance_church');
        exit();
    }
});

/*
|--------------------------------------------------------------------------
| Post Controller Constructor
|--------------------------------------------------------------------------
| Called immediately after controller constructor, but before any method
| execution.
*/
Events::on('post_controller_constructor', static function () {
    // Load church-specific helpers
    helper(['form', 'url', 'text', 'security', 'church_schedule', 'ministry']);
    
    // Auto-load church models that are frequently used
    $controller = service('router')->controllerName();
    
    // Check if controller is related to church management
    if (strpos($controller, 'Church') !== false || 
        strpos($controller, 'Ministry') !== false ||
        strpos($controller, 'Schedule') !== false ||
        strpos($controller, 'Commission') !== false) {
        
        // Load common church models
        $churchModels = [
            'ChurchActivityModel',
            'MinistryModel', 
            'ScheduleModel',
            'CommissionModel',
            'MemberModel'
        ];
        
        foreach ($churchModels as $model) {
            if (file_exists(APPPATH . 'Models/' . $model . '.php')) {
                model('App\Models\\' . $model);
            }
        }
    }
});

/*
|--------------------------------------------------------------------------
| Post Controller
|--------------------------------------------------------------------------
| Called immediately after controller execution.
*/
Events::on('post_controller', static function () {
    // Log church activity if user is logged in
    $session = \Config\Services::session();
    if ($session->has('logged_in') && $session->get('logged_in') === true) {
        // Track user activity for church system audit trail
        $auditTrail = service('audittrail');
        if ($auditTrail) {
            $auditTrail->logActivity();
        }
    }
    
    // Clean up temporary church files
    cleanTempChurchFiles();
});

/*
|--------------------------------------------------------------------------
| Display
|--------------------------------------------------------------------------
| Called after the final output is sent to the browser.
*/
Events::on('display', static function () {
    // Add church system footer info for development
    if (ENVIRONMENT !== 'production' && 
        !service('request')->isAJAX() &&
        service('response')->getContentType() === 'text/html') {
        
        $output = service('response')->getBody();
        
        // Add church system debug info
        $debugInfo = "<!-- Church Management System v" . CHURCH_SYSTEM_VERSION . " -->\n";
        $debugInfo .= "<!-- Generated: " . date('Y-m-d H:i:s') . " -->\n";
        $debugInfo .= "<!-- Memory Usage: " . number_format(memory_get_usage() / 1024 / 1024, 2) . " MB -->\n";
        
        $output = str_replace('</body>', $debugInfo . '</body>', $output);
        service('response')->setBody($output);
    }
    
    // Send weekly schedule notifications (example)
    if (date('w') == 1) { // Every Monday
        sendWeeklyScheduleNotifications();
    }
});

/*
|--------------------------------------------------------------------------
| Context-Specific Events for Church System
|--------------------------------------------------------------------------
| Custom events for church management system
*/

// Event for when a new worship schedule is created
Events::on('schedule.created', static function ($data) {
    // Notify ministry leaders
    $notificationService = service('notification');
    $notificationService->sendScheduleNotification($data);
    
    // Log the schedule creation
    log_message('info', 'New worship schedule created: ' . $data['schedule_name']);
    
    // Sync with church calendar if enabled
    if (env('church.SYNC_CALENDAR', false)) {
        syncWithChurchCalendar($data);
    }
});

// Event for when a commission program is updated
Events::on('commission.updated', static function ($commissionId, $data) {
    // Update related activities
    $commissionModel = model('App\Models\CommissionModel');
    $commissionModel->updateRelatedActivities($commissionId, $data);
    
    // Notify commission members
    notifyCommissionMembers($commissionId, 'Program updated');
});

// Event for when a member registers for an activity
Events::on('activity.registration', static function ($activityId, $memberId) {
    // Send confirmation
    sendRegistrationConfirmation($activityId, $memberId);
    
    // Update activity count
    $activityModel = model('App\Models\ChurchActivityModel');
    $activityModel->incrementRegistrationCount($activityId);
});

// Event for automated daily tasks
Events::on('daily.maintenance', static function () {
    // Clean up old logs
    cleanOldChurchLogs();
    
    // Backup database if configured
    if (env('church.AUTO_BACKUP', false)) {
        backupChurchDatabase();
    }
    
    // Send daily reminders for tomorrow's activities
    sendDailyReminders();
});

/*
|--------------------------------------------------------------------------
| Custom Helper Functions for Church System
|--------------------------------------------------------------------------
*/

if (!function_exists('getLiturgicalSeason')) {
    /**
     * Determine current liturgical season
     */
    function getLiturgicalSeason(): string
    {
        $date = date('m-d');
        $year = date('Y');
        
        // Calculate Easter date (simplified)
        $easter = date('m-d', easter_date($year));
        
        // Determine season based on date
        if ($date >= '12-25' || $date < '01-06') {
            return 'Christmas';
        } elseif ($date >= '01-06' && $date < '03-02') {
            return 'Epiphany';
        } elseif ($date >= '03-02' && $date < calculateDateBeforeEaster($easter, 46)) {
            return 'Lent';
        } elseif ($date >= calculateDateBeforeEaster($easter, 46) && $date < $easter) {
            return 'Holy Week';
        } elseif ($date >= $easter && $date < calculateDateAfterEaster($easter, 49)) {
            return 'Easter';
        } elseif ($date >= calculateDateAfterEaster($easter, 49) && $date < calculateDateAfterEaster($easter, 56)) {
            return 'Pentecost';
        } else {
            return 'Ordinary Time';
        }
    }
}

if (!function_exists('calculateDateBeforeEaster')) {
    function calculateDateBeforeEaster($easterDate, $daysBefore): string
    {
        $date = date_create(date('Y') . '-' . $easterDate);
        date_sub($date, date_interval_create_from_date_string("$daysBefore days"));
        return date_format($date, 'm-d');
    }
}

if (!function_exists('calculateDateAfterEaster')) {
    function calculateDateAfterEaster($easterDate, $daysAfter): string
    {
        $date = date_create(date('Y') . '-' . $easterDate);
        date_add($date, date_interval_create_from_date_string("$daysAfter days"));
        return date_format($date, 'm-d');
    }
}

if (!function_exists('cleanTempChurchFiles')) {
    /**
     * Clean temporary church files
     */
    function cleanTempChurchFiles(): void
    {
        $tempPath = WRITEPATH . 'temp/church/';
        if (is_dir($tempPath)) {
            $files = glob($tempPath . '*');
            $now = time();
            
            foreach ($files as $file) {
                if (is_file($file) && ($now - filemtime($file)) > 3600) {
                    unlink($file);
                }
            }
        }
    }
}

if (!function_exists('is_admin_user')) {
    /**
     * Check if current user is admin
     */
    function is_admin_user(): bool
    {
        $session = \Config\Services::session();
        if ($session->has('user_role')) {
            return in_array($session->get('user_role'), ['admin', 'pastor', 'elder']);
        }
        return false;
    }
}

/*
|--------------------------------------------------------------------------
| Development Hot Reload
|--------------------------------------------------------------------------
*/
if (ENVIRONMENT === 'development') {
    Events::on('pre_system', static function () {
        if (getenv('CI_HOT_RELOAD') === 'true') {
            $hotReloader = new HotReloader();
            $hotReloader->run();
        }
    });
}