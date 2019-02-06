<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Attachments implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'AttachmentsSensor';

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('add_attachment', [ & $this, 'onAttachmentUploaded'], 10, 1);
        add_action('delete_attachment', [ & $this, 'onAttachmentDeleted'], 10, 1);
        add_action('edit_attachment', [ & $this, 'onAttachmentUpdated'], 10, 1);
    }

    public function onAttachmentUploaded($post_id)
    {

        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $post_after       = get_post($post_id);

        $this->alertManager->event(
            [
                'alert_code'        => 2050,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => 'Attachment added',
                'alert_description' => "User $current_username uploaded an attachment",
                'sensor'            => self::$name,
                'url'               => $post_after->guid,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'post_id'           => $post_id,
                'post_title'        => $post_after->post_title,
            ]
        );
    }

    public function onAttachmentDeleted($post_id)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $post_before      = get_post($post_id);

        if ($post_before->post_type == 'nav_menu_item') {
            return;
        }

        $this->alertManager->event(
            [
                'alert_code'        => 2051,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "User $current_username deleted an attachment",
                'alert_description' => 'Attachment was deleted permanently',
                'sensor'            => self::$name,
                'url'               => $post_before->guid,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'post_id'           => $post_before->ID,
                'post_status'       => $post_before->post_status,
                'post_type'         => $post_before->post_type,
                'post_title'        => $post_before->post_title,
            ]
        );
    }

    public function onAttachmentUpdated($post_id)
    {

        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $post_after       = get_post($post_id);

        $this->alertManager->event(
            [
                'alert_code'        => 2052,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => 'Attachment updated',
                'alert_description' => "User $current_username edited an attachment",
                'sensor'            => self::$name,
                'url'               => $post_after->guid,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'post_id'           => $post_id,
                'post_title'        => $post_after->post_title,
            ]
        );
    }

}
