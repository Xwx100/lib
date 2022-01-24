<?php
// +----------------------------------------------------------------------
// | xu: 2021/8/16 0016
// +----------------------------------------------------------------------

namespace Lib\Common;


/**
 * 通用型数组转对象
 * Class ArrayObject
 */
trait ArrayObject
{
//    protected $origin = [];

    public function injectOrigin(array $origin = [])
    {
        $this->origin = $origin;
        return $this;
    }

    public function __get($name)
    {
        return $this->origin[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->origin[$name] = $value;
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->origin[$offset]);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->origin[$offset]);
    }


    public function __toString(): string
    {
        return json_encode($this->origin, JSON_UNESCAPED_UNICODE);
    }

    public function toJson($options = 0)
    {
        return $this->__toString();
    }

    public function toArray(): array
    {
        return $this->origin;
    }

    public function snake($value, $delimiter = '_')
    {
        static $cache = null;
        if (empty($cache[$value]) && !ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $cache[$value] = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }
        return $cache[$value];
    }


    /**
     * setLogin => $this->>origin['login']
     * @param $name
     * @param $arguments
     * @return static
     * @throws \Exception
     */
    public function __call($name, $arguments): self
    {
        $offset = strpos($name, 'set');
        if ($offset === 0) {
            $key = $this->snake(substr($name, $offset + 3));
            $this->__set($key, $arguments[0] ?? null);
            return $this;
        }
        throw new \Exception('[]');
    }
}
