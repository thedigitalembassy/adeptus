<?php
namespace TDE\Adeptus;

class Settings
{
    public function __construct($plugin_basename)
    {
        add_action('admin_menu', array($this, 'registerOptionsPage'));
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'addActionLinks'), 10, 4);
    }

    public function addActionLinks($links)
    {
        $setting_link = array(
            '<a href="' . admin_url('options-general.php?page=adeptus') . '">Settings</a>',
        );
        return array_merge($links, $setting_link);
    }

    public function registerOptionsPage()
    {
        add_options_page('Log Settings', 'Log Settings', 'manage_options', 'adeptus', array($this, 'adeptusOptionsPage'));
    }

    public function adeptusOptionsPage()
    {
        if (isset($_POST['adeptus_logstash_url'])) {
            update_option('adeptus_logstash_url', $_POST['adeptus_logstash_url']);
        }

        if (isset($_POST['adeptus_logstash_logger'])) {
            update_option('adeptus_logstash_logger', 1);
        } else {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                update_option('adeptus_logstash_logger', 0);
            }
        }

        if (isset($_POST['adeptus_file_logger'])) {
            update_option('adeptus_file_logger', 1);
        } else {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                update_option('adeptus_file_logger', 0);
            }
        }

        if (isset($_POST['adeptus_curl_method'])) {
            update_option('adeptus_curl_method', $_POST['adeptus_curl_method']);
        }

        ?>
        <div>
            <h2>Adeptus: Activity Audit Logger Settings</h2>
            <form method="post" action="">
                <?php settings_fields('adeptus_options_page');?>

                <p>
                    <input <?=get_option('adeptus_logstash_logger') == '1' ? 'checked' : '';?> type="checkbox" name="adeptus_logstash_logger"><label for="adeptus_logstash_logger">Enable Logstash Logger</label>
                </p>
                <p>
                    <input <?=get_option('adeptus_file_logger') == '1' ? 'checked' : '';?> type="checkbox" name="adeptus_file_logger"><label for="adeptus_file_logger">Enable File Logger</label>
                </p>
                <p>
                    <label for="adeptus_logstash_url">Logstash URL:</label>
                </p>
                <input type="text" id="adeptus_logstash_url" name="adeptus_logstash_url"  class="widefat" style="max-width: 700px" value="<?=get_option('adeptus_logstash_url');?>" />

                <p>
                    <label for="adeptus_logstash_url">Logstash Curl Method:</label>
                </p>
                <select name="adeptus_curl_method">
                    <option value="php" <?=get_option('adeptus_curl_method') == 'php' ? 'selected' : '';?>>PHP</option>
                    <option value="shell" <?=get_option('adeptus_curl_method') == 'shell' ? 'selected' : '';?>>SHELL</option>
                </select>
                <?php submit_button();?>
            </form>
        </div><?php
    }
}
