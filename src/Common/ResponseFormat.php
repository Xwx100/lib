<?php
/**
 * 功能：
 *
 * @date 2022/2/10
 * @author xu
 */

namespace Lib\Common;

/**
 * Class Res
 *
 * @property string $msg
 * @property array  $data
 * @property        $code
 * @property        $request_id
 *
 * @method $this setMsg($msg)
 * @method $this setData($data)
 * @method $this setCode($code)
 * @method $this setRequestId($requestId)
 *
 * @package X
 */
class ResponseFormat
{
    use ArrayObject;

    protected $origin = [
        'code' => 0,
        'data' => [],
        'msg' => '',
        'request_id' => ''
    ];

    const CODE_SUCCESS = 0;
    const CODE_FAIL = 1;
    const MSG_DEFAULT_SUCCESS = '成功';
    const MSG_DEFAULT_FAIL = '失败';

    public function makeOk($data = [], $msg = self::MSG_DEFAULT_SUCCESS): self
    {
        return $this->setCode(self::CODE_SUCCESS)->setMsg($msg)->setData($data);
    }

    public function makeFail($data = [], $msg = self::MSG_DEFAULT_FAIL): self
    {
        return $this->setCode(self::CODE_FAIL)->setMsg($msg)->setData($data);
    }

    public function isOk()
    {
        return $this->code === self::CODE_SUCCESS;
    }

    public function isFail()
    {
        return $this->isOk();
    }

    public function noEmpty()
    {
        return boolval($this->data);
    }

    public function empty()
    {
        return !$this->noEmpty();
    }
}
