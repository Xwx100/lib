<?php
/**
 * 功能：
 *
 * @date 2022/2/10
 * @author xu
 */

namespace Lib\Hyperf\Zipkin;

use Hyperf\Tracer\SpanStarter;
use Lib\Common\JsonRpc;
use Lib\Hyperf\Helper;
use OpenTracing\Tracer;

class Index
{
    use SpanStarter;
    use JsonRpc;

    /**
     * @var Tracer
     */
    private $tracer;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * @param string $name
     * @param string $f
     * @param $context
     * @param $users
     * @param $roles
     * @param $robots
     * @return \OpenTracing\Span
     * @date 2022/2/10
     */
    public function log(string $name, string $f, $context = [], $users = [], $roles = [], $robots = [])
    {
        $value = Helper::func()->sprintf($f, $context);
        $child = $this->startSpan($name);
        $child->setTag('msg', $value);
        if ($users) {
            $child->setTag('notify.users', Helper::func()->sprintf('%s', $users));
        }
        if ($roles) {
            $child->setTag('notify.roles', Helper::func()->sprintf('%s', $roles));
        }
        if ($robots) {
            $child->setTag('notify.robots', Helper::func()->sprintf('%s', $robots));
        }
        $child->finish(Helper::func()->microSeconds());
        return $child;
    }
}