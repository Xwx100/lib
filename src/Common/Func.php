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
}