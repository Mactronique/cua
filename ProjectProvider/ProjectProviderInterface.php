<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jean-baptiste.nahan@inextenso.fr>
 * @copyright 2016-2018 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace InExtenso\CUA\ProjectProvider;

interface ProjectProviderInterface
{
    /**
     * return the list of project with configuration
     * @return array
     */
    public function getProjects();
}
