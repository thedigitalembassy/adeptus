<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Menus implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'MenusSensor';

    public $beforeUpdateTermData = [];

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('create_term', [ & $this, 'onMenuCreated'], 10, 3);
        add_action('edit_term_taxonomy', [ & $this, 'onMenuBeforeEdit'], 10, 2);
        add_action('edited_term', [ & $this, 'onMenuEdited'], 10, 3);
        add_action('delete_term_taxonomy', [ & $this, 'onMenuDeleted'], 10, 1);
        add_action('save_post', [ & $this, 'onMenuItemAdded'], 10, 3);
    }

    public function onMenuCreated($term_id, $tt_id, $taxonomy)
    {
        if ($taxonomy != 'nav_menu') {
            return;
        }
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $term             = get_term($term_id, $taxonomy);
        $term_title       = $term->name;

        $this->alertManager->event(
            [
                'alert_code'        => 2040,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "Menu created",
                'alert_description' => "User ($current_username) created ($term_title)",
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'term_id'           => $term_id,
                'term_taxonomy_id'  => $tt_id,
                'type'              => $taxonomy,
                'term_title'        => $term_title,
            ]
        );
    }

    public function onMenuDeleted($tt_id)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $term             = get_term($tt_id);
        $term_title       = $term->name;
        $taxonomy         = $term->taxonomy;
        if ($taxonomy != 'nav_menu') {
            return;
        }

        $this->alertManager->event(
            [
                'alert_code'        => 2041,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "Menu deleted",
                'alert_description' => "User ($current_username) deleted ($term_title)",
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'type'              => $taxonomy,
                'term_title'        => $term_title,
            ]
        );
    }

    public function onMenuBeforeEdit($tt_id, $taxonomy)
    {
        if ($taxonomy != 'nav_menu') {
            return;
        }
        $menus       = [];
        $term        = get_term($tt_id);
        $term_title  = $term->name;
        $term_id     = $term->term_id;
        $term_object = get_objects_in_term($tt_id, $taxonomy);
        foreach ($term_object as $object) {
            $meta_post = get_post_meta($object, '_menu_item_object_id', true);
            $post      = get_post($meta_post);
            $menus[]   = $post->post_title;
        }

        $updated_term = [
            'term_title' => $term_title,
            'menus'      => $menus,
        ];

        $this->beforeUpdateTermData[$term_id] = $updated_term;

    }

    public function onMenuEdited($term_id, $tt_id, $taxonomy)
    {

        if ($taxonomy != 'nav_menu') {
            return;
        }
        $menus            = [];
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $term             = get_term($tt_id);
        $term_title       = $term->name;
        $term_object      = get_objects_in_term($tt_id, $taxonomy);
        foreach ($term_object as $object) {
            $meta_post = get_post_meta($object, '_menu_item_object_id', true);
            $post      = get_post($meta_post);
            $menus[]   = $post->post_title;
        }

        $new_term = [
            'term_title' => $term_title,
            'menus'      => $menus,
        ];

        $old_term = $this->beforeUpdateTermData[$tt_id];

        $this->alertManager->event(
            [
                'alert_code'        => 2042,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "Menu updated",
                'alert_description' => "User ($current_username) updated menu (" . $old_term['term_title'] . ") to ($term_title)",
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'type'              => $taxonomy,
                'old_menu_title'    => $old_term['term_title'],
                'new_menu_title'    => $term_title,
                'old_menus'         => $old_term,
                'new_menus'         => $new_term,
            ]
        );
    }

    public function onMenuItemAdded($post_id, $post, $update)
    {
        if ($post->post_type != 'nav_menu_item') {
            return;
        }

        if ($update == true && $post->post_title == '') {
            return;
        }

        $statement = $update ? 'modified' : 'inserted';

        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'        => 2043,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "Menu post updated",
                'alert_description' => "User ($current_username) $statement $post->post_title menu",
                'sensor'            => self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'post_id'           => $post_id,
                'post_status'       => $post->post_status,
                'post_type'         => $post->post_type,
                'post_title'        => $post->post_title,
            ]
        );
    }
}
