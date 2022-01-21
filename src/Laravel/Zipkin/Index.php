<?php
/**
 * 功能：
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Laravel\Zipkin;

use Lib\Laravel\Helper;
use Zipkin\Endpoint;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;

class Index
{
    public $tracer = null;
    public $span = null;

    public function __construct()
    {
    }

    public function register()
    {
        $endpoint = Endpoint::create(env('APP_NAME', __METHOD__));
        $reporter = new \Zipkin\Reporters\Log(Helper::log()->getLogger());
        $sampler = BinarySampler::createAsAlwaysSample();
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();

        $this->tracer = $tracing->getTracer();
        $this->span = $this->tracer->newTrace();
        return $this;
        $t = $tracer->newTrace();
        return $t;
        $n->tag('test', '333');
        $newSpan = $tracer->nextSpan($n->getContext());
        $newSpan->tag('test', '1111');
        $tracer->flush();
    }

    public function nextSpan()
    {

    }
}