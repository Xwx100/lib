<?php
/**
 * 功能：
 *
 * @date 2022/1/24
 * @author xu
 */

namespace Lib\Hyperf;

use Lib\Hyperf\Zipkin\ZipkinTracerFactory;

class ConfigProvider
{
    public function __invoke()
    {
        return array_merge([
            'middlewares' => [
                'jsonrpc' => [
                    \Lib\Hyperf\Zipkin\Middleware::class,
                    \Hyperf\Tracer\Middleware\TraceMiddleware::class,
                ]
            ],
        ], ZipkinTracerFactory::config());
    }
}