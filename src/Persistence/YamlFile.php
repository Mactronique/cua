<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\Persistence;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Yaml\Yaml;

class YamlFile implements Persistence
{
    /**
     * @var string default path of file
     */
    private $filePath;
    /**
     * @var string default path of file
     */
    private $fileSecurityPath;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->filePath = $config['path'];
        $this->fileSecurityPath = $config['path_security'];
    }

    /**
     * @param array $content Content to save
     * @param array $config custom config
     */
    public function save(array $content, array $config = null)
    {
        $content = Yaml::dump($content, 100);
        file_put_contents(($config !== null && isset($config['path']))? $config['path']:$this->filePath, $content);
    }


    public function saveSecurity(array $content, array $config = null)
    {
        $content = Yaml::dump($content, 100);
        file_put_contents(($config !== null && isset($config['path_security']))? $config['path_security']:$this->fileSecurityPath, $content);
    }
}
