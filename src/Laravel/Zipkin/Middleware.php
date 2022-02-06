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
        $zipKin = Helper::zipkin();
        $zipKin->register();
        $zipKin->rootSpan->tag('request', Helper::func()->sprintf('%s', Helper::request()->input()));
        $response = $next($request);
        $zipKin->rootSpan->tag('response', Helper::func()->sprintf('%s', $response));
        $zipKin->end();
        return $response;
    }
}
