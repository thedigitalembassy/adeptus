<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Core implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'CoreSensor';

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('_core_updated_successfully', array(&$this, 'onCoreUpdatedSuccessfully'));
    }

    public function onCoreUpdatedSuccessfully($wp_version)
    {
        global $pagenow;

        $current_user = wp_get_current_user();

        // Auto updated
        if ('update-core.php' !== $pagenow) {
            $object_name = 'WordPress Auto Updated';
        } else {
            $object_name = 'WordPress Updated';
        }

        $this->alertManager->event(
            [
                'alert_code'        => 1000,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => $object_name,
                'alert_description' => $object_name,
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
            ]
        );
    }
}
