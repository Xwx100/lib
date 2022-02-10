<?php
/**
 * 功能：
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Common;

trait Func
{
    /**
     * @param bool $cond
     * @param $msg
     * @param int $code
     * @return bool
     * @throws \Exception
     */
    public function throwIf(bool $cond, $msg, int $code = 1): bool
    {
        if (!$cond) {
            return $cond;
        }
        if (is_callable($msg)) {
            $msg = call_user_func($msg);
        }
        throw new \Exception($msg, $code);
    }


    /**
     * @param bool $cond
     * @param string $msg
     * @param array $data
     * @return bool|null
     */
    public function throwData(bool $cond, string $msg = '', array $data = []): ?bool
    {
        if ($cond) {
            throw new class($msg, $data) extends \Exception {
                public $data = [];

                public function __construct($message = "", $data = [], $code = 0)
                {
                    $this->data = $data;
                    parent::__construct($message, $code);
                }
            };
        }
        return $cond;
    }

    /**
     * 毫秒整数
     * @return int
     * @date 2022/1/24
     */
    public function microSeconds()
    {
        return (int)(microtime(true) * 1000 * 1000);
    }

    /**
     * 深拷贝
     * @param $obj
     * @return mixed
     * @date 2022/1/26
     */
    public function deepCopy($obj)
    {
        return unserialize(serialize($obj));
    }

    public function isDate($d): bool
    {
        if (!$d) {
            return false;
        }
        $arr = date_parse($d);
        return checkdate($arr['month'] ?? null, $arr['day'] ?? null, $arr['year'] ?? null);
    }

    /**
     * @param $format
     * @param ...$context
     * @return string
     * @date 2022/2/10
     */
    public function sprintf($format, ...$context)
    {
        foreach ($context as &$ctx) {
            if (is_scalar($ctx)) {
                continue;
            }
            if (is_object($ctx) && method_exists($ctx, 'toArray'))
            {
                $ctx = $ctx->toArray();
            }
            $ctx = json_encode($ctx, JSON_UNESCAPED_UNICODE);
        }
        return sprintf($format, ...$context);
    }
}
