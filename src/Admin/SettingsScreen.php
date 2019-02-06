<?php
namespace TDE\Adeptus\Admin;

class SettingsScreen
{
    private $pluginSlug = 'adeptus_settings';

    public function __construct($plugin_path)
    {
        // Hook into the admin menu
        add_action('admin_menu', [$this, 'createPluginSettingsPage']);

        // Add Settings and Fields
        add_action('admin_init', [$this, 'setupSections']);
        add_action('admin_init', [$this, 'setupFields']);
    }

    public function createPluginSettingsPage()
    {
        // Add the menu item and page
        $page_title = 'Adeptus Settings';
        $menu_title = 'Log Settings';
        $capability = 'manage_options';
        $slug       = $this->pluginSlug;
        $callback   = [$this, 'pluginSettingsPageContent'];
        add_options_page($page_title, $menu_title, $capability, $slug, $callback);
    }

    public function pluginSettingsPageContent()
    {
        ?>
        <div class="wrap">
            <h2>Adeptus Settings</h2>
            <?php
            if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
                $this->adminNoticeSuccess();
            }

            if (defined('WP_ADEPTUS_LOGGING_DISABLED') && WP_ADEPTUS_LOGGING_DISABLED == true) {
                $this->adminNoticeDisabled();
            }
            ?>
            <form method="POST" action="options.php">
                <?php
                settings_fields($this->pluginSlug);
                do_settings_sections($this->pluginSlug);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function adminNoticeDisabled()
    {
        ?>
        <div class="notice notice-warning">
            <p>Logging has been disabled globally by the `WP_ADEPTUS_LOGGING_DISABLED` constant.</p>
        </div>
        <?php
    }

    public function adminNoticeSuccess()
    {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div>
        <?php
    }

    public function setupSections()
    {
        add_settings_section('debuglog_section', 'Debug.log', [$this, 'sectionCallback'], $this->pluginSlug);
        add_settings_section('syslog_section', 'Syslog', [$this, 'sectionCallback'], $this->pluginSlug);
        add_settings_section('errorlog_section', 'PHP Error Log', [$this, 'sectionCallback'], $this->pluginSlug);
        add_settings_section('logstash_section', 'Logstash', [$this, 'sectionCallback'], $this->pluginSlug);
    }

    public function sectionCallback($arguments)
    {
        // Display content above section form
        return;
    }

    public function shellCurlAvailable()
    {
        if (!function_exists('exec')) {
            return false;
        }

        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            return false;
        }

        @exec('curl -V', $output, $exit);

        return $exit == 0;
    }

    public function setupFields()
    {
        $fields = [
            [
                'uid'      => 'adeptus_debuglog_enabled',
                'label'    => 'Enabled',
                'section'  => 'debuglog_section',
                'type'     => 'select',
                'datatype' => 'boolean',
                'options'  => [
                    'yes' => 'Yes',
                    'no'  => 'No',
                ],
                'default'  => ['no'],
            ],
            [
                'uid'      => 'adeptus_syslog_enabled',
                'label'    => 'Enabled',
                'section'  => 'syslog_section',
                'type'     => 'select',
                'datatype' => 'boolean',
                'options'  => [
                    'yes' => 'Yes',
                    'no'  => 'No',
                ],
                'default'  => ['no'],
            ],
            [
                'uid'      => 'adeptus_errorlog_enabled',
                'label'    => 'Enabled',
                'section'  => 'errorlog_section',
                'type'     => 'select',
                'datatype' => 'boolean',
                'options'  => [
                    'yes' => 'Yes',
                    'no'  => 'No',
                ],
                'default'  => ['no'],
            ],
            [
                'uid'      => 'adeptus_logstash_enabled',
                'label'    => 'Enabled',
                'section'  => 'logstash_section',
                'type'     => 'select',
                'datatype' => 'boolean',
                'options'  => [
                    'yes' => 'Yes',
                    'no'  => 'No',
                ],
                'default'  => ['no'],
            ],
            [
                'uid'         => 'adeptus_logstash_url',
                'label'       => 'Server URL',
                'section'     => 'logstash_section',
                'type'        => 'text',
                'placeholder' => 'https://logstashserver:8081',
                'supplimental' => 'For HTTP basic auth use the following format "https://usr:pass@logstashserver:8081".'
            ],
            [
                'uid'         => 'adeptus_logstash_endpoint',
                'label'       => 'Endpoint',
                'section'     => 'logstash_section',
                'type'        => 'text',
                'placeholder' => '/events/event/1',
            ],
            [
                'uid'          => 'adeptus_logstash_method',
                'label'        => 'Method',
                'section'      => 'logstash_section',
                'type'         => 'select',
                'options'      => [
                    'php'   => 'PHP Curl' . (function_exists('curl_init') ? '' : ' (Not available)'),
                    'shell' => 'Shell Fork Curl' . ($this->shellCurlAvailable() ? '' : ' (Not available)'),
                ],
                'default'      => ['php'],
                'supplimental' => 'The shell method is faster, but requires the curl command to be executable by the web server process.',
            ],
        ];

        foreach ($fields as $field) {
            add_settings_field($field['uid'], $field['label'], [$this, 'fieldCallback'], $this->pluginSlug, $field['section'], $field);
            register_setting($this->pluginSlug, $field['uid'], [
                'type' => !empty($field['datatype']) ? $field['datatype'] : 'string',
            ]);
        }
    }

    public function fieldCallback($arguments)
    {
        $value = get_option($arguments['uid']);

        if (!$value) {
            $value = isset($arguments['default']) ? $arguments['default'] : '';
        }

        switch ($arguments['type']) {
            case 'text':
            case 'password':
            case 'number':
                printf('<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" class="regular-text" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'] ?? '', $value);
                break;
            case 'textarea':
                printf('<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value);
                break;
            case 'select':
            case 'multiselect':
                if (!empty($arguments['options']) && is_array($arguments['options'])) {
                    $value = is_array($value) ? $value : [$value];
                    $attributes     = '';
                    $options_markup = '';
                    foreach ($arguments['options'] as $key => $label) {
                        $options_markup .= sprintf('<option value="%s" %s>%s</option>', $key, selected($value[array_search($key, $value, true)], $key, false), $label);
                    }
                    if ($arguments['type'] === 'multiselect') {
                        $attributes = ' multiple="multiple" ';
                    }
                    printf('<select name="%1$s' . ($arguments['type'] === 'multiselect' ? '[]' : '') . '" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup);
                }
                break;
            case 'radio':
            case 'checkbox':
                if (!empty($arguments['options']) && is_array($arguments['options'])) {
                    $options_markup = '';
                    $iterator       = 0;
                    foreach ($arguments['options'] as $key => $label) {
                        $iterator++;
                        $options_markup .= sprintf('<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked($value[array_search($key, $value, true)], $key, false), $label, $iterator);
                    }
                    printf('<fieldset>%s</fieldset>', $options_markup);
                }
                break;
        }

        if (!empty($arguments['helper'])) {
            printf('<span class="helper"> %s</span>', $arguments['helper']);
        }

        if (!empty($arguments['supplimental'])) {
            printf('<p class="description">%s</p>', $arguments['supplimental']);
        }
    }
}