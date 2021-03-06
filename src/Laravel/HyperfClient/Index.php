<?php
/**
 * 功能：客户端调用
 *
 * @date 2022/1/21
 * @author xu
 */

namespace Lib\Laravel\HyperfClient;

use Hyperf\Jet\ClientFactory;
use Hyperf\Jet\Packer\JsonEofPacker;
use Hyperf\Jet\PathGenerator\PathGenerator;
use Hyperf\Jet\ProtocolManager;
use Hyperf\Jet\ServiceManager;
use Hyperf\Jet\Transporter\StreamSocketTransporter;
use Lib\Laravel\Helper;
use Lib\Laravel\HyperfClient\DataFormatter;

/**
 * 优先读取 env 配置
 * HYPERF_MICRO_*_*: 单独配置
 * HYPERF_MICRO_*：全局配置
 * 当前文件配置（host可取hosts文件配置）
 */
class Index
{
    const PROTOCOL = 'jsonrpc';
    const CLIENT_AUC = 'AucService';
    const CLIENT_GAO_DE = 'GaoDeService';
    const CLIENT_MINI_WEIXIN = 'MiniWeixinService';
    const CLIENT_FILE = 'FileService';
    const CLIENT_HOUSE = 'HouseService';

    public static $services = [
        self::CLIENT_AUC => ['host' => 'php', 'port' => 9301],
        self::CLIENT_GAO_DE => ['host' => 'php', 'port' => 9301],
        self::CLIENT_MINI_WEIXIN => ['host' => 'php', 'port' => 9301],
        self::CLIENT_HOUSE => ['host' => 'php', 'port' => 9301],
        self::CLIENT_FILE => ['host' => 'php', 'port' => 9301]
    ];


    /**
     * @return \App\Service\Auc\Auc
     * @date 2022/1/22
     */
    public function auc()
    {
        return static::client(self::CLIENT_AUC);
    }

    public function house()
    {
        return static::client(self::CLIENT_HOUSE);
    }

    public function gaoDe()
    {
        return static::client(self::CLIENT_GAO_DE);
    }

    public function miniWeixin()
    {
        return static::client(self::CLIENT_MINI_WEIXIN);
    }

    public function file()
    {
        return static::client(self::CLIENT_FILE);
    }

    public function __construct()
    {
        $this->register();
    }

    public function register()
    {
        // 绑定 CalculatorService 与 jsonrpc 协议，同时设定静态的节点信息
        ProtocolManager::register(static::PROTOCOL, [
            ProtocolManager::TRANSPORTER => new StreamSocketTransporter(),
            ProtocolManager::PACKER => new JsonEofPacker(),
            ProtocolManager::PATH_GENERATOR => new PathGenerator(),
            ProtocolManager::DATA_FORMATTER => new DataFormatter(),
        ]);

        foreach (static::$services as $name => $service) {
            // 绑定 CalculatorService 与 jsonrpc 协议，同时设定静态的节点信息
            ServiceManager::register($name, static::PROTOCOL, [
                ServiceManager::NODES => [
                    $this->getConfig($name)
                ],
            ]);
        }
    }

    public function client($name)
    {
        $clientFactory = new ClientFactory();
        return $clientFactory->create($name, static::PROTOCOL);
    }

    /**
     * 优先读取 env 配置
     * HYPERF_MICRO_*_*: 单独配置
     * HYPERF_MICRO_*：全局配置
     * 当前文件配置（host可取hosts文件配置）
     */
    public function getConfig($name = '') {
        $service = [];
        if ($name) {
            $service = static::$services[$name];
        }
        return [
            env(Helper::func()->sprintf('HYPERF_MICRO_HOST_%s', $name))
                ?: env('HYPERF_MICRO_HOST')
                ?: $service['host'],
            env(Helper::func()->sprintf('HYPERF_MICRO_PORT_%s', $name))
                ?: env('HYPERF_MICRO_PORT')
                ?: $service['port']
        ];
    }

}
