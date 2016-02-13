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
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Yaml\Yaml;


class DbalPersistance implements Persistence{
	/**
	 * @var string default plath of file
	 */
	private $config;

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
		
	}
}