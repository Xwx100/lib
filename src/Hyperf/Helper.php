<?php
/**
 * 功能：
 *
 * @date 2022/1/20
 * @author xu
 */

namespace Lib\Hyperf;

use Hyperf\Logger\Logger;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Context;
use Hyperf\Utils\Parallel;
use Hyperf\Validation\ValidatorFactory;
use Lib\HelperInterfaceHyperf;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Lib\HelperInterface;
use Hyperf\Rpc\Context as ContextRpc;

use Lib\Hyperf\Func\Index as FuncIndex;
use Lib\Hyperf\Arr\Index as ArrIndex;
use Lib\Hyperf\Storage\Index as StorageIndex;
use Lib\Hyperf\ResponseFormat\Index as ResponseFormatIndex;
use Lib\Hyperf\Query\Index as QueryIndex;


abstract class Helper implements HelperInterface,HelperInterfaceHyperf
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
     * @return Collection
     * @date 2022/1/25
     */
    public static function collection()
    {
        return self::singleton(Collection::class);
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
        return static::context()->get(ServerRequestInterface::class);
    }

    /**
     * @return ResponseInterface
     * @date 2022/1/23
     */
    public static function response()
    {
        return static::context()->get(ResponseInterface::class);
    }

    /**
     * @return ResponseFormatIndex
     * @date 2022/1/26
     */
    public static function responseFormat()
    {
        return self::singletonCo(ResponseFormatIndex::class);
    }

    /**
     * @return Logger
     * @date 2022/1/23
     */
    public static function log()
    {
        /**
         * @var LoggerFactory $loggerFactory
         */
        $loggerFactory = self::singleton(LoggerFactory::class);
        return $loggerFactory->get();
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
     * @return StorageIndex
     * @date 2022/1/23
     */
    public static function storage()
    {
        return self::singletonCo(StorageIndex::class);
    }

    /**
     * @return Parallel
     * @date 2022/1/26
     */
    public static function parallel()
    {
        return make(Parallel::class);
    }

    /**
     * @return QueryIndex
     * @date 2022/2/6
     */
    public static function queryIndex()
    {
        return self::singleton(QueryIndex::class);
    }

    /**
     * @return Context
     * @date 2022/1/25
     */
    public static function context()
    {
        return self::singleton(Context::class);
    }

    /**
     * @return ContextRpc
     * @date 2022/1/25
     */
    public static function contextRpc()
    {
        return self::singleton(ContextRpc::class);
    }

    public static function singletonCo($abstract, $concrete = null, $arguments = null)
    {
        if (!self::context()->has($abstract)) {
            self::context()->set(
                $abstract,
                make($abstract)
            );
        }
        return self::context()->get($abstract);
    }

    public static function singletonArgs($abstract, $concrete = null, $arguments = null)
    {
        $newClass = "{$abstract}_" . md5(serialize($arguments));
        return static::singleton($newClass, $newClass);
    }

    public static function singleton($abstract, $concrete = null, $arguments = null)
    {
        if (!static::container()->has($abstract) && $concrete) {
            static::container()->set($abstract, $concrete);
        }
        return static::container()->get($abstract);
    }


    /**
     * @return \Psr\Container\ContainerInterface|Container
     */
    public static function container()
    {
        return ApplicationContext::getContainer();
    }
}
