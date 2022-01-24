<?php
/**
 * 功能：
 *
 * @date 2022/1/22
 * @author xu
 */

namespace Lib\Common;

trait ArrTree
{
    public function getTree($arr, $pid = 0, $pidKey = 'pid', $idKey = 'id', $childrenKey = 'children'): array
    {
        $result = [];
        foreach ($arr as $row) {
            if ($row[$pidKey] == $pid) {
                $row[$childrenKey] = $this->getTree($arr, $row[$idKey], $pidKey, $idKey, $childrenKey);
                $result[] = $row;
            }
        }
        return $result;
    }

    public function getTreeAutoChildren($arr, $pid = 0, $pidKey = 'pid', $idKey = 'id', $childrenKey = 'children'): array
    {
        $result = [];
        foreach ($arr as $row) {
            if ($row[$pidKey] == $pid) {
                $t = $this->getTreeAutoChildren($arr, $row[$idKey], $pidKey, $idKey, $childrenKey);
                if ($t) {
                    $row[$childrenKey] = $t;
                }
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * 获取树某级别
     * @param string $childrenKey
     * @return array
     */
    public function getTreeBottom($tree, $recordCb, $enterCb, $childrenKey = 'children', $record = null)
    {
        $result = [];
        foreach ($tree as $row) {
            if ($row[$childrenKey]) {
                $record1 = $recordCb($row, $record);
                $row[$childrenKey] = $this->getTreeBottom($row[$childrenKey], $recordCb, $enterCb, $childrenKey, $record1);
                if ($row[$childrenKey]) {
                    $result = array_merge($result, $row[$childrenKey]);
                }
            } else {
                try {
                    array_push($result, $enterCb($row, $record));
                } catch (\Exception $e) {
                }
            }
        }
        return $result;
    }

    /**
     * 获取树级别-原有格式
     * @param string $childrenKey
     * @return array
     */
    public function getTreeBottomOrigin($tree, $recordCb, $enterCb, $childrenKey = 'children', $record = null)
    {
        $result = [];
        foreach ($tree as $k => $row) {
            if ($row[$childrenKey]) {
                $record1 = $recordCb($row, $record);
                $row[$childrenKey] = $this->getTreeBottomOrigin($row[$childrenKey], $recordCb, $enterCb, $childrenKey, $record1);
                $result[$k] = $enterCb($row, $record);
            } else {
                try {
                    $result[$k] = $enterCb($row, $record);
                } catch (\Exception $e) {
                }
            }
        }
        return $result;
    }
}
