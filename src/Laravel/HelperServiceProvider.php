<?php
/**
 * 功能：
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Laravel;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Lib\Laravel\Zipkin\Middleware;

class HelperServiceProvider extends ServiceProvider
{

    public function register()
    {
    }

    public function boot()
    {
        /**
         * 中间件-zipkin + 配置唯一请求ID
         */
        $this->app->make(Kernel::class)->prependMiddleware(Middleware::class);
        /**
         * 配置唯一请求ID
         */
        Helper::storage()->setRequestId(Helper::zipkin()->tracerId);
    }
}
