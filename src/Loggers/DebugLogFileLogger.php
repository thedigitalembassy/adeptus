<?php
namespace TDE\Adeptus\Loggers;

class DebugLogFileLogger extends AbstractLogger
{
    private $uploadDir;
    private $logFileName;

    public function __construct($uploadDir = WP_CONTENT_DIR, $logFileName = 'debug.log')
    {
        $this->logFileName = $logFileName;
        $this->uploadDir   = $uploadDir;
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
        $logMessage = '[' . date('d-M-Y H:i:s e') . '] Event: ' . $message . ' for ' . $context['domain'] . "\n";

        @file_put_contents(
            $this->uploadDir . '/' . $this->logFileName,
            $logMessage,
            FILE_APPEND
        );
    }
}
