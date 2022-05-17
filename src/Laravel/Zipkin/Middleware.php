<?php
/**
 * 功能：
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Laravel\Zipkin;

use Illuminate\Http\JsonResponse;
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
        Helper::storage()->setRequestId($zipKin->tracerId);
        $zipKin->rootSpan->tag('request.uri', Helper::request()->url());
        $zipKin->rootSpan->tag('request.params', Helper::func()->sprintf('%s', Helper::request()->input()));
        $response = $next($request);
        if ($response instanceof JsonResponse) {
            $zipKin->rootSpan->tag('response', Helper::func()->sprintf('%s', $response->getContent()));
        }
        $zipKin->end();
        return $response;
    }
}
