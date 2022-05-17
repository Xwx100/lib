<?php
/**
 * 功能：
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Laravel\ResponseFormat;

use Lib\Common\ArrayObject;
use Lib\Common\ResponseFormat;
use Lib\Laravel\Helper;


class Index extends ResponseFormat
{
    public function __construct()
    {
        $this->setRequestId(Helper::storage()->request_id);
    }
}
