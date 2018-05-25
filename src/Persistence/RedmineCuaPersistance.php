<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jean-baptiste.nahan@inextenso.fr>
 * @copyright 2016-2018 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace InExtenso\CUA\Persistence;

class RedmineCuaPersistance implements Persistence
{
    /**
     * @var string default plath of file
     */
    private $config;

    private $connexion;

    private $updated;

    private $projectCodeCache;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->updated = [];
        $this->projectCodeCache = [];
    }

    /**
     * @param array $content Content to save
     * @param array $config  custom config
     */
    public function save(array $content, array $config = null)
    {
        $this->connexion = \Doctrine\DBAL\DriverManager::getConnection($this->config);
        $this->connexion->connect();

        foreach ($content as $key => $data) {
            if (in_array($key, $this->updated)) {
                continue;
            }
            $projectId = $this->convertProjectCode($key);
            $this->removeAll($projectId);
            $this->installedLib($projectId, $data['installed']);
            $this->installLib($projectId, $data['install']);
            $this->updateLib($projectId, $data['update']);
            $this->removeLib($projectId, $data['uninstall']);
            $this->abandonedLib($projectId, $data['abandoned']);
            $this->updated[] = $key;
        }
    }


    
    public function saveSecurity(array $content, array $config = null)
    {
        $this->connexion = \Doctrine\DBAL\DriverManager::getConnection($this->config);
        $this->connexion->connect();

        foreach ($content as $project => $securitydata) {
            $projectId = $this->convertProjectCode($project);
            $data = ['state' => 'fixed'];
            $data['updated_at'] = new \DateTime();

            $this->connexion->update($this->config['table_name_security'], $data, ['project_id' => $projectId, 'state' => 'open'], ['string', 'datetime', 'integer', 'string']);
            if (count($securitydata) === 0) {
                continue;
            }

            foreach ($securitydata as $library => $infos) {
                $list = json_encode($infos['advisories']);
                if ($this->checkSecurityExist($projectId, $library, $infos['version'])) {
                    $this->connexion->update(
                        $this->config['table_name_security'],
                        [
                            'details' => $list,
                            'state' => 'open',
                            'updated_at' => new \DateTime(),
                        ],
                        ['project_id' => $projectId, 'library'=>$library, 'version'=>$version],
                        ['string', 'string', 'datetime', 'integer', 'string', 'string']
                    );
                } else {
                    $this->connexion->insert(
                        $this->config['table_name_security'],
                        [
                            'details' => $list,
                            'state' => 'open',
                            'updated_at' => new \DateTime(),
                            'project_id' => $projectId,
                            'library'=>$library,
                            'version'=>$version
                        ],
                        ['string', 'string', 'datetime', 'integer', 'string', 'string']
                    );
                }
            }
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
            $dbData = ['project_id' => $project, 'library' => $library, 'version' => $version, 'state' => 'installed', 'to_library' => null, 'to_version' => null];
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
            $dbData = ['project_id' => $project, 'library' => $data['library'], 'version' => $data['version'], 'state' => 'install', 'to_library' => null, 'to_version' => null];
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
            $dbData = ['project_id' => $project, 'library' => $data['from_library'], 'version' => $data['from_version'], 'state' => 'update', 'to_library' => $data['to_library'], 'to_version' => $data['to_version']];
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
            $dbData = ['project_id' => $project, 'library' => $data['library'], 'version' => $data['version'], 'state' => 'remove', 'to_library' => null, 'to_version' => null];
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
        foreach ($abandonned as $key => $library) {
            $this->connexion->update($this->config['table_name'], ['deprecated' => true], ['project_id' => $project, 'library' => $library], ['boolean', 'integer', 'string']);
        }
    }

    private function checkExist($project, $library)
    {
        $result = $this->connexion->executeQuery('SELECT count(*) as nombre FROM '.$this->config['table_name'].' WHERE project_id= ? AND library=?', [$project, $library], ['integer', 'string']);
        $nb = $result->fetch(\PDO::FECTH_ASSOC);

        return intval($nb['nombre']) != 0;
    }

    private function checkSecurityExist($project, $library, $version)
    {
        $result = $this->connexion->executeQuery('SELECT count(*) as nombre FROM '.$this->config['table_name_security'].' WHERE project_id= ? AND library=? AND version=?', [$project, $library, $version], ['integer', 'string', 'string']);
        $nb = $result->fetch(\PDO::FECTH_ASSOC);

        return intval($nb['nombre']) != 0;
    }

    /**
     * @param array $data list of field (key) with value.
     */
    private function insert(array $data)
    {
        $data['deprecated'] = false;
        $data['updated_at'] = new \DateTime();

        $this->connexion->insert($this->config['table_name'], $data, ['integer', 'string', 'string', 'string', 'string', 'string', 'boolean', 'datetime']);
    }

    /**
     * @param array $data list of field (key) with value.
     */
    private function update(array $data)
    {
        $data['updated_at'] = new \DateTime();
        $key['project_id'] = $data['project_id'];
        $key['library'] = $data['library'];
        unset($data['project_id']);
        unset($data['library']);
        if (array_key_exists('deprecated', $data)) {
            unset($data['deprecated']);
        }

        $this->connexion->update($this->config['table_name'], $data, $key, ['string', 'string', 'string', 'string', 'datetime', 'string', 'integer']);
    }

    /**
     * Mark all dependency to deleted.
     *
     * @param string $key
     */
    private function removeAll($project)
    {
        $data = ['state' => 'removed', 'to_library' => null, 'to_version' => null];
        $data['updated_at'] = new \DateTime();

        $this->connexion->update($this->config['table_name'], $data, ['project_id' => $project], ['string', 'string', 'string', 'datetime', 'integer']);
    }

    /**
     * return the index for the project code.
     */
    private function convertProjectCode($projectCode)
    {
        if (array_key_exists($projectCode, $this->projectCodeCache)) {
            return $this->projectCodeCache[$projectCode];
        }

        $result = $this->connexion->executeQuery('SELECT id FROM projects WHERE identifier = ?', [$projectCode], ['string']);
        $rows = $result->fetchAll();
        if (count($rows) == 0) {
            $this->projectCodeCache[$projectCode] = null;

            return;
        }
        $this->projectCodeCache[$projectCode] = intval($rows[0]['id']);

        return $this->projectCodeCache[$projectCode];
    }
}
