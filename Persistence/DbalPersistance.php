<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\Persistence;

class DbalPersistance implements Persistence
{
    /**
     * @var string default plath of file
     */
    private $config;

    private $connexion;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $content Content to save
     * @param array $config  custom config
     */
    public function save(array $content, array $config = null)
    {
        $configObj = new \Doctrine\DBAL\Configuration();
        $this->connexion = \Doctrine\DBAL\DriverManager::getConnection($this->config, $configObj);

        foreach ($content as $key => $data) {
            $this->installedLib($key, $data['installed']);
            $this->installLib($key, $data['install']);
            $this->updateLib($key, $data['update']);
            $this->removeLib($key, $data['uninstall']);
            $this->abandonedLib($key, $data['abandoned']);
        }
    }

    /**
     * Manage the installed library.
     *
     * @param string $projet
     * @param array  $installed library list
     */
    private function installedLib($project, array $installed)
    {
        foreach ($installed as $library => $version) {
            $dbData = ['project' => $project, 'library' => $library, 'version' => $version, 'state' => 'installed', 'to_library' => null, 'to_version' => null];
            if ($this->checkExist($project, $library)) {
                $this->update($dbData);
                continue;
            }
            $this->insert($dbData);
        }
    }

    /**
     * Manage the library to install.
     *
     * @param string $projet
     * @param array  $install library list
     */
    private function installLib($project, array $install)
    {
        foreach ($install as $data) {
            $dbData = ['project' => $project, 'library' => $data['library'], 'version' => $data['version'], 'state' => 'install', 'to_library' => null, 'to_version' => null];
            if ($this->checkExist($project, $data['library'])) {
                $this->update($dbData);
                continue;
            }
            $this->insert($dbData);
        }
    }

    /**
     * Manage the library to update.
     *
     * @param string $projet
     * @param array  $update library list
     */
    private function updateLib($project, array $update)
    {
        foreach ($update as $data) {
            $dbData = ['project' => $project, 'library' => $data['from_library'], 'version' => $data['from_version'], 'state' => 'update', 'to_library' => $data['to_library'], 'to_version' => $data['to_version']];
            if ($this->checkExist($project, $data['from_library'])) {
                $this->update($dbData);
                continue;
            }
            $this->insert($dbData);
        }
    }

    /**
     * Manage the library to remove.
     *
     * @param string $projet
     * @param array  $remove library list
     */
    private function removeLib($project, array $remove)
    {
        foreach ($remove as $data) {
            $dbData = ['project' => $project, 'library' => $data['library'], 'version' => $data['version'], 'state' => 'remove', 'to_library' => null, 'to_version' => null];
            if ($this->checkExist($project, $data['library'])) {
                $this->update($dbData);
                continue;
            }
            $this->insert($dbData);
        }
    }

    /**
     * Manage the abandoned library.
     *
     * @param string $projet
     * @param array  $abandonned library list
     */
    private function abandonedLib($project, array $abandonned)
    {
    }

    private function checkExist($project, $library)
    {
        $result = $this->connexion->execute('SELECT count(*) as nombre FROM '.$this->config['table_name'].' WHERE project= ? AND library=?', [$project, $library]);
        $nb = $result->fetch();

        return $nb['nombre'] != 0;
    }

    /**
     * @param array $data list of field (key) with value.
     */
    private function insert(array $data)
    {
        $data['updated_at'] = new \DateTime();
        $this->connexion->insert($this->config['table_name'], $data, ['string', 'string', 'string', 'string', 'string', 'string', 'boolean', 'datetime']);
    }

    /**
     * @param array $data list of field (key) with value.
     */
    private function update(array $data)
    {
        $data['updated_at'] = new \DateTime();
        $key['project'] = $data['project'];
        $key['library'] = $data['library'];
        unset($data['project']);
        unset($data['library']);

        $this->connexion->insert($this->config['table_name'], $data, $key, ['string', 'string', 'string', 'string', 'datetime', 'string', 'string']);
    }
}
