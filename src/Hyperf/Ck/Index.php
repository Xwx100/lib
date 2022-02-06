<?php


namespace Lib\Hyperf\Ck;


use ClickHouseDB\Client;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;
use Hyperf\Utils\Arr;
use Hyperf\DbConnection\Db;
use League\Flysystem\Filesystem;
use Hyperf\Utils\Str;



/**
 * 1. 字段描述 使用 自定义文件缓存：修改表结构时 记得清除缓存
 * 2. 自动字段过滤
 * 3. 自动获取带库表名
 * 4. 支持关联数组单个 insert && batchInsert && update && batchUpdate && batchUpdateOrCreate
 * 5. update 必须有更新条件 & 防止跨类型筛选 & 自动判断是否需要更新并过滤出需要更新的字段
 * 6. 支持 mysql 语法 => ck 语法 (待完善)
 * Class Ck
 */
trait Index
{
    /**
     * 经测试 判断不了
     * @param array $row
     * @return \ClickHouseDB\Statement
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function insert(array $row): \ClickHouseDB\Statement
    {
        return $this->getClient()->insertAssocBulk($this->getDbTable(), Arr::only($row, $this->getTableColumns()));
    }

    /**
     * 批量插入
     * @param array $rows
     * @param array $options format.true => 自动转换类型
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function batchInsert(array $rows, array $options = []): bool
    {
        if (empty($rows) || empty($rows[0])) {
            return false;
        }
        $columns = array_intersect(array_keys($rows[0]), $this->getTableColumns());
        $props = $this->getTablePropByKey();

        $rows = array_map(function ($row) use ($columns, $props, $options) {
            $tmp = [];
            foreach ($columns as $column) {
                $tmp[$column] = $row[$column] ?? null;
            }
            if (!empty($options['format'])) {
                $tmp = $this->handleRowByProp($tmp, $props);
            }

            return $tmp;
        }, $rows);
        $args = $this->getClient()->prepareInsertAssocBulk($rows);
        /**
         * 经测试 判断不了
         */
        $this->getClient()->insert($this->getDbTable(), ...array_reverse($args));
        return true;
    }

    /**
     * 更新
     * @param array $row
     * @param array $where
     * @return \ClickHouseDB\Statement
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Throwable
     */
    public function update(array $row, array $where): \ClickHouseDB\Statement
    {
        $row = $this->getRowsFilterByColumns($row);
        $row = array_diff_assoc($row, $where);
        $where = $this->getRowsFilterByColumns($where);

        $ckSql = $this->query(function (Builder $query) use ($row, $where) {
            foreach ($where as $k => $v) {
                $query->where($k, '=', $v);
            }
        }, null, $row, 'update');

        return $this->getClient()->write($ckSql);
    }

    /**
     * 批量更新
     * @param array $rows
     * @param array $whereKeys
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Throwable
     */
    public function batchUpdate(array $rows, array $whereKeys)
    {
        foreach ($rows as $row) {
            $this->update($row, Arr::only($row, $whereKeys));
        }
    }

    /**
     * 批量 更新或新增
     * @param array $rows
     * @param array $whereKeys
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Throwable
     */
    public function batchUpdateOrCreate(array $rows, $whereKeys = [])
    {
        $insert = [];
        $update = [];
        $type = $this->getTableType();

        foreach ($rows as $row) {
            $sql = $this->query(function (Builder $query) use ($row, $whereKeys, $type) {
                foreach ($whereKeys as $whereKey) {
                    $value = $row[$whereKey];
                    $query->where($whereKey, $value);
                }
            });
            $exist = $this->getClient()->select($sql)->fetchOne();
            if ($exist) {
                // 判断是否需要更新
                if ($new = array_diff_assoc(array_intersect_key($row, $exist), $exist)) {
                    $update[] = array_merge($new, Arr::only($row, $whereKeys));
                }
            } else {
                $insert[] = $row;
            }
        }

        if ($insert) {
            $this->batchInsert($insert);
        }

        if ($update) {
            $this->batchUpdate($update, $whereKeys);
        }
    }

    /**
     * 获取所有数据列
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getTableColumns(): array
    {
        return array_column($this->getTableProp(), 'name') ?: $this->getTableProp();
    }

    /**
     * 获取所有数据类型
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getTableType(): array
    {
        return array_column($this->getTableProp(), 'type', 'name');
    }

    /**
     * 获取所有数据注释
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getTableComments(): array
    {
        return array_column($this->getTableProp(), 'comment');
    }

    /**
     * 获取字段属性
     * @param string $key
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getTablePropByKey($key = 'name'): array
    {
        $tmp = [];
        $prop = $this->getTableProp();
        foreach ($prop as $k => $v) {
            if (!empty($v[$key])) {
                $tmp[$v[$key]] = $v;
            }
        }

        return $tmp;
    }

    /**
     * 使用自定义文件缓存 好直接删目录
     * 缓存目录：storage/app/clickhouse/host/port/db_table.php
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getTableProp(): array
    {
        try {
            $storage = XHelper::container()->get(Filesystem::class);
            $filePath = $this->getFileCachePath();
            $rowsAndCache = function () use ($storage, $filePath) {
                $rows = $this->getClient()->select("DESC {$this->getDbTable()}")->rows();
                $content = var_export($rows, true);
                $storage->write($filePath, "<?php \n\nreturn {$content};");
                return $rows;
            };

            if (!$storage->has($filePath)) {
                return $rowsAndCache();
            }

            $rows = include "{$storage->getAdapter()->applyPathPrefix($filePath)}";
            if (empty($rows)) {
                return $rowsAndCache();
            }
            return $rows;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 缓存规则：host/port/db_table.php
     */
    public function getFileCachePath(): string
    {
        $config = $this->getDbConfig();
        return "{$config['host']}/{$config['port']}/{$this->getDbTable()}.php";
    }

    /**
     * 获取库表名
     * @return string
     */
    public function getDbTable(): string
    {
        return "{$this->getDatabase()}.{$this->getTable()}";
    }

    /**
     * 获取当前数据库
     * @return string
     */
    public function getDatabase(): string
    {
        return 'advertsdk';
    }

    /**
     * 获取当前表名
     * @return string
     */
    public function getTable(): string
    {
        return Str::snake(class_basename(get_called_class()));
    }

    /**
     * 获取连接类
     * @param string $key
     * @return \ClickHouseDB\Client
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getClient($key = 'clickhouse'): \ClickHouseDB\Client
    {
        $config = $this->getDbConfig($key);
        try {
            return XHelper::container()->make(Client::class, ['connectParams' => $config]);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 获取数据库配置
     * @param string $key
     * @return mixed
     */
    public function getDbConfig($key = 'clickhouse')
    {
        return [
            'host' => '106.13.1.86',
            'port' => '8123',
            'username' => 'default',
            'password' => ''
        ];
        $config = config($key);
        return $config[env('APP_ENV', 'local')];
    }

    /**
     * 单例化
     * @return static
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function single(): self
    {
        return XHelper::container()->make(get_called_class());
    }

    /**
     * 强制合并数据 去重 官方建议不能依赖
     * @return \ClickHouseDB\Statement
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function forceMerge(): \ClickHouseDB\Statement
    {
        return $this->getClient()->selectAsync("OPTIMIZE TABLE {$this->getDbTable()}");
    }

    /**
     * 因 clickhouse 百分之90 支持sql
     * 所以 借用 mysql => clickhouse
     * @param $callback
     * @param null $table
     * @param array $bindings 更新时必传
     * @param string $type
     * @return string
     * @throws \Throwable
     */
    public function query($callback, $table = null, $bindings = [], $type = 'select'): string
    {
        $query = Db::table($table ?: $this->getDbTable());
        $sql = '';
        if ($callback) {
            $sql = $callback($query);
            if (!is_string($sql)) {
                $sql = $this->queryToSql($query, $bindings, $type);
                $sql = $this->mysqlToCk($sql, $type);
            }
        }

        return $sql;
    }

    /**
     * mysql => clickhouse 兼容语法
     * @param $sql
     * @param string $type
     * @return string
     * @throws \Throwable
     */
    public function mysqlToCk($sql, $type = 'select'): string
    {
        if ($type === 'select') {

        } elseif ($type === 'update') {
            preg_match('/update .*? set (.*?)where(.*)/i', $sql, $match);
            list(, $update, $where) = $match;
            $sql = "ALTER TABLE {$this->getDbTable()} UPDATE {$update} WHERE {$where}";
        }

        return $sql;
    }

    /**
     * 查询器转sql
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $bindings
     * @param string $type
     * @return string
     * @throws \Throwable
     */
    public function queryToSql(Builder $query, $bindings = [], $type = 'select'): string
    {
        if ($type === 'select') {
            $sql = $query->getGrammar()->compileSelect($query);
            $bindings = $query->getBindings();
        } elseif ($type === 'update') {
            $sql = $query->getGrammar()->compileUpdate($query, $bindings);
            $bindings = $this->cleanBindings($query->getGrammar()->prepareBindingsForUpdate($query->getRawBindings(), $bindings));
        } elseif ($type === 'delete') {
            $sql = $query->getGrammar()->compileDelete($query);
            $bindings = $query->getBindings();
        } else {
            throw new \Exception("[Ck.queryToSql] type={$type} 错误");
        }

        $bindingKeys = [];
        if (false !== stripos($sql, '= ?')) {
            preg_match_all('/(.*?) = \?/', $sql, $keys);
            $keys = $keys[1];
            $keys = array_filter(array_map(function ($v) {
                $vv = explode(' ', $v);
                return trim(array_pop($vv), "'`,()");
            }, $keys));

            $bindingKeys = array_combine($keys, $bindings);
            $bindingKeys = $this->changeRowType($bindingKeys);
        }

        $sql = str_replace('?', '%s', $sql);
        return sprintf($sql, ...array_values($bindingKeys));
    }

    /**
     * 修改数据格式 用于拼接 sql
     * @param array $row
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function changeRowType(array $row): array
    {
        $types = $this->getTableType();

        foreach ($row as $key => &$value) {
            $type = $types[$key];
            if (false !== stripos($type, 'int') || false !== stripos($type, 'float')) {
                $value = intval($value);
            } elseif (in_array($type, ['Date', 'String'])) {
                $value = "'{$value}'";
            }
        }

        return $row;
    }

    /**
     * 关联数组 过滤成 字段符合表设计
     * @param $arr
     * @return array|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getRowsFilterByColumns($arr)
    {
        $columns = $this->getTableColumns();
        $columns = array_combine($columns, $columns);
        if (Arr::isAssoc($arr)) {
            $arr = array_intersect_key($arr, $columns);
        } else {
            foreach ($arr as &$item) {
                $item = array_intersect_key($item, $columns);
            }
        }
        return $arr;
    }

    /**
     * 照抄 Builder.cleanBindings
     * @param $bindings
     * @return array
     */
    public function cleanBindings($bindings): array
    {
        return array_values(array_filter($bindings, function ($binding) {
            return !$binding instanceof Expression;
        }));
    }

    /**
     * 批量更新或新增：判断数据是否存在
     * @return array
     */
    public function unionKeys(): array
    {
        return [];
    }

    /**
     * 根据 prop 格式化 数据
     * @param $row
     * @param null $prop
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handleRowByProp($row, $prop = null)
    {
        if (empty($prop)) {
            $prop = $this->getTablePropByKey();
        }
        foreach ($row as $k => &$v) {
            $p = Arr::get($prop, "{$k}.type", '');
            if (false !== stripos($p, 'int')) {
                $v = intval($v);
            } elseif (false !== stripos($p, 'decimal')) {
                $v = floatval($v);
            } elseif (false !== stripos($p, 'string')) {
                $v = (string)($v);
            }
        }

        return $row;
    }

    public function clearAll() {
        return $this->getClient()->write("alter table {$this->getDbTable()} delete where 1=1");
    }

    public function count() {
        return intval($this->getClient()->write("select count(*) as _c from {$this->getDbTable()}")->rawData());
    }
}
