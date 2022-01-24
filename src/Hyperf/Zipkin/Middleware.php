<?php
/**
 * 功能：
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

class Middleware implements MiddlewareInterface
{
    use JsonRpc;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getAttribute('data');
        $tracerContext = $data[$this->sendFlag()];
        Helper::storageCoData()->set($this->receiveHyperfFlag(), $tracerContext);
        return $handler->handle($request);
    }
}
