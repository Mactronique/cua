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
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

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
        $process = new Process($php_path.' '.$this->securityCheckPath.' security:check '.$lockPath.' --format=json ');
        $process->setWorkingDirectory($projectPath);
        $process->setTimeout(3000);
        try {
            $returnCode = $process->run();
        } catch (ProcessTimedOutException $e) {
            $this->logger->error('Process time out', ['projectPath'=>$projectPath, 'exception'=>$e]);
            $resultProject['error'] = 'Time out '.$e->getMessage();
            return $resultProject;
        }

        //var_dump($returnCode);
        $sortieErr = $process->getErrorOutput();
        if (strlen($sortieErr) > 0) {
            $resultProject['error'] = $sortieErr;
        }

        $sortie = $process->getOutput();
        $datas = json_decode($sortie, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Json decode error', ['projectPath'=>$projectPath, 'json_error'=>json_last_error_msg()]);
            $resultProject['error'] .= 'Json decode error '.json_last_error_msg();
            return $resultProject;
        }
        $resultProject['result'] = $datas;

        return $resultProject;
    }
}
