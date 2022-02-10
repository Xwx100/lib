<?php
/**
 * 功能：有关联关系的字符串 traceId-跟踪id kind-服务端、客户端 tag-跟踪标签mysql、redis
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Laravel\Zipkin;

use Lib\Common\JsonRpc;
use Lib\Laravel\Helper;
use Zipkin\DefaultTracing;
use Zipkin\Endpoint;
use Zipkin\Propagation\CurrentTraceContext;
use Zipkin\Propagation\DefaultSamplingFlags;
use Zipkin\Propagation\Map;
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
    use JsonRpc;

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

    public function end()
    {
        $this->rootSpan->finish(Helper::func()->microSeconds());
        $this->tracer->flush();
    }


    public function registerReporterCurl()
    {
        return new Http(null, [
            'endpoint_url' => env('LOG_SLS_ALI_REPORT_URL'),
            'headers' => [
                'x-sls-otel-project' => env('LOG_SLS_ALI_PROJECT'),
                'x-sls-otel-instance-id' => env('LOG_SLS_ALI_INSTANCE_ID'),
                'x-sls-otel-ak-id' => env('LOG_SLS_ALI_AK_ID'),
                'x-sls-otel-ak-secret' => env('LOG_SLS_ALI_AK_SECRET'),
            ]
        ]);
    }

    public function log(string $name, string $f, $context = [], $users = [], $roles = [], $robots = [])
    {
        $value = Helper::func()->sprintf($f, $context);
        $child = $this->tracer->nextSpan($this->rootSpan->getContext());
        $child->setName($name);
        $child->start(Helper::func()->microSeconds());
        $child->tag('msg', $value);
        if ($users) {
            $child->tag('notify.users', Helper::func()->sprintf('%s', $users));
        }
        if ($roles) {
            $child->tag('notify.roles', Helper::func()->sprintf('%s', $roles));
        }
        if ($robots) {
            $child->tag('notify.robots', Helper::func()->sprintf('%s', $robots));
        }
        $child->finish(Helper::func()->microSeconds());
        $child->flush();
        return $child;
    }

    /**
     * 固定请求头格式-发送
     * @return array
     * @date 2022/1/24
     */
    public function headersSend()
    {
        static $headers = [];
        if (empty($headers)) {
            $injector = $this->tracering->getPropagation()->getInjector(new Map());
            $injector($this->rootSpan->getContext(), $headers);
        }
        return $headers;
    }
}
