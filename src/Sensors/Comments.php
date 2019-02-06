<?php
namespace TDE\Adeptus\Sensors;

use \TDE\Adeptus\LogEventAwareInterface;

class Comments implements \TDE\Adeptus\SensorInterface
{
    private $alertManager;

    public static $name = 'CommentsSensor';

    public function __construct(LogEventAwareInterface $alertManager)
    {
        $this->alertManager = $alertManager;
    }

    public function hookEvents()
    {
        add_action('wp_insert_comment', [ & $this, 'onCommentCreated'], 10, 2);
        add_action('wp_set_comment_status', [ & $this, 'onStatusChanged'], 10, 2);
    }

    public function onCommentCreated($id, $commentdata)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;

        $this->alertManager->event(
            [
                'alert_code'        => 2080,
                'alert_level'       => \Psr\Log\LogLevel::INFO,
                'alert_title'       => "New Comment Posted",
                'alert_description' => "\"" . $commentdata->comment_author . "\"  left a comment for post #" . $commentdata->comment_post_ID,
                'sensor'            => self::$name,
                'author_email'      => $commentdata->comment_author_email,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => !empty($current_username) ? $current_username : '-',
            ]
        );
    }

    public function onStatusChanged($id, $comment_status)
    {
        $current_user     = wp_get_current_user();
        $current_username = $current_user->user_login;
        $current_username = !empty($current_username) ? $current_username : '-';

        $this->alertManager->event(
            [
                'alert_code'        => 2081,
                'alert_level'       => \Psr\Log\LogLevel::WARNING,
                'alert_title'       => "Comment Status Changed",
                'alert_description' => "\"" . $current_username . "\" set the comment #$id status to \"" . $comment_status . "\"",
                'sensor'            => self::$name,
                'comment_id'        => $id,
                'current_user_id'   => $current_user->ID,
                'current_user_name' => $current_username,
            ]
        );
    }
}
