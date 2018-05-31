<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jean-baptiste.nahan@inextenso.fr>
 * @copyright 2016-2018 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace InExtenso\CUA\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SensioLabs\Security\SecurityChecker;

class SecurityCheckService
{
    private $securityCheckPath;

    private $logger;


    public function __construct($securityCheckPath, LoggerInterface $logger = null)
    {
        $this->securityCheckPath = $securityCheckPath;
        $this->logger = ($logger === null)? new NullLogger():$logger;
    }

    public function checkSecurity($projectPath, $lockPath, $php_path)
    {
        $resultProject = [
            'error' => '',
            'result' => [],
        ];

        $checker = new SecurityChecker();
        try {
            $datas = $checker->check($lockPath);

            $resultProject['result'] = $datas;
        } catch(\Exception $e) {
            $resultProject['error'] = $e->getMessage();
        }

        return $resultProject;
    }
}
