<?php
/**
 * 功能：接口
 *
 * @date 2022/1/19
 * @author xu
 */

namespace Lib;


interface HelperInterface
{
    /**
     * 容器-请求
     * @return mixed
     * @date 2022/1/20
     */
    public static function request();

    /**
     * 容器-响应
     * @return mixed
     * @date 2022/1/20
     */
    public static function response();

    /**
     * 容器-响应-格式化
     * @return mixed
     * @date 2022/1/21
     */
    public static function responseFormat();

    /**
     * 容器-日志
     * @return mixed
     * @date 2022/1/20
     */
    public static function log();

    /**
     * 容器-通用函数
     * @return mixed
     * @date 2022/1/21
     */
    public static function func();

    /**
     * 单例
     * @param $abstract
     * @param $concrete
     * @param $arguments
     * @return mixed
     * @date 2022/1/20
     */
    public static function singleton($abstract, $concrete = null, $arguments = null);

    /**
     * 单例-参数化
     * @param $abstract
     * @param $concrete
     * @param $arguments
     * @return mixed
     * @date 2022/1/21
     */
    public static function singletonArgs($abstract, $concrete = null, $arguments = null);

    /**
     * 存储(进程变量)
     * @return mixed
     * @date 2022/1/20
     */
    public static function storage();


    /**
     * 容器
     * @return mixed
     * @date 2022/1/20
     */
    public static function container();
}