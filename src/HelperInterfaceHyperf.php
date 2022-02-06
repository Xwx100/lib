<?php
/**
 * 功能：
 *
 * @date 2022/1/28
 * @author xu
 */

namespace Lib;

interface HelperInterfaceHyperf
{

    /**
     * 容器-存储(协程变量)
     * @return mixed
     * @date 2022/1/20
     */
    public static function context();

    /**
     * 容器-存储(协程变量-数据)
     * @return mixed
     * @date 2022/1/20
     */
    public static function contextRpc();

}