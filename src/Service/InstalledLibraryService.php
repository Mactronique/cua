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
            if (!isset($json['platform'])) {
                continue;
            }
            foreach ($json['platform'] as $platform => $version) {
                $libraries[$platform] = $version;
            }
        }

        return $libraries;
    }
}
