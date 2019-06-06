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

class PlatformNeededService
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

    public function getPlatformRequirement($projectPath)
    {
        $finder = new Finder();

        $platform = [];
        $composer = $finder->in($projectPath)->files()->name('composer.json');
        foreach ($composer as $file) {
            $json = json_decode($file->getContents(), true);

            foreach ($json['require'] as $name => $version) {
                if (!preg_match('/^(php|ext-[[:alnum:]]+)$/i', $name)) {
                    continue;
                }
                $platform[$name] = $version;
            }
        }

        return $platform;
    }
}
