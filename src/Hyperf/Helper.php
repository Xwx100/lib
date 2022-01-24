<?php
/**
 * 功能：
 *
 * @date 2022/1/20
 * @author xu
 */

namespace Lib\Hyperf;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Utils\Context;
use Hyperf\Validation\ValidatorFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Lib\HelperInterface;
use Hyperf\Rpc\Context as ContextRpc;

use Lib\Hyperf\Func\Index as FuncIndex;
use Lib\Hyperf\Arr\Index as ArrIndex;
use Lib\Hyperf\Storage\Index as StorageIndex;
use Lib\Hyperf\ResponseFormat\Index as ResponseFormatIndex;

abstract class Helper implements HelperInterface
{

    /**
     * @return ArrIndex
     * @date 2022/1/23
     */
    public static function arr()
    {
        return self::singleton(ArrIndex::class);
    }

    /**
     * @return FuncIndex
     * @date 2022/1/23
     */
    public static function func()
    {
        return self::singleton(FuncIndex::class);
    }

    /**
     * @return ServerRequestInterface
     * @date 2022/1/23
     */
    public static function request()
    {
        return Context::get(ServerRequestInterface::class);
        return static::singleton(ServerRequestInterface::class);
    }

    /**
     * @return ResponseInterface
     * @date 2022/1/23
     */
    public static function response()
    {
        return Context::get(ResponseInterface::class);
        return static::singleton(ResponseInterface::class);
    }

    public static function responseFormat()
    {
        return self::singleton(ResponseFormatIndex::class);
    }

    /**
     * @return LoggerFactory
     * @date 2022/1/23
     */
    public static function log()
    {
        return self::singleton(LoggerFactory::class);
    }

    /**
     * @return ValidatorFactory
     * @date 2022/1/23
     */
    public static function validator()
    {
        return self::singleton(ValidatorFactory::class);
    }

    /**
     * StorageIndex
     * @return Container|mixed
     * @date 2022/1/23
     */
    public static function storage()
    {
        return self::singleton(StorageIndex::class);
    }

    /**
     * @return Context
     * @date 2022/1/23
     */
    public static function storageCo()
    {
        return self::singleton(Context::class);
    }

    /**
     * @return ContextRpc
     * @date 2022/1/23
     */
    public static function storageCoData()
    {
        return self::singleton(ContextRpc::class);
    }

    public static function singleton($abstract, $concrete = null)
    {
        if (!static::container()->has($abstract) && $concrete) {
            static::container()->set($abstract, $concrete);
        }
        return static::container()->get($abstract);
    }

    public static function singletonArgs($abstract, $concrete = null)
    {
        $newClass = "{$abstract}_" . md5(serialize($arguments));
        return static::singleton($newClass, $newClass);
    }

    /**
     * @return Container
     */
    public static function container()
    {
        return ApplicationContext::getContainer();
    }
}
