<?php
/**
 * 功能：https://cloud.baidu.com/doc/BOS/s/2jwvys4em
 *
 * @date 2022/3/19
 * @author xu
 */

namespace Lib\Laravel\File;

require_once __DIR__ . '/bce-php-sdk-0.9.16/BaiduBce.phar';

use BaiduBce\BceClientConfigOptions;
use BaiduBce\Services\Bos\BosClient;
use Darabonba\GatewaySpi\Models\InterceptorContext\request;
use Illuminate\Support\Arr;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;


class BosAdapter implements AdapterInterface
{

    public $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

//    putObjectFromFile
    public function write($path, $contents, Config $config)
    {
        $path = $this->makePath($path);
        if ($contents instanceof \Illuminate\Http\UploadedFile) {
            $contents = $contents->getRealPath();
        }
        if (file_exists($contents)) {
            return $this->client()->putObjectFromFile($this->getBucketName(), $path, $contents);
        } else {
            return $this->client()->putObjectFromString($this->getBucketName(), $path, $contents);
        }
    }

    public function writeStream($path, $resource, Config $config)
    {
    }

    public function update($path, $contents, Config $config)
    {
        // TODO: Implement update() method.
    }

    public function updateStream($path, $resource, Config $config)
    {
        // TODO: Implement updateStream() method.
    }

    public function rename($path, $newpath)
    {
        // TODO: Implement rename() method.
    }

    public function copy($path, $newpath)
    {
        // TODO: Implement copy() method.
    }

    public function delete($path)
    {
        // TODO: Implement delete() method.
    }

    public function deleteDir($dirname)
    {
        // TODO: Implement deleteDir() method.
    }

    public function createDir($dirname, Config $config)
    {
        // TODO: Implement createDir() method.
    }

    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.
    }

    public function has($path)
    {
        // TODO: Implement has() method.
    }

    public function read($path)
    {
        // TODO: Implement read() method.
    }

    public function readStream($path)
    {
        // TODO: Implement readStream() method.
    }

    public function listContents($directory = '', $recursive = false)
    {
        // TODO: Implement listContents() method.
    }

    public function getMetadata($path)
    {
        // TODO: Implement getMetadata() method.
    }

    public function getSize($path)
    {
        // TODO: Implement getSize() method.
    }

    public function getMimetype($path)
    {
        // TODO: Implement getMimetype() method.
    }

    public function getTimestamp($path)
    {
        // TODO: Implement getTimestamp() method.
    }

    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method.
    }

    // 提供地址
    public function getUrl($path)
    {
        return "{$this->options['domain']}{$this->makePath($path)}";
    }

    // filename 不需要带前缀/
    public function makePath($fileName)
    {
        return $this->getDir() . "/{$fileName}";
    }

    public function getBucketName()
    {
        return $this->options['bucket_name'];
    }

    // dir 需要带前缀/
    public function getDir()
    {
        return $this->options['dir'];
    }

    protected function client() {
        return new BosClient(Arr::only($this->options, ['credentials', 'endpoint']));
    }
}
