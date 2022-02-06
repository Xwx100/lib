<?php
/**
 * 功能：
 *
 * @date 2022/1/20
 * @author xu
 */

namespace Lib\Hyperf\Storage;


use Lib\Common\ArrayObject;

/**
 * @property string $request_id 唯一请求ID
 *
 * @method $this setRequestId($requestId)
 * @date 2022/1/21
 * @author xu
 */
class Index
{
    use ArrayObject;
    protected $origin = [];
}
