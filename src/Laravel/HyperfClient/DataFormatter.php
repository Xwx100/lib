<?php
/**
 * 功能：注入 client 的信息
 *
 * @date 2022/1/24
 * @author xu
 */

namespace Lib\Laravel\HyperfClient;

use Hyperf\Jet\DataFormatter\DataFormatter as JetDataFormatter;
use Lib\Common\JsonRpc;
use Lib\Laravel\Helper;

class DataFormatter extends JetDataFormatter
{
    use JsonRpc;

    public function formatRequest($data)
    {
        $req = parent::formatRequest($data);
        $req = array_merge($req, [
            $this->sendFlag() => Helper::zipkin()->headersSend()
        ]);

        return $req;
    }
}
