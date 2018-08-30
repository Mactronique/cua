<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jean-baptiste.nahan@inextenso.fr>
 * @copyright 2016-2018 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace InExtenso\CUA\ProjectProvider;

use Symfony\Component\Config\Definition\Processor;
use InExtenso\CUA\Configuration\ProjectConfiguration;

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
        $result = $connexion->executeQuery(sprintf("SELECT s.*, p.identifier as pid FROM `%s` s LEFT JOIN `projects` p ON s.project_id = p.id LEFT JOIN `repositories` r ON s.project_id = r.project_id WHERE p.status= '1' and r.is_default = '1'", $this->config['db']['table_name']));
        while ($ligne = $result->fetch(\PDO::FETCH_ASSOC)) {
            $conf = [
                'path' => $this->config['working_dir'].'/'.$ligne['pid'],
                'check_dependencies' => boolval($ligne['check_update']),
                'check_security' => boolval($ligne['check_security']),
            ];
            if (!empty($ligne['lock_path'])) {
                $conf['lock_path'] = $ligne['lock_path'];
            }
            if (!empty($ligne['php_bin'])) {
                $conf['php_path'] = $ligne['php_bin'];
            }
            $config[$ligne['pid']] = $conf;
        }

        $configs = [['projects' => $config]];
        $processor = new Processor();
        $configuration = new ProjectConfiguration();
        $this->projects = $processor->processConfiguration($configuration, $configs)['projects'];

        return $this->projects;
    }
}
