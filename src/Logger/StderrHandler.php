<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class StderrHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        fputs(STDERR, $record->formatted);
    }
}
