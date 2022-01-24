<?php
/**
 * 功能：
 *
 * @date 2022/1/20
 * @author xu
 */

namespace Lib\Laravel\Storage;

use Lib\Common\ArrayObject;

/**
 * 功能：
 * @property string $request_id 唯一请求ID
 * @property array $login 存储用户数据
 * @property bool $access 存储是否需要验证权限
 *
 * @method $this setRequestId($requestId)
 * @method $this setLogin(array $login)
 * @method $this setAccess($access)
 * @date 2022/1/21
 * @author xu
 */
class Index
{
    use ArrayObject;
    protected $origin = [];
}
