<?php
namespace TDE\Adeptus;

class Logger
{
    private $sensors;
    private $alertManager;

    public function __construct($alertManager)
    {
        $this->alertManager = $alertManager;

        if ($this->optionEnabled('adeptus_logstash_enabled') == '1') {
            $logger = new \TDE\Adeptus\Loggers\LogStashLogger(
                get_option('adeptus_logstash_url'),
                get_option('adeptus_logstash_endpoint'),
                get_option('adeptus_logstash_method')
            );

            $this->alertManager->setLogger($logger);
        }

        if ($this->optionEnabled('adeptus_syslog_enabled')) {
            $this->alertManager->setLogger(new \TDE\Adeptus\Loggers\SyslogLogger());
        }

        if ($this->optionEnabled('adeptus_errorlog_enabled')) {
            $this->alertManager->setLogger(new \TDE\Adeptus\Loggers\ErrorLogLogger());
        }

        if ($this->optionEnabled('adeptus_debuglog_enabled')) {
            $this->alertManager->setLogger(new \TDE\Adeptus\Loggers\DebugLogFileLogger());
        }

        do_action('adeptus/loggers', $this->alertManager);

        $this->sensors = apply_filters('adeptus/sensors', [
            new Sensors\Core($this->alertManager),
            new Sensors\Posts($this->alertManager),
            new Sensors\Taxonomies($this->alertManager),
            new Sensors\Users($this->alertManager),
            new Sensors\Plugins($this->alertManager),
            new Sensors\Menus($this->alertManager),
            new Sensors\Attachments($this->alertManager),
            new Sensors\Options($this->alertManager),
            new Sensors\Themes($this->alertManager),
            new Sensors\Errors($this->alertManager),
            new Sensors\Comments($this->alertManager),
            new Sensors\Woocommerce($this->alertManager),
        ], $this->alertManager);
    }

    public function hookEvents()
    {
        foreach ($this->sensors as $sensor) {
            $sensor->hookEvents();
        }
    }

    private function optionEnabled($option)
    {
        $value = get_option($option, false);

        if (empty($value)) {
            return false;
        }

        if (is_array($value)) {
            $value = $value[0];
        }

        return $value == 'yes';
    }
}
