<?php
/**
 * 功能：
 *
 * @date 2022/1/20
 * @author xu
 */

namespace Lib\Laravel;

use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Lib\HelperInterface;
use Lib\Laravel\ResponseFormat\Index as ResponseFormatIndex;
use Lib\Laravel\Storage\Index as StorageIndex;
use Lib\Laravel\Func\Index as FuncIndex;
use Lib\Laravel\Zipkin\Index as ZipkinIndex;
use Lib\Laravel\HyperfClient\Index as HyperfClientIndex;

abstract class Helper implements HelperInterface
{

    /**
     * @return \Illuminate\Http\Request
     * @date 2022/1/21
     */
    public static function request()
    {
        return static::container()->make('request');
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     * @date 2022/1/21
     */
    public static function response()
    {
        return static::container()->make('response');
    }

    /**
     * @return ResponseFormatIndex
     * @date 2022/1/22
     */
    public static function responseFormat()
    {
        return static::singleton(ResponseFormatIndex::class);
    }

    /**
     * @return HyperfClientIndex
     * @date 2022/1/22
     */
    public static function hyperfClient()
    {
        return static::singleton(HyperfClientIndex::class);
    }

    /**
     * @return FuncIndex
     * @date 2022/1/21
     */
    public static function func()
    {
        return static::singleton(FuncIndex::class);
    }

    /**
     * @return ZipkinIndex
     * @date 2022/1/21
     */
    public static function zipkin()
    {
        return static::singleton(ZipkinIndex::class);
    }

    public static function singleton($abstract, $concrete = null, $arguments = [])
    {
        app()->singletonIf($abstract, $concrete);
        return app()->make($abstract, $arguments);
    }

    public static function singletonArgs($abstract, $concrete = null, $arguments = []) {
        $newClass = "{$abstract}_" . md5(serialize($arguments));
        return static::singleton($newClass, $concrete ?: $abstract, $arguments);
    }


    /**
     * @return StorageIndex
     * @date 2022/1/21
     */
    public static function storage()
    {
        return static::singleton(StorageIndex::class);
    }

    public static function container()
    {
        return app();
    }
}
