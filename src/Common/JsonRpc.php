<?php
/**
 * 功能：
 *
 * @date 2022/1/24
 * @author xu
 */

namespace Lib\Common;


use Zipkin\Span;

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

    /**
     * 日志设施
     * @return int
     * @date 2022/2/10
     */
    public function facility()
    {
        return LOG_LOCAL4;
    }

    /**
     * 注册日志syslog
     * @return \Zipkin\Reporters\Log
     * @date 2022/2/10
     */
    public function registerReporterSyslog()
    {
        return $reporter = new \Zipkin\Reporters\Log(
            (new \Monolog\Logger(env('APP_NAME')))
                ->setHandlers([
                    (new \Monolog\Handler\SyslogHandler(env('APP_NAME'), $this->facility()))
                ])
        );
    }

    /**
     * @param string $f
     * @param $context
     * @param $robots
     * @return Span
     * @date 2022/2/10
     */
    public function log2Tech(string $f, $context = [], $robots = ['x_fen_dui_dingding'])
    {
        return $this->log('debug', $f, $context, [], [], $robots);
    }

    /**
     * @param string $f
     * @param $context
     * @param $users
     * @param $roles
     * @return Span
     * @date 2022/2/10
     */
    public function log2Job(string $f, $context = [], $users = [], $roles = [])
    {
        return $this->log('debug', $f, $context, $users, $roles);
    }

    /**
     * @param string $f
     * @param array $context
     * @return Span
     * @date 2022/2/10
     */
    public function logMysql(string $f, $context = [])
    {
        return $this->log('mysql', $f, $context);
    }

}
