<?php
/**
 * 功能：
 *
 * @date 2022/1/22
 * @author xu
 */

namespace Lib\Hyperf\Arr;

use Hyperf\Utils\Arr;
use Lib\Common\ArrTree;

/**
 * 功能：
 *
 * @date 2022/1/25
 * @author xu
 */
class Index extends Arr
{
    use ArrTree;

    /**
     * 解析 空间
     * @return array
     * @date 2022/3/23
     */
    public function parseMysqlGen($v)
    {
        return unpack('x/x/x/x/corder/Ltype/dlat/dlon', $v);
    }
}
