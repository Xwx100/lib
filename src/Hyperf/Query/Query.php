<?php
/**
 * 功能：
 *
 * @date 2022/1/26
 * @author xu
 */

namespace Lib\Hyperf\Query;

use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Contracts\Arrayable;
use Lib\Common\ArrayObject;
use Lib\Hyperf\Helper;

/**
 * 前端 转换 数据查询
 * 禁止单例化
 * Class Query
 *
 * @property Builder     $query
 * @property QueryParams $params
 * @property array       $options
 * @property array       $rules
 *
 * @method $this setQuery($query)
 * @method $this setParams(QueryParams $params)
 * @method $this setOptions(array $options)
 * @method $this setRules(array $rules)
 */
class Query
{
    use ArrayObject;

    protected $origin = [
        'query' => null,
        'rules' => [],
        'params' => null,
        'options' => [],
    ];

    /**
     * @return \Hyperf\Contract\LengthAwarePaginatorInterface|Builder
     */
    public function run()
    {
        $cos = ['pByField', 'pByWhere', 'pByGroupBy', 'pByOrderBy'];
        $p = Helper::parallel();
        foreach ($cos as $co) {
            $p->add(function () use ($co) {
                $this->$co();
            });
        }
        $p->wait();

        return $this->pByPage();
    }


    public function pByField(): self
    {
        $info = $this->params->field;
        if (empty($info)) {
            return $this;
        }
        $info = array_map(function ($f) {
            $s = ['group_concat', 'as'];
            foreach ($s as $v) {
                if (is_string($f) && false !== strpos($f, $v)) {
                    return Db::raw($f);
                }
            }
            return $f;
        }, $info);
        $this->query->select($info);
        return $this;
    }


    public function pByWhere(): self
    {
        $info = $this->params->where;
        if (empty($info)) {
            return $this;
        }

        $rules = $this->rules;
        foreach ($info as $k => $v) {
            $rule = Arr::get($rules, $k);
            if (empty($rule)) {
                // 默认规则
                if (is_array($v) || $v instanceof Arrayable) {
                    $v0 = Arr::get($v, '0');
                    $v1 = Arr::get($v, '1');
                    $next = function () use ($k, $v) {
                        $this->query->whereIn($k, $v);
                    };

                    if (!isset($v0) || !isset($v1)) {
                        $next();
                    } elseif (in_array($v0, ['<', '>'])) {
                        $this->query->where($k, $v0, $v1);
                    } elseif ($v0 === 'between') {
                        $this->query->whereBetween($k, $v1);
                    } elseif (Helper::func()->isDate($v0) && Helper::func()->isDate($v1)) {
                        $this->query->whereBetween($k, $v);
                    }
                } elseif (is_scalar($v)) {
                    $this->query->where($k, $v);
                }
            } elseif (is_callable($rule)) {
                $rule($k, $v, $this->query, $info);
            }
        }
        return $this;
    }

    public function pByGroupBy(): self
    {
        $info = $this->params->group_by;
        if (empty($info)) {
            return $this;
        }

        $this->query->groupBy($info);
        return $this;
    }

    public function pByOrderBy(): self
    {
        $info = $this->params->order_by;
        if (empty($info)) {
            return $this;
        }

        foreach ($info as $item) {
            $sortField = Arr::get($item, 'sort_field');
            if ($sortField) {
                $sortType = Arr::get($item, 'sort_type');
                $this->query->orderBy($sortField, $sortType ?: 'asc');
            }
        }
        return $this;
    }

    /**
     * @return \Hyperf\Contract\LengthAwarePaginatorInterface|Builder
     */
    public function pByPage()
    {
        $info = $this->params->page_info;
        if (empty($info)) {
            return $this->query;
        }
        // 终止
        if (!empty($info['stop'])) {
            return $this->query;
        }
        $page = intval($info['page'] ?? 1);
        $pageSize = intval($info['page_size'] ?? 30);
        return $this->query->paginate($pageSize, ['*'], 'page', $page);
    }
}