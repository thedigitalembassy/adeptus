<?php
namespace TDE\Adeptus\Loggers;

class ErrorLogLogger extends AbstractLogger
{
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
        $logMessage = 'Event: ' . $message . ' for ' . $context['domain'];
        error_log($logMessage . ' Context = ' . json_encode($context));
    }
}
