<?php


namespace Lib\Laravel;


/**
 * 功能：单例化 相比于 类来说 实例有很多回调机制可以利用 例如魔术方法等
 *
 * @property array $maps static
 *
 * @date 2022/1/19
 * @author xu
 */
trait Instance
{

    public static $maps = null;


    /**
     * @return \Illuminate\Contracts\Foundation\Application|mixed
     */
    public static function container()
    {
        return app();
    }

    /**
     * @param string $name
     * @return string
     * @date 2022/1/19
     */
    public static function getClass(string $name): string
    {
        return static::$maps[$name] ?? '';
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @date 2022/1/19
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function __callStatic($name, $arguments)
    {
        app()->singletonIf((static::getClass($name)));
        return app()->make((static::getClass($name)), $arguments);
    }
}
