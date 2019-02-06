<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Taxonomies implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'TaxonomiesSensor';

    public $beforeUpdateTermData = [];

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('create_term', [ & $this, 'onTermCreated'], 10, 3);
        add_action('edit_term_taxonomy', [ & $this, 'onTermBeforeEdit'], 10, 2);
        add_action('edited_term', [ & $this, 'onTermEdited'], 10, 3);
        add_action('delete_term_taxonomy', [ & $this, 'onTermDeleted'], 10, 1);
    }

    public function onTermCreated($term_id, $tt_id, $taxonomy)
    {
        if ($taxonomy == 'nav_menu') {
            return;
        }

        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $term             = get_term($term_id, $taxonomy);
        $term_title       = $term->name;

        $this->alertManager->event(
            [
                'alert_code'        => 2010,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "Term created",
                'alert_description' => "User ($current_username) created ($taxonomy) ($term_title)",
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

    public function onTermDeleted($tt_id)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $term             = get_term($tt_id);
        $term_title       = $term->name;
        $term_id          = $term->term_id;
        $taxonomy         = $term->taxonomy;
        if ($taxonomy == 'nav_menu') {
            return;
        }

        $this->alertManager->event(
            [
                'alert_code'        => 2012,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "Term deleted",
                'alert_description' => "User ($current_username) deleted ($taxonomy) ($term_title)",
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

    public function onTermBeforeEdit($tt_id, $taxonomy)
    {
        if ($taxonomy == 'nav_menu') {
            return;
        }

        $term              = get_term($tt_id);
        $term_title        = $term->name;
        $term_id           = $term->term_id;
        $term_parent       = $term->parent;
        $parent_term       = get_term($term_parent);
        $term_parent_title = !empty($parent_term->name) ? $parent_term->name : '';
        $updated_term      = [
            'term_title'        => $term_title,
            'term_parent_id'    => $term_parent,
            'term_parent_title' => $term_parent_title,
        ];

        $this->beforeUpdateTermData[$term_id] = $updated_term;
    }

    public function onTermEdited($term_id, $tt_id, $taxonomy)
    {

        if ($taxonomy == 'nav_menu') {
            return;
        }

        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $term             = get_term($tt_id);
        $term_title       = $term->name;
        $old_term         = $this->beforeUpdateTermData[$tt_id];

        $term_parent       = $term->parent;
        $parent_term       = get_term($term_parent);
        $term_parent_title = !empty($parent_term->name) ? $parent_term->name : '';

        $this->alertManager->event(
            [
                'alert_code'         => 2012,
                'alert_level'        => \Psr\Log\LogLevel::INFO,
                'alert_title'        => "Term updated",
                'alert_description'  => "User ($current_username) updated ($taxonomy) (" . $old_term['term_title'] . ") to ($term_title)",
                'sensor'             => self::$name,
                'current_user_id'    => $current_user->ID,
                'current_user_name'  => $current_user->user_login,
                'term_id'            => $term_id,
                'term_taxonomy_id'   => $tt_id,
                'type'               => $taxonomy,
                'old_term_title'     => $old_term['term_title'],
                'new_term_title'     => $term_title,
                'old_term_parent'    => $old_term['term_parent_title'],
                'new_term_parent'    => $term_parent_title,
                'old_term_parent_id' => $old_term['term_parent_id'],
                'new_term_parent_id' => $term_parent,
            ]
        );
    }
}
