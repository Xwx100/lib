<?php
/**
 * 功能：
 *
 * @date 2022/1/26
 * @author xu
 */

namespace Lib\Hyperf\Query;


/**
 * 前端 转换 数据查询
 * 禁止单例化
 * Class Query
 */
class Index
{
    /**
     * @return Query
     * @date 2022/1/26
     */
    public function query()
    {
        return make(Query::class);
    }

    /**
     * @return QueryParams
     * @date 2022/1/26
     */
    public function queryParams()
    {
        return make(QueryParams::class);
    }
}