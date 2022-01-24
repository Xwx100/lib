<?php
/**
 * 功能：
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Laravel\Zipkin;

use Lib\Laravel\Helper;

class Middleware
{

    /**
     * @param $request
     * @param \Closure $next
     * @return void
     * @date 2022/1/21
     */
    public function handle($request, \Closure $next)
    {
        Helper::zipkin()->register();
        $response = $next($request);
        Helper::zipkin()->rootSpan->finish(Helper::func()->microSeconds());
        Helper::zipkin()->tracer->flush();
        return $response;
    }
}
