<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\Persistence;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class DbalPersistance implements Persistence{
	/**
	 * @var string default plath of file
	 */
	private $config;

	private $connexion;

	/**
	 * @param array $config
	 */
	public function __construct(array $config){
		$this->config = $config;
	}

	/**
	 * @param array $content Content to save
	 * @param array $config custom config
	 */
	public function save(array $content, array $config = null)
	{
		$this->connexion = new \Doctrine\Dbal\Connexion($this->config);

		$connexion
	}


	private function checkExist($project, $library){
		$result = $this->connexion->execute('SELECT count(*) as nombre FROM '.$config['table_name']. ' WHERE project= ? AND library=?', [$project, $library]);
		$nb = $result->fetch();

		return $nb['nombre']!=0;
	}
}