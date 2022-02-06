<?php
/**
 * 功能：注入tracer
 *
 * @date 2022/1/24
 * @author xu
 */

namespace Lib\Hyperf\Zipkin;

use Lib\Common\JsonRpc;
use Lib\Hyperf\Helper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Coroutine;
use Zipkin\Propagation\B3;

class Middleware implements MiddlewareInterface
{
    use JsonRpc;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * 注入 zipkin
         */
        $data = $request->getAttribute('data');
        $tracerContext = $data[$this->sendFlag()];
        Helper::contextRpc()->set($this->receiveHyperfFlag(), $tracerContext);
        /**
         * 注入 约定好的requestId
         */
        Helper::storage()->setRequestId($tracerContext[strtolower(B3::TRACE_ID_NAME)]);
        return $handler->handle($request);
    }
}
