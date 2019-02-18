<?php
/*
Plugin Name: Adeptus: Activity Audit Log
Plugin URI: http://www.thedigitalembassy.co
Description: Logs activity on WordPress website.
Version: 1.1.3
Author: The Digital Embassy
Author URI: http://www.thedigitalembassy.co/
License: GPL
Text Domain: wp-adeptus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require 'autoload.php';

/**
 * Set default values on plugin activation
 */
register_activation_hook(__FILE__, 'adeptus_activate');

function adeptus_activate() {
    do_action('adeptus/activate');
}

/*
 * Set up settings screen
 */
$adeptus_settings = new \TDE\Adeptus\Admin\SettingsScreen(plugin_basename(__FILE__));

/*
 * Set up logger
 */
if (
    !defined('WP_ADEPTUS_LOGGING_DISABLED') ||
    (
        defined('WP_ADEPTUS_LOGGING_DISABLED') && WP_ADEPTUS_LOGGING_DISABLED == false
    )
) {
    $adeptus_alertManager = new \TDE\Adeptus\AlertManager();
    $adeptus_logger       = new \TDE\Adeptus\Logger($adeptus_alertManager);
    $adeptus_logger->hookEvents();
}

/**
 * Allows external code to access logger instance.
 *   Usage:
 *
 *   Adeptus::logEvent([
 *      'alert_code'        => 10000,
 *      'alert_level'       => \Psr\Log\LogLevel::INFO,
 *      'alert_title'       => "Test",
 *      'alert_description' => "Test description",
 *      'sensor'            => "TestSensor",
 *      'post_id'           => 1234 // Additional property
 *   ]);
 */
class Adeptus
{
    private function __construct()
    {
        // Static class
    }

    /**
     * Log new event
     * @param  array  $payload Associative array of data to log
     *                         should contain the following properties
     *                          - alert_code
     *                          - alert_level
     *                          - alert_title
     *                          - alert_description
     *                          - sensor
     * @return void
     */
    public static function logEvent(array $payload = array())
    {
        global $adeptus_alertManager;

        if (isset($adeptus_alertManager)) {
            $adeptus_alertManager->event($payload);
        }
    }

    /**
     * Disable logging
     * When disabled no events will be captured.
     * @return void
     */
    public static function disableLogging()
    {
        global $adeptus_alertManager;

        if (isset($adeptus_alertManager)) {
            $adeptus_alertManager->disableLogging();
        }
    }

    /**
     * Enable logging
     * @return void
     */
    public static function enableLogging()
    {
        global $adeptus_alertManager;

        if (isset($adeptus_alertManager)) {
            $adeptus_alertManager->enableLogging();
        }
    }
}
