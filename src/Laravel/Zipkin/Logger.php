<?php

namespace Lib\Laravel\Zipkin;

use Illuminate\Log\Logger as LogLogger;
use Zipkin\Reporters\SpanSerializer;
use Zipkin\Reporter;

class Logger implements Reporter {

    public $serializer = null;
    public $logger = null;

    public function __construct(SpanSerializer $serializer = null, LogLogger $logger = null)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    public function  report(array $spans): void
    {
        if (\count($spans) === 0) {
            return;
        }
        $this->logger->debug($this->serializer->serialize($spans));
    }
}