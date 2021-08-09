<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\Logger;

use Monolog\Handler\AbstractProcessingHandler;

class StderrHandler extends AbstractProcessingHandler
{
    protected function write(array $record): void
    {
        fputs(STDERR, $record['formatted']);
    }
}
