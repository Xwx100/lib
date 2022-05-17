<?php
/**
 * 功能：
 *
 * @date 2022/2/10
 * @author xu
 */

namespace Lib\Common;

trait Password
{

    /**
     * @return string
     * @date 2022/2/10
     */
    public function hash($pwd)
    {
        return password_hash($pwd, PASSWORD_DEFAULT);
    }

    /**
     * @param $pwd
     * @param $hash
     * @return bool
     * @date 2022/2/10
     */
    public function verify($pwd, $hash)
    {
        return password_verify($pwd, $hash);
    }
}