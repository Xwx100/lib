<?php
// +----------------------------------------------------------------------
// | xu: 2021/8/16 0016
// +----------------------------------------------------------------------

namespace Lib\Hyperf\Query;

use Lib\Common\ArrayObject;

/**
 * 前端 转换 数据查询
 * Class Query
 *
 * @property array   $field
 * @property array   $where
 * @property array   $group_by
 * @property array   $order_by
 * @property array   $page_info
 * @property array   $having
 *
 * @method $this setField($query)
 * @method $this setWhere(array $params)
 * @method $this setOptions(array $options)
 * @method $this setRules(array $rules)
 */
class QueryParams
{
    use ArrayObject;

    protected $origin = [
        'field' => [],
        'where' => [],
        'group_by' => [],
        'order_by' => [],
        'having' => [],
        'page_info' => [
            'page' => 1,
            'page_size' => 20
        ]
    ];
}