<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Spiral\Goridge\RelayInterface as Relay;
use Spiral\Goridge\RPC;

class GoridgeRpcHandler extends AbstractProcessingHandler
{
    protected RPC $rpc;

    public function __construct(RPC $rpc, $level = \Monolog\Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->rpc = $rpc;
    }

    protected function write(array $record): void
    {
        // for now just log to stderr, downside is, that it will always show as "WARN"
        fputs(STDERR, $record['formatted']);

        // TODO: find out how to use it properly and do the RPC call instead of stderr
        // $this->rpc->call('debugger.SendDebugInfo', $record['formatted']);
    }
}
