<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Errors implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'ErrorsSensor';

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('shutdown', [ & $this, 'onErrorOccurred'], 10, 0);
    }

    public function onErrorOccurred()
    {

        $err = error_get_last();

        if (!$err) {
            return;
        }

        $fatals = array(
            //E_NOTICE         => 'Run-time Notice',
            E_ERROR           => 'Fatal Run-time Error',
            E_WARNING         => 'Run-time Warning',
            E_USER_ERROR      => 'Fatal Error',
            E_PARSE           => 'Parse Error',
            E_CORE_ERROR      => 'Core Error',
            E_CORE_WARNING    => 'Core Warning',
            E_COMPILE_ERROR   => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
        );

        if (isset($fatals[$err['type']])) {
            $error_type = $fatals[$err['type']];
        } else {
            return;
        }

        if ($err['type'] == E_WARNING || $err['type'] == E_COMPILE_WARNING || $err['type'] == E_CORE_WARNING) {
            $alert_level = \Psr\Log\LogLevel::WARNING;
        } else {
            $alert_level = \Psr\Log\LogLevel::ERROR;

        }

        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $description = explode('Stack trace', $err['message'])[0];

        if (!empty($err['file'])) {
            $description = str_replace(dirname($err['file']), '', $description);
        }

        $this->alertManager->event(
            [
                'alert_code'        => 2070,
                'alert_level'       => $alert_level,
                'alert_title'       => $error_type,
                'alert_description' => $description,
                'sensor'            => self::$name,
                'error_file'        => $err['file'],
                'error_line'        => $err['line'],
                'current_user_id'   => $current_user->ID,
                'current_user_name' => !empty($current_username) ? $current_username : '-',
                'error_message'     => $err['message'],
            ]
        );
    }
}
