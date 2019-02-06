<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Woocommerce implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'WooCommerceSensor';

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('post_updated', [ & $this, 'onProductUpdated'], 10, 3); // insert, update, trash
        add_action('before_delete_post', [ & $this, 'onProductDeleted'], 10, 1);
        add_action('woocommerce_update_product_variation', [ & $this, 'onProductVarUpdated'], 10, 1);
        add_action('woocommerce_attribute_updated', [ & $this, 'onProductAttrUpdated'], 10, 3);
        add_action('updated_option', [ & $this, 'onWooOptionUpdates'], 10, 3);
    }

    public function onProductUpdated($post_id, $post_after, $post_before)
    {
        if ($post_after->post_type != 'product') {
            return;
        }

        $current_user      = wp_get_current_user();
        $current_username  = $current_user->user_login;
        $modified_fields   = array_diff((array) $post_before, (array) $post_after);
        $post_status_label = $this->getPostStatusLabel($post_before->post_status);
        $post_type_label   = $this->getPostTypeSingularLabel($post_before->post_type);

        $this->alertManager->event(
            [
                'alert_code'        => 2090,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "User $current_username modified content for a $post_status_label $post_type_label \"$post_after->post_title\"",
                'alert_description' => count($modified_fields) . " fields modified (" . implode(', ', array_keys($modified_fields)) . ").",
                'sensor'            => self::$name,
                'post_data_changed' => $modified_fields,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'product_id'        => $post_after->ID,
                'product_status'    => $post_after->post_status,
                'product_type'      => $post_after->post_type,
                'product_title'     => $post_after->post_title,
            ]
        );
    }

    public function onProductDeleted($post_id)
    {

        $post_before = get_post($post_id);
        if ($post_before->post_type != 'product') {
            return;
        }

        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $modified_fields   = (array) $post_before;
        $post_status_label = $this->getPostStatusLabel($post_before->post_status);
        $post_type_label   = $this->getPostTypeSingularLabel($post_before->post_type);

        $this->alertManager->event(
            [
                'alert_code'        => 2091,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "User $current_username deleted a $post_status_label $post_type_label \"$post_before->post_title\"",
                'alert_description' => 'Product was deleted permanently',
                'sensor'            => self::$name,
                'post_data_changed' => $modified_fields,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'product_id'        => $post_before->ID,
                'product_status'    => $post_before->post_status,
                'product_type'      => $post_before->post_type,
                'product_title'     => $post_before->post_title,
            ]
        );
    }

    public function onProductVarUpdated($product_id)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'        => 2092,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "User $current_username updated a product variation",
                'alert_description' => 'Product #' . $product_id . ' was updated',
                'sensor'            => self::$name,
                'product_id'        => $product_id,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
            ]
        );
    }

    public function onProductAttrUpdated($id, $data, $old_slug)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $this->alertManager->event(
            [
                'alert_code'        => 2093,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "User $current_username updated a product attr",
                'alert_description' => 'Attribute was updated',
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
            ]
        );
    }

    public function onWooOptionUpdates($option, $oldvalue, $newvalue)
    {

        if (strpos($option, 'woocommerce_') === false) {
            return;
        }

        $blacklist_options = apply_filters('adeptus/sensors/options/woocommerce_blacklist', [
            'woocommerce_queue_flush_rewrite_rules',

        ]);
        if (in_array($option, $blacklist_options)) {
            return;
        }
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'        => 2094,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => 'Woocommerce Option updated',
                'alert_description' => "User $current_username changed ($option)",
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'object_name'       => $option,
                'old_value'         => (object) [json_encode($oldvalue)],
                'new_value'         => (object) [json_encode($newvalue)],
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
