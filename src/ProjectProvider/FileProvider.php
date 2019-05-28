<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\ProjectProvider;

use Mactronique\CUA\Configuration\ProjectConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class FileProvider implements ProjectProviderInterface
{
    private $config;

    private $projects;

    public function __construct($config)
    {
        $this->config = $config;
        if (!isset($config['path'])) {
            throw new \Exception("The path of file project is not set !", 1);
        }
    }

    public function getProjects()
    {
        if (null !== $this->projects && is_array($this->projects)) {
            return $this->projects;
        }
        $filePath = __DIR__.'/../'.$this->config['path'];
        if (!file_exists($filePath)) {
            throw new \Exception("Unable to load file : ".$filePath, 1);
        }
        $config = Yaml::parse(file_get_contents($filePath));

        $configs = [$config];
        $processor = new Processor();
        $configuration = new ProjectConfiguration();
        $this->projects = $processor->processConfiguration($configuration, $configs)['projects'];

        return $this->projects;
    }
}
