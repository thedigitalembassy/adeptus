<?php
namespace TDE\Adeptus\Loggers;

use \Psr\Log\LogLevel;

class SyslogLogger extends AbstractLogger
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
        $logLevelMapping = [
            LogLevel::EMERGENCY => LOG_EMERG,
            LogLevel::ALERT     => LOG_ALERT,
            LogLevel::CRITICAL  => LOG_CRIT,
            LogLevel::ERROR     => LOG_ERR,
            LogLevel::WARNING   => LOG_WARNING,
            LogLevel::NOTICE    => LOG_NOTICE,
            LogLevel::INFO      => LOG_INFO,
            LogLevel::DEBUG     => LOG_DEBUG,
        ];

        $logLevel = $logLevelMapping[$level] ?? LOG_INFO;

        openlog('adeptus', LOG_NDELAY | LOG_PID, LOG_USER);
        syslog($logLevelMapping[$level], 'Event: ' . $message . ' for ' . $context['domain']);
    }
}
