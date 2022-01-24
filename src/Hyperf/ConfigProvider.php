<?php
/**
 * 功能：
 *
 * @date 2022/1/24
 * @author xu
 */

namespace Lib\Hyperf;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'middlewares' => [
                'jsonrpc' => [
                    \Lib\Hyperf\Zipkin\Middleware::class,
                    \Hyperf\Tracer\Middleware\TraceMiddleware::class,
                ]
            ]
        ];
    }
}