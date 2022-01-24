<?php
/**
 * 功能：有关联关系的字符串 traceId-跟踪id kind-服务端、客户端 tag-跟踪标签mysql、redis
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Laravel\Zipkin;

use Lib\Laravel\Helper;
use Zipkin\DefaultTracing;
use Zipkin\Endpoint;
use Zipkin\Propagation\CurrentTraceContext;
use Zipkin\Propagation\DefaultSamplingFlags;
use Zipkin\Propagation\Map;
use Zipkin\Propagation\ServerHeaders;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Span;
use Zipkin\Tracer;
use Zipkin\TracingBuilder;
use Zipkin\Propagation\TraceContext;
use const Zipkin\Kind\CLIENT;

/**
 * 功能：request => debug|mysql => (debug|mysql).child
 *
 * @date 2022/1/24
 * @author xu
 */
class Index
{
    /**
     * @var DefaultTracing
     * @date 2022/1/24
     */
    public $tracering = null;

    /**
     * @var Tracer
     * @date 2022/1/21
     */
    public $tracer = null;
    /**
     * @var string
     * @date 2022/1/22
     */
    public $tracerId = null;
    /**
     * @var Span
     * @date 2022/1/24
     */
    public $rootSpan = null;

//    const CHILD_SPAN_DEBUG = 'debug';
//    const CHILD_SPAN_MYSQL = 'mysql';
//    /**
//     * @var array
//     * @date 2022/1/24
//     */
//    public $childSpan = [
//        CHILD_SPAN_DEBUG => null,
//        CHILD_SPAN_MYSQL => null,
//    ];

    /**
     * 注册
     * @return $this
     * @date 2022/1/21
     */
    public function register()
    {
        $appName = env('APP_NAME', __METHOD__);
        $endpoint = Endpoint::create($appName);
        $sampler = BinarySampler::createAsAlwaysSample();
        $builder = TracingBuilder::create();
        $reporter = call_user_func([$this, "registerReporter" . ucfirst(env('LOG_SLS_ALI_REPORT_STYLE'))]);
        $currentTraceText = new CurrentTraceContext();
        $currentTraceText->createScopeAndRetrieveItsCloser(TraceContext::createAsRoot(DefaultSamplingFlags::createAsEmpty()));

        $this->tracering = $builder
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->havingCurrentTraceContext($currentTraceText)
            ->build();

        $this->tracer = $this->tracering->getTracer();
        $this->rootSpan = $this->tracer->newTrace();
        $this->rootSpan->start(Helper::func()->microSeconds());
        $this->rootSpan->setName($appName);
        $this->rootSpan->setKind(CLIENT);
        $this->tracer->openScope($this->rootSpan);
        $this->tracerId = $this->rootSpan->getContext()->getTraceId();
        return $this;
    }


    public function registerReporterCurl()
    {
        return new Http([
            'endpoint_url' => env('LOG_SLS_ALI_REPORT_URL'),
            'headers' => [
                'x-sls-otel-project' => env('LOG_SLS_ALI_PROJECT'),
                'x-sls-otel-instance-id' => env('LOG_SLS_ALI_INSTANCE_ID'),
                'x-sls-otel-ak-id' => env('LOG_SLS_ALI_AK_ID'),
                'x-sls-otel-ak-secret' => env('LOG_SLS_ALI_AK_SECRET'),
            ]
        ]);
    }

    public function registerReporterSyslog()
    {
        return $reporter = new \Zipkin\Reporters\Log(Helper::log()->getLogger());
    }

    public function logDebug(string $value)
    {
        return $this->log('debug', $value);
    }

    public function logMysql(string $value)
    {
        return $this->log('mysql', $value);
    }

    public function log(string $key, string $value)
    {
        $childRootSpan = $this->childRootSpan($key);
        $child = $this->tracer->nextSpan($childRootSpan->getContext());
        $child->start(Helper::func()->microSeconds());
        $child->tag($key, $value);
        return $child;
    }

    public function childRootSpan($name)
    {
        if (!isset($this->childSpan[$name])) {
            /**
             * @var Span
             */
            $span = $this->tracer->nextSpan();
            $span->start(Helper::func()->microSeconds());
            $span->setName($name);
            $this->childSpan[$name] = $span;
        }
        return $this->childSpan[$name];
    }

    /**
     * 固定请求头格式-发送
     * @return array
     * @date 2022/1/24
     */
    public function headersSend()
    {
        static $headers = null;
        if (empty($headers))
        {
            $injector = $this->tracering->getPropagation()->getInjector(new Map());
            $injector($this->rootSpan->getContext(), $headers);
        }
        return $headers;
    }
}
