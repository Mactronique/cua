<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\ProjectProvider;

interface ProjectProviderInterface
{
    /**
     * return the list of project with configuration
     * @return array
     */
    public function getProjects();
}
