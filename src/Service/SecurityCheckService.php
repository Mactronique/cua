<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SensioLabs\Security\SecurityChecker;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class SecurityCheckService
{
    const INTERNAL = 'internal';

    /** @var string */
    private $securityCheckPath;

    /** @var LoggerInterface */
    private $logger;

    /**
     * SecurityCheckService constructor.
     * @param string $securityCheckPath
     * @param LoggerInterface|null $logger
     */
    public function __construct($securityCheckPath, LoggerInterface $logger = null)
    {
        $this->securityCheckPath = $securityCheckPath;
        $this->logger = ($logger === null)? new NullLogger():$logger;
    }

    /**
     * @param string $projectPath
     * @param string $lockPath
     * @param string $phpPath
     * @return array
     */
    public function checkSecurity($projectPath, $lockPath, $phpPath)
    {
        if (strtolower($this->securityCheckPath) === self::INTERNAL){
            return $this->checkSecurityInt($projectPath, $lockPath);
        }
        return $this->checkSecurityExt($projectPath, $lockPath, $phpPath);
    }

    /**
     * Check security with the integrated checker
     * @param string $projectPath
     * @param string $lockPath
     * @return array
     */
    private function checkSecurityInt($projectPath, $lockPath)
    {
        $resultProject = [
            'error' => '',
            'result' => [],
        ];

        try {
            $result = (string) (new SecurityChecker())->check($lockPath);

            return $this->processJson($result, $resultProject, $projectPath);
        } catch(\Exception $e) {
            $resultProject['error'] = $e->getMessage();
        }

        return $resultProject;
    }

    /**
     * Use external checker
     * @param string $projectPath
     * @param string $lockPath
     * @param string $phpPath
     * @return array
     */
    private function checkSecurityExt($projectPath, $lockPath, $phpPath)
    {
        $resultProject = [
            'error' => '',
            'result' => [],
        ];
        $process = new Process($phpPath.' '.$this->securityCheckPath.' security:check '.$lockPath.' --format=json ');
        $process->setWorkingDirectory($projectPath);
        $process->setTimeout(3000);
        try {
            $returnCode = $process->run();
            $sortieErr = $process->getErrorOutput();
            if (strlen($sortieErr) > 0) {
                $resultProject['error'] = 'Result code' . $returnCode. "\n" . $sortieErr;
            }

            return $this->processJson($process->getOutput(), $resultProject, $projectPath);
        } catch (ProcessTimedOutException $e) {
            $this->logger->error('Process time out', ['projectPath'=>$projectPath, 'exception'=>$e]);
            $resultProject['error'] = 'Time out '.$e->getMessage();
            return $resultProject;
        }
    }

    /**
     * @param string $jsonString
     * @param array  $resultProject
     * @param string $projectPath
     * @return mixed
     */
    private function processJson($jsonString, $resultProject, $projectPath)
    {
        $datas = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Json decode error', ['projectPath'=>$projectPath, 'json_error'=>json_last_error_msg()]);
            $resultProject['error'] .= 'Json decode error '.json_last_error_msg();
            return $resultProject;
        }
        $resultProject['result'] = $datas;
        return $resultProject;
    }
}
