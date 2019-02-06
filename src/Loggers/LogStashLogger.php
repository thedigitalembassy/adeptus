<?php
namespace TDE\Adeptus\Loggers;

class LogStashLogger extends AbstractLogger
{
    private $hostname;
    private $endpoint;
    private $curlMethod;

    const CURL_METHOD_PHP   = 'php';
    const CURL_METHOD_SHELL = 'shell';

    public function __construct($hostname, $endpoint, $curlMethod = self::CURL_METHOD_PHP)
    {
        $this->hostname   = $hostname;
        $this->endpoint   = $endpoint;
        $this->curlMethod = $curlMethod;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if (empty($this->hostname) || empty($this->endpoint)) {
            error_log('Adeptus:LogStashLogger - Empty hostname or endpoint.');
            return;
        }

        $payload = json_encode($context);

        if ($this->curlMethod == self::CURL_METHOD_PHP) {
            $this->logUsingPHPCurl($level, $message, $payload);
        } else if ($this->curlMethod == self::CURL_METHOD_SHELL) {
            $this->logUsingShellCurl($level, $message, $payload);
        } else {
            throw new Exception("Invalid curl method", 1);
        }
    }

    /**
     * Logs event using PHP curl extension.
     * Note this is slower than using a shell fork.
     * @param  string $level   See \Psr\Log\LogLevel
     * @param  string $message Log message
     * @param  string $payload JSON encoded payload
     * @return void
     */
    private function logUsingPHPCurl($level, $message, $payload)
    {
        if (!function_exists('curl_init')) {
            error_log('Adeptus:LogStashLogger - PHP curl is not installed.');
            return;
        }

        $ch = curl_init();

        if (!$ch) {
            error_log('Adeptus:LogStashLogger - Failed to initialize curl.');
            return;
        }

        @curl_setopt($ch, CURLOPT_URL, $this->hostname . $this->endpoint);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Prevents output from being echoed
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        @curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        @curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        $headers   = array();
        $headers[] = "Content-Type: application/json";
        @curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = @curl_exec($ch);

        if (@curl_errno($ch)) {
            error_log('Adeptus:LogStashLogger - Curl error ' . curl_error($ch) . '.');
            return;
        }

        @curl_close($ch);
    }

    /**
     * Logs event using shell fork
     * @param  string $level   See \Psr\Log\LogLevel
     * @param  string $message Log message
     * @param  string $payload JSON encoded payload
     * @return void
     */
    private function logUsingShellCurl($level, $message, $payload)
    {
        if (!function_exists('exec')) {
            error_log('Adeptus:LogStashLogger - Function `exec` is not available.');
            return;
        }

        $payload = escapeshellarg($payload);
        $url     = escapeshellarg($this->hostname . $this->endpoint);

        $cmd = "curl -XPUT -H 'Content-Type: application/json'";
        $cmd .= " '$url' -d '$payload'";

        // In non-dev environments ignore output of command for performance reasons
        if (!defined('WP_ENV') || WP_ENV != 'development') {
            $cmd .= " > /dev/null 2>&1 &";
        }

        @exec($cmd, $output, $exit);

        if ($exit != 0) {
            error_log("Adeptus:LogStashLogger - Curl command failed with exit code '$exit'. \n" . implode("\n", $output));
            return;
        }
    }
}
