<?php
/**
 * 功能：
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Laravel;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Lib\Laravel\File\BosAdapterFactory;
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
        $this->app->bindIf(SpanSerializer::class, JsonV2Serializer::class);
        /**
         * 新增bos配置
         */
        Storage::extend('bos', function ($app, $config) {
            return (new BosAdapterFactory())->make($config);
        });
    }
}
