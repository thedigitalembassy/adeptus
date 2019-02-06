<?php
namespace TDE\Adeptus\Loggers;

class FileLogger extends AbstractLogger
{
    private $uploadDir;
    private $logFileName;

    public function __construct($uploadDir = '', $logFileName = 'adeptus.log')
    {
        $this->logFileName = $logFileName;

        if (empty($uploadDir)) {
            $wpUploadDir     = wp_upload_dir();
            $this->uploadDir = $wpUploadDir['basedir'];
        } else {
            $this->uploadDir = $uploadDir;
        }
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
        @file_put_contents(
            $this->uploadDir . '/' . $this->logFileName,
            "\n--------------------\n" . json_encode($context, JSON_PRETTY_PRINT),
            FILE_APPEND
        );
    }
}
