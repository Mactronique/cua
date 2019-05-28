<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\ProjectProvider;

use Symfony\Component\Config\Definition\Processor;
use Mactronique\CUA\Configuration\ProjectConfiguration;

class DbalProvider implements ProjectProviderInterface
{
    private $config;

    private $projects;

    public function __construct($config)
    {
        if (null !== $config || !is_array($config)) {
            throw new \Exception("The configuration is not set or not an array", 1);
        }

        $this->config = $config;

        if (!isset($config['working_dir']) || !is_dir($config['working_dir'])) {
            throw new \Exception("working_dir configuration key is not set or not exists", 1);
        }

        if (!isset($config['db']) || !is_array($config['db'])) {
            throw new \Exception("db configuration key is not set or is not an array", 1);
        }

        if (!isset($config['table_name']) || !is_string($config['table_name'])) {
            throw new \Exception("table_name configuration key is not set or is not a string", 1);
        }
    }

    public function getProjects()
    {
        if (null !== $this->projects && is_array($this->projects)) {
            return $this->projects;
        }

        $connexion = \Doctrine\DBAL\DriverManager::getConnection($this->config['db']);
        $connexion->connect();

        $config = [];
        $result = $connexion->executeQuery(sprintf("SELECT code, name, path, check_dependencies, check_security, lock_path, php_path, private_dependencies FROM %s WHERE check_dependencies = '1' or check_security = '1'", $this->config['db']['table_name']));
        while ($ligne = $result->fetch(\PDO::FETCH_ASSOC)) {
            $conf = [
                'path' => $this->config['working_dir'].DIRECTORY_SEPARATOR.$ligne['path'],
                'check_dependencies' => boolval($ligne['check_dependencies']),
                'check_security' => boolval($ligne['check_security']),
            ];

            if (!empty($ligne['private_dependencies'])) {
                $conf['private_dependencies'] = json_decode($ligne['private_dependencies']);
                // JSON decode silent error
                if ($conf['private_dependencies'] === false) {
                    $conf['private_dependencies'] = null;
                }
            }
            if (!empty($ligne['lock_path'])) {
                $conf['lock_path'] = $ligne['lock_path'];
            }
            if (!empty($ligne['php_path'])) {
                $conf['php_path'] = $ligne['php_path'];
            }
            $config[$ligne['code']] = $conf;
        }

        $configs = [['projects' => $config]];
        $processor = new Processor();
        $configuration = new ProjectConfiguration();
        $this->projects = $processor->processConfiguration($configuration, $configs)['projects'];

        return $this->projects;
    }
}
