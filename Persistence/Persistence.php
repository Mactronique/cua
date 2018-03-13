<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jean-baptiste.nahan@inextenso.fr>
 * @copyright 2016-2018 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace InExtenso\CUA\Persistence;

interface Persistence
{
    /**
     * @param array $content Content to save
     * @param array $config  custom config
     */
    public function save(array $content, array $config = null);
}
