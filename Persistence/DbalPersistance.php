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
        $this->connexion = new \Doctrine\Dbal\Connexion($this->config);

    }

    /**
     * Manage the installed library
     * @param string $projet
     * @param array  $installed library list
     */
    private function installedLib($project, array $installed)
    {
    }

    /**
     * Manage the library to install
     * @param string $projet
     * @param array  $install library list
     */
    private function installLib($project, array $install)
    {
    }

    /**
     * Manage the library to update
     * @param string $projet
     * @param array  $update library list
     */
    private function updateLib($project, array $update)
    {
    }

    /**
     * Manage the library to remove
     * @param string $projet
     * @param array  $remove library list
     */
    private function removeLib($project, array $remove)
    {
    }

    /**
     * Manage the abandoned library
     * @param string $projet
     * @param array  $abandonned library list
     */
    private function abandonedLib($project, array $abandonned)
    {
    }

    private function checkExist($project, $library)
    {
        $result = $this->connexion->execute('SELECT count(*) as nombre FROM '.$config['table_name']. ' WHERE project= ? AND library=?', [$project, $library]);
        $nb = $result->fetch();

        return $nb['nombre']!=0;
    }

    /**
     * @param array $data list of field (key) with value.
     */
    private function insert(array $data)
    {
    }

    /**
     * @param array $data list of field (key) with value.
     */
    private function update(array $data)
    {
    }
}
