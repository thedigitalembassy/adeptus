<?php
namespace TDE\Adeptus;

use \Psr\Log\LoggerInterface;
use \Psr\Log\LogLevel;

class AlertManager implements LogEventAwareInterface
{
    private $loggers = [];
    private $enabled = true;

    /**
     * Logs event with installed logger
     * @param  array  $payload
     * @return void
     */
    public function event(array $payload = array())
    {
        if (!$this->enabled) {
            return;
        }

        $alertTitle = isset($payload['alert_title']) ? $payload['alert_title'] : 'Unknown event';
        $alertLevel = isset($payload['alert_level']) ? $payload['alert_level'] : LogLevel::INFO;

        // Additional values
        $payload['wp_home']            = get_home_url(get_main_site_id());
        $payload['domain']             = get_home_url();
        $payload['client_ip']          = $this->getClientIP();
        $payload['client_remote_addr'] = $_SERVER['REMOTE_ADDR'];
        $payload['alert_level_code']   = $this->getAlertLevelCode($alertLevel);

        // Apply filters
        $alertTitle = apply_filters('adeptus/alert_manager/alert_title', $alertTitle, $payload);
        $alertLevel = apply_filters('adeptus/alert_manager/alert_level', $alertLevel, $payload);
        $payload    = apply_filters('adeptus/alert_manager/context', $payload);
        $loggers    = apply_filters('adeptus/alert_manager/loggers', $this->loggers, $payload);

        // Run all registered loggers
        if (!empty($loggers)) {
            foreach ($loggers as $logger) {
                $logger->log($alertLevel, $alertTitle, $payload);
            }
        }
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->loggers[] = $logger;
    }

    public function disableLogging()
    {
        $this->enabled = false;
    }

    public function enableLogging()
    {
        $this->enabled = true;
    }

    /**
     * Covert alert level into code
     * Same as syslog codes see https://en.wikipedia.org/wiki/Syslog
     * Returns 100 for unknown alert levels
     *
     * @param  string $alertLevel  Alert level
     * @return int                 Alert code
     */
    public function getAlertLevelCode($alertLevel)
    {
        $levelCodes = [
            'emergency' => 0,
            'alert'     => 1,
            'critical'  => 2,
            'error'     => 3,
            'warning'   => 4,
            'notice'    => 5,
            'info'      => 6,
            'debug'     => 7,
        ];

        return isset($levelCodes[$alertLevel]) ? $levelCodes[$alertLevel] : 100;
    }

    /**
     * Attempts to get the client's real IP address
     *
     * @return string IP Address
     */
    public function getClientIP()
    {
        $ipaddress = '';

        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ipaddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }
}
