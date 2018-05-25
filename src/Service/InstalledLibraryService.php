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
use Symfony\Component\Finder\Finder;

class InstalledLibraryService
{
    /**
     * Construct the service.
     *
     * @param string $composerPath
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = ($logger === null) ? new NullLogger() : $logger;
    }

    public function getInstalledLibrary($projectPath)
    {
        $finder = new Finder();

        $libraries = [];
        $composer = $finder->in($projectPath)->files()->name('composer.lock');
        foreach ($composer as $file) {
            $json = json_decode($file->getContents(), true);

            foreach ($json['packages'] as $value) {
                $libraries[$value['name']] = $value['version'];
            }
        }

        return $libraries;
    }
}
