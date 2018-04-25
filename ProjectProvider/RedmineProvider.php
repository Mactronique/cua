<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jean-baptiste.nahan@inextenso.fr>
 * @copyright 2016-2018 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace InExtenso\CUA\ProjectProvider;

class RedmineProvider implements ProjectProviderInterface
{
    private $config;

    private $projects;

    public function __construct($config)
    {
        $this->config = $config;
        if (!isset($config['working_dir']) || !is_dir($config['working_dir'])) {
            throw new \Exception("working_dir configuration key is not set or not exists", 1);
        }

        if (!isset($config['db']) || !is_array($config['db'])) {
            throw new \Exception("db configuration key is not set or is not an array", 1);
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
        $result = $this->connexion->executeQuery('SELECT s.*, p.identifier as pid FROM `cua_settings` s LEFT JOIN `projects` p ON s.project_id = p.id');
        while ($ligne = $result->fetch(\PDO::FETCH_ASSOC)) {
            $conf = [
                'path' => $this->config['working_dir'].'/'.$ligne['pid'],
                'check_dependencies' => boolval($ligne['check_dependencies']),
                'check_security' => boolval($ligne['check_security']),
            ];
            if (!empty($conf['lock_path'])) {
                $conf['lock_path'] = $ligne['lock_path'];
            }
            if (!empty($conf['php_path'])) {
                $conf['php_path'] = $ligne['php_path'];
            }
            $config[$ligne['pid']] = $conf;
        }

        $configs = [$config];
        $processor = new Processor();
        $configuration = new ProjectConfiguration();
        $this->projects = $processor->processConfiguration($configuration, $configs)['projects'];

        return $this->projects;
    }
}
