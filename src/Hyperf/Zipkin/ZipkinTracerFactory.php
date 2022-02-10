<?php
/**
 * 功能：
 *
 * @date 2022/2/9
 * @author xu
 */

namespace Lib\Hyperf\Zipkin;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Tracer\Adapter\HttpClientFactory;
use Hyperf\Tracer\Contract\NamedFactoryInterface;
use Lib\Common\JsonRpc;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;
use ZipkinOpenTracing\Tracer;

class ZipkinTracerFactory implements NamedFactoryInterface
{
    use JsonRpc;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var HttpClientFactory
     */
    private $clientFactory;

    /**
     * @var string
     */
    private $prefix = 'opentracing.zipkin.';

    public function __construct(ConfigInterface $config, HttpClientFactory $clientFactory)
    {
        $this->config = $config;
        $this->clientFactory = $clientFactory;
    }

    public function make(string $name): \OpenTracing\Tracer
    {
        if (! empty($name)) {
            $this->prefix = "opentracing.tracer.{$name}.";
        }
        [$app, $options, $sampler, $style] = $this->parseConfig();
        $endpoint = Endpoint::create($app['name'], $app['ipv4'], $app['ipv6'], $app['port']);
        if ($style === 'syslog')
        {
            $reporter = $this->registerReporterSyslog();
        } else {
            $reporter = new Http($this->clientFactory, $options);
        }
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();
        return new Tracer($tracing);
    }

    public function parseConfig(): array
    {
        // @TODO Detect the ipv4, ipv6, port from server object or system info automatically.
        return [
            $this->getConfig('app', [
                'name' => 'skeleton',
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ]),
            $this->getConfig('options', [
                'timeout' => 1,
            ]),
            $this->getConfig('sampler', BinarySampler::createAsAlwaysSample()),
            $this->getConfig('report_style', 'curl'),
        ];
    }

    public function getConfig(string $key, $default)
    {
        return $this->config->get($this->prefix . $key, $default);
    }

    public static function config()
    {
        return [
            'opentracing' => [
                'tracer' => [
                    'zipkin_all' => [
                        'driver' => self::class,
                        'report_style' => env('TRACER_REPORT_STYLE', 'curl'),
                        'app' => [
                            'name' => env('APP_NAME', 'skeleton'),
                            // Hyperf will detect the system info automatically as the value if ipv4, ipv6, port is null
                            'ipv4' => '127.0.0.1',
                            'ipv6' => null,
                            'port' => 9301,
                        ],
                        'options' => [
                            'endpoint_url' => env('LOG_SLS_ALI_REPORT_URL', 'http://localhost:9411/api/v2/spans'),
                            'timeout' => env('ZIPKIN_TIMEOUT', 1),
                            'headers' => [
                                'x-sls-otel-project' => env('LOG_SLS_ALI_PROJECT'),
                                'x-sls-otel-instance-id' => env('LOG_SLS_ALI_INSTANCE_ID'),
                                'x-sls-otel-ak-id' => env('LOG_SLS_ALI_AK_ID'),
                                'x-sls-otel-ak-secret' => env('LOG_SLS_ALI_AK_SECRET'),
                            ]
                        ],
                        'sampler' => BinarySampler::createAsAlwaysSample(),
                    ],
                ]
            ]
        ];
    }
}