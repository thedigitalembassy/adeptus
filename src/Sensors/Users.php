<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Users implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'UsersSensor';

    public $beforeUpdateProfile = [];

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('wp_login', [ & $this, 'onUserLogin'], 1, 2);
        add_action('wp_login_failed', [ & $this, 'onUserLoginFailed'], 10, 1);
        add_action('clear_auth_cookie', [ & $this, 'onUserLogout']); // use this to get user info before logout
        add_action('profile_update', [ & $this, 'onUserProfileUpdate'], 10, 2);
        add_action('set_user_role', [ & $this, 'onUserRoleUpdate'], 10, 3);
        add_action('password_reset', [ & $this, 'onUserPasswordReset'], 10, 2);
        add_action('user_register', [ & $this, 'onUserRegister'], 10, 1);
        add_action('delete_user', [ & $this, 'onUserDelete'], 10, 1);
    }

    public function onUserLogin($user_login, $user)
    {
        $this->alertManager->event(
            [
                'alert_code'        => 2020,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "User logged in",
                'alert_description' => "User $user_login logged in",
                'sensor'            => self::$name,
                'current_user_id'   => $user->ID,
                'current_user_name' => !empty($user_login) ? $user_login : '-',
            ]
        );
    }

    public function onUserLoginFailed($user_login)
    {
        $this->alertManager->event(
            [
                'alert_code'        => 2028,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "User failed to log in",
                'alert_description' => "Login attempted with ($user_login)",
                'sensor'            => self::$name,
            ]
        );
    }

    public function onUserLogout()
    {
        $current_user = wp_get_current_user();

        $this->alertManager->event(
            [
                'alert_code'        => 2021,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "User logged out",
                'alert_description' => "User $current_user->user_login logged out",
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => !empty($current_user->user_login) ? $current_user->user_login : '-',
            ]
        );
    }

    public function onUserProfileUpdate($user_id, $old_user_data)
    {
        $current_user = wp_get_current_user();
        $user         = get_user_by('id', $user_id);

        $this->alertManager->event(
            [
                'alert_code'        => 2022,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "User profile was updated",
                'alert_description' => "User ($current_user->user_login) updated user profile for ($old_user_data->user_login)",
                'sensor'            => self::$name,
                'user_id'           => $user->ID,
                'user_name'         => $user->user_login,
                'current_user_id'   => $current_user->ID,
                /*'current_user_name' => $current_user->user_login,*/
            ]
        );

        if ($old_user_data->user_pass != $user->user_pass) {
            $this->alertManager->event(
                [
                    'alert_code'        => 2023,
                    'alert_level'       => \Psr\Log\LogLevel::INFO,
                    'alert_title'       => "User password was updated",
                    'alert_description' => "User ($current_user->user_login) changed password for user ($old_user_data->user_login)",
                    'sensor'            => self::$name,
                    'user_id'           => $user->ID,
                    'user_name'         => $user->user_login,
                    'current_user_id'   => $current_user->ID,
                    'current_user_name' => $current_user->user_login,
                ]
            );
        }
    }

    public function onUserRoleUpdate($user_id, $role, $old_roles)
    {
        $current_user = wp_get_current_user();
        $user         = get_user_by('id', $user_id);

        $this->alertManager->event(
            [
                'alert_code'        => 2024,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "User role updated",
                'alert_description' => "User Role was updated for ($user->user_login)",
                'sensor'            => self::$name,
                'user_id'           => $user->ID,
                'user_name'         => $user->user_login,
                'old_user_role'     => $old_roles[0],
                'new_user_role'     => $role,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
            ]
        );
    }

    public function onUserPasswordReset($user, $new_pass)
    {
        $this->alertManager->event(
            [
                'alert_code'        => 2025,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "User password reset",
                'alert_description' => "User password was reset for ($user->user_login)",
                'sensor'            => self::$name,
                'user_id'           => $user->ID,
                'user_name'         => $user->user_login,
            ]
        );
    }

    public function onUserRegister($user_id)
    {
        $current_user = wp_get_current_user();
        $user         = get_user_by('id', $user_id);

        $this->alertManager->event(
            [
                'alert_code'        => 2026,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "New user was registered",
                'alert_description' => "New user ($user->user_login) was registered by ($current_user->user_login)",
                'sensor'            => self::$name,
                'user_id'           => $user->ID,
                'user_name'         => $user->user_login,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
            ]
        );
    }

    public function onUserDelete($user_id)
    {
        $current_user = wp_get_current_user();
        $user         = get_user_by('id', $user_id);

        $this->alertManager->event(
            [
                'alert_code'        => 2027,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "User was deleted",
                'alert_description' => "User ($user->user_login) was deleted by ($current_user->user_login)",
                'sensor'            => self::$name,
                'user_id'           => $user->ID,
                'user_name'         => $user->user_login,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
            ]
        );
    }
}
