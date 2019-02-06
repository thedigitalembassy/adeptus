<?php
namespace TDE\Adeptus;

use \Psr\Log\LoggerAwareInterface;

interface LogEventAwareInterface extends LoggerAwareInterface
{
    public function event(array $payload);
}
