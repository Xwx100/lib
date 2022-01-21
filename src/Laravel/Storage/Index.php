<?php
/**
 * 功能：
 *
 * @date 2022/1/20
 * @author xu
 */

namespace Lib\Laravel\Storage;

use Lib\Laravel\Common\ArrayObject;

/**
 * 功能：
 * @property        $request_id
 *
 * @method $this setRequestId($requestId)
 * @date 2022/1/21
 * @author xu
 */
class Index extends ArrayObject
{
    protected $origin = [
        'request_id' => ''
    ];
}