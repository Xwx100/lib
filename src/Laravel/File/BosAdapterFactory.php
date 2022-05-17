<?php
/**
 * 功能：
 *
 * @date 2022/3/19
 * @author xu
 */

namespace Lib\Laravel\File;

use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;

class BosAdapterFactory
{
    public function make(array $options)
    {
        return new FilesystemAdapter(
            new Filesystem(new BosAdapter($options), $options)
        );
    }
}
