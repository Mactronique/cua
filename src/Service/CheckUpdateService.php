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
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class CheckUpdateService
{

    /**
     * @var LoggerInterface|NullLogger
     */
    private $logger;

    /**
     * @var string
     */
    private $composerPath;

    /**
     * Construct the service
     * @param string $composerPath
     */
    public function __construct($composerPath, LoggerInterface $logger = null)
    {
        $this->composerPath = $composerPath;
        $this->logger = ($logger === null)? new NullLogger():$logger;
    }

    public function checkComposerUpdate($projectPath, $php_path)
    {
        $resultProject = [
            'install' => [],
            'uninstall' => [],
            'update' => [],
            'abandoned' => [],
            'error' => '',
        ];

        $process = new Process($php_path.' '.$this->composerPath.' update --dry-run --no-ansi');
        $process->setWorkingDirectory($projectPath);
        $process->setTimeout(300);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $this->logger->error('Process Fail', ['projectPath'=>$projectPath, 'exception'=>$e]);
            $resultProject['error'] = $process->getErrorOutput();
            return $resultProject;
        } catch (ProcessTimedOutException $e) {
            $this->logger->error('Process time out', ['projectPath'=>$projectPath, 'exception'=>$e]);
            $resultProject['error'] = 'Time out '.$e->getMessage();
            return $resultProject;
        }

        $sortie = $process->getErrorOutput();

        if (preg_match('/Nothing to install or update/', $sortie)) {
            return $resultProject;
        }

        if (preg_match_all('/- Installing ([a-zA-Z0-9\-_\.\/]*) \((.*)\)/', $sortie, $install)) {
            $r = $install[1];
            foreach ($r as $key => $lib) {
                $resultProject['install'][] = ['library' => $lib, 'version' => $install[2][$key]];
            }
        }

        if (preg_match_all('/- Uninstalling ([a-zA-Z0-9\-_\.\/]*) \((.*)\)/', $sortie, $uninstall)) {
            $r = $uninstall[1];
            foreach ($r as $key => $lib) {
                $resultProject['uninstall'][] = ['library' => $lib, 'version' => $uninstall[2][$key]];
            }
        }

        if (preg_match_all('/- Updating ([a-zA-Z0-9\-_\.\/]*) \((.*)\) to ([a-zA-Z0-9\-_\.\/]*) \((.*)\)/', $sortie, $update)) {
            $r = $update[1];
            foreach ($r as $key => $lib) {
                $resultProject['update'][] = ['from_library' => $lib, 'from_version' => $update[2][$key], 'to_library' => $update[3][$key], 'to_version' => $update[4][$key]];
            }
        }

        if (preg_match_all('/Package ([a-zA-Z0-9\-_\.\/]*) is abandoned/', $sortie, $abandoned)) {
            $resultProject['abandoned'] = $abandoned[1];
        }
        return $resultProject;
    }
}
