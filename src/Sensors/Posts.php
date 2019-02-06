<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Posts implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'PostsSensor';

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('post_updated', [ & $this, 'onPostUpdated'], 10, 3); // insert, update, trash
        add_action('before_delete_post', [ & $this, 'onPostDeleted'], 10, 1);
    }

    public function onPostUpdated($post_id, $post_after, $post_before)
    {
        if ($post_after->post_type == 'nav_menu_item' || $post_after->post_type == 'product') {
            return;
        }

        $current_user      = wp_get_current_user();
        $current_username  = !empty($current_user->user_login) ? $current_user->user_login : '-';
        $modified_fields   = array_diff((array) $post_before, (array) $post_after);
        $post_status_label = $this->getPostStatusLabel($post_before->post_status);
        $post_type_label   = $this->getPostTypeSingularLabel($post_before->post_type);

        $this->alertManager->event(
            [
                'alert_code'        => 2000,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "User $current_username modified content for a $post_status_label $post_type_label \"$post_after->post_title\"",
                'alert_description' => count($modified_fields) . " fields modified (" . implode(', ', array_keys($modified_fields)) . ").",
                'sensor'            => self::$name,
                'post_data_changed' => $modified_fields,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_username,
                'post_id'           => $post_after->ID,
                'post_status'       => $post_after->post_status,
                'post_type'         => $post_after->post_type,
                'post_title'        => $post_after->post_title,
            ]
        );
    }

    public function onPostDeleted($post_id)
    {
        $current_user     = wp_get_current_user();
        $current_username = !empty($current_user->user_login) ? $current_user->user_login : '-';
        $post_before      = get_post($post_id);

        if ($post_before->post_type == 'nav_menu_item' || $post_before->post_type == 'product') {
            return;
        }

        $modified_fields   = (array) $post_before;
        $post_status_label = $this->getPostStatusLabel($post_before->post_status);
        $post_type_label   = $this->getPostTypeSingularLabel($post_before->post_type);

        $this->alertManager->event(
            [
                'alert_code'        => 2001,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "User $current_username deleted a $post_status_label $post_type_label \"$post_before->post_title\"",
                'alert_description' => 'Post was deleted permanently',
                'sensor'            => self::$name,
                'post_data_changed' => $modified_fields,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_username,
                'post_id'           => $post_before->ID,
                'post_status'       => $post_before->post_status,
                'post_type'         => $post_before->post_type,
                'post_title'        => $post_before->post_title,
            ]
        );
    }

    private function getPostTypeSingularLabel($post_type)
    {
        $post_type_labels = get_post_type_object($post_type);
        return strtolower($post_type_labels->labels->singular_name);
    }

    private function getPostStatusLabel($post_status)
    {
        switch ($post_status) {
            case 'publish':
                return 'published';
            case 'trash':
                return 'trashed';
            case 'inherit':
                return 'inherited';
            default:
                return $post_status;
        }
    }
}
