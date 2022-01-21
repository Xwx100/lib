<?php
/**
 * 功能：
 *
 * @date 2022/1/20
 * @author xu
 */

namespace Lib\Laravel;

use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Lib\HelperInterface;
use Lib\Laravel\Storage\Index as StorageIndex;
use Lib\Laravel\Func\Index as FuncIndex;

abstract class Helper implements HelperInterface
{
    /**
     * @return mixed
     * @see \Illuminate\Http\Request
     * @date 2022/1/21
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function request()
    {
        return static::container()->make('request');
    }

    /**
     * @return mixed
     * @date 2022/1/21
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function response()
    {
        return static::container()->make('response');
    }

    /**
     * @return Logger
     * @date 2022/1/21
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function log()
    {
        return static::container()->make('log');
    }

    /**
     * @return FuncIndex
     * @date 2022/1/21
     */
    public static function func()
    {
        return static::singleton(FuncIndex::class);
    }

    public static function singleton($abstract, $concrete = null, $arguments = null)
    {
        app()->singletonIf($abstract, $concrete);
        return app()->make($abstract, $arguments);
    }

    public static function singletonArgs($abstract, $concrete = null, $arguments = null) {
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