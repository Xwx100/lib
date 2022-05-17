<?php
/**
 * 功能：
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Hyperf\ResponseFormat;

use Lib\Common\ResponseFormat;
use Lib\Hyperf\Helper;


class Index extends ResponseFormat
{

    public function __construct()
    {
        $this->setRequestId(Helper::storage()->request_id);
    }
}
