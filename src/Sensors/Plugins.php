<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Plugins implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'PluginsSensor';

    public $beforeUpdateTermData = [];

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('activated_plugin', [ & $this, 'onPluginActivated'], 10, 2);
        add_action('deactivated_plugin', [ & $this, 'onPluginDeactivated'], 10, 2);
        add_action('upgrader_process_complete', [ & $this, 'onPluginUpgrade'], 10, 2);
    }

    public function onPluginActivated($plugin, $network_activation)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'         => 2030,
                'alert_level'        => \Psr\Log\LogLevel::INFO,
                'alert_title'        => "Plugin activated",
                'alert_description'  => "Plugin activated by user ($current_username)",
                'sensor'             => self::$name,
                'current_user_id'    => $current_user->ID,
                'current_user_name'  => $current_user->user_login,
                'plugin'             => $plugin,
                'network_activation' => $network_activation,
            ]
        );
    }

    public function onPluginDeactivated($plugin, $network_activation)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'          => 2031,
                'alert_level'         => \Psr\Log\LogLevel::WARNING,
                'alert_title'         => "Plugin deactivated",
                'alert_description'   => "Plugin deactivated by user ($current_username)",
                'sensor'              => self::$name,
                'current_user_id'     => $current_user->ID,
                'current_user_name'   => $current_user->user_login,
                'plugin'              => $plugin,
                'networkdeactivation' => $network_activation,
            ]
        );
    }

    public function onPluginUpgrade($upgrader_object, $options)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'        => 2032,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "Plugin updated",
                'alert_description' => "Plugin updated by user ($current_username)",
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'plugins'           => $options['plugins'],
            ]
        );
    }

}
