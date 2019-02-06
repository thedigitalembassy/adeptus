<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Options implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'OptionsSensor';

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('updated_option', [ & $this, 'onOptionUpdates'], 10, 3);
    }

    public function onOptionUpdates($option, $oldvalue, $newvalue)
    {
        if (strpos($option, 'woocommerce_') !== false) {
            return;
        }

        if (strpos($option, 'wpseo_') !== false) {
            return $this->onYoastOptionUpdates($option, $oldvalue, $newvalue);
        }

        if (strpos($option, 'options_') !== false) {
            return $this->onACFOptionUpdates($option, $oldvalue, $newvalue);
        }

        $whitelist_options = apply_filters('adeptus/sensors/options/whitelist', [
            // General
            'blogname',
            'blogdescription',
            'siteurl',
            'home',
            'admin_email',
            'users_can_register',
            'default_role',
            'timezone_string',
            'date_format',
            'time_format',
            'start_of_week',

            // Writing
            'use_smilies',
            'use_balanceTags',
            'default_category',
            'default_post_format',
            'mailserver_url',
            'mailserver_login',
            'mailserver_pass',
            'default_email_category',
            'ping_sites',

            // Reading
            'show_on_front',
            'page_on_front',
            'page_for_posts',
            'posts_per_page',
            'posts_per_rss',
            'rss_use_excerpt',
            'blog_public',

            // Discussion
            'default_pingback_flag',
            'default_ping_status',
            'default_comment_status',
            'require_name_email',
            'comment_registration',
            'close_comments_for_old_posts',
            'close_comments_days_old',
            'thread_comments',
            'thread_comments_depth',
            'page_comments',
            'comments_per_page',
            'default_comments_page',
            'comment_order',
            'comments_notify',
            'moderation_notify',
            'comment_moderation',
            'comment_whitelist',
            'comment_max_links',
            'moderation_keys',
            'blacklist_keys',
            'show_avatars',
            'avatar_rating',
            'avatar_default',

            // Media
            'thumbnail_size_w',
            'thumbnail_size_h',
            'thumbnail_crop',
            'medium_size_w',
            'medium_size_h',
            'large_size_w',
            'large_size_h',
            'uploads_use_yearmonth_folders',

            // Permalinks
            'permalink_structure',
            'category_base',
            'tag_base',

            // Widgets
            'sidebars_widgets',
            'widget_custom_html',
            'widget_pages',
            'widget_calendar',
            'widget_tag_cloud',
            'widget_nav_menu',
            'widget_media_audio',
            'widget_media_image',
            'widget_media_video',

            // timezone
            'gmt_offset',
        ]);

        if (!in_array($option, $whitelist_options)) {
            return;
        }

        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'        => 2060,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => 'Option updated',
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

    public function onYoastOptionUpdates($option, $oldvalue, $newvalue)
    {
        //exclude cache validator options
        if (strpos($option, 'cache_validator') !== false) {
            return;
        }

        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $modified_fields = array_diff_assoc($oldvalue, $newvalue);

        foreach ($modified_fields as $k => $v) {
            if (strpos($k, 'hideeditbox') !== false || strpos($k, 'breadcrumbs-blog-remove') !== false) {
                unset($modified_fields[$k]);
            }
        }

        $this->alertManager->event(
            [
                'alert_code'          => 2065,
                'alert_level'         => \Psr\Log\LogLevel::WARNING,
                'alert_title'         => 'SEO Option updated',
                'alert_description'   => "User $current_username changed ($option)",
                'sensor'              => 'Seo' . self::$name,
                'current_user_id'     => $current_user->ID,
                'current_user_name'   => $current_user->user_login,
                'object_name'         => $option,
                'option_data_changed' => $modified_fields,
            ]
        );
    }

    public function onACFOptionUpdates($option, $oldvalue, $newvalue)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'        => 2066,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => 'ACF Option updated',
                'alert_description' => "User $current_username changed ($option)",
                'sensor'            => 'ACF' . self::$name,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_user->user_login,
                'object_name'       => $option,
                'old_value'         => (object) [json_encode($oldvalue)],
                'new_value'         => (object) [json_encode($newvalue)],
            ]
        );
    }
}
