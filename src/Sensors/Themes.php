<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Themes implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'ThemeSensor';

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('switch_theme', [ & $this, 'onThemeChanged'], 10, 2);
    }

    public function onThemeChanged($new_name, $new_theme)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'        => 2090,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => 'Theme changed',
                'alert_description' => "User $current_username changed the theme",
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'theme_name'        => $new_name,
            ]
        );
    }

}
