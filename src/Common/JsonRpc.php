<?php
/**
 * 功能：
 *
 * @date 2022/1/24
 * @author xu
 */

namespace Lib\Common;

trait JsonRpc
{
    /**
     * laravel-jsonrpc 发送标识
     * @return string
     * @date 2022/1/24
     */
    public function sendFlag()
    {
        return 'zipkin';
    }

    /**
     * hyperf-jsonrpc 接收标识
     * @return string
     * @date 2022/1/24
     */
    public function receiveHyperfFlag()
    {
        return 'tracer.carrier';
    }
}
