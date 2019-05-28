<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\CUA\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ProjectListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('project:list')
            ->setDescription('Get configured project list')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projects = $this->getApplication()->getProjects();

        //Pas de fichier de config donc fichier de sortie obligatoire
        if ($input->hasParameterOption(['--no-config'], true)) {
            throw new \Exception('Unable to get the project list without configuration', 1);
        }
        $outputStyle = new OutputFormatterStyle('red', 'yellow', array());
        $output->getFormatter()->setStyle('caution', $outputStyle);

        $table = new Table($output);
        $table->setHeaders(['Project Name', 'Location', 'Check Dependencies', 'Check Security', 'PHP Bin', 'composer.lock path']);

        foreach ($projects as $projectName => $projectConf) {
            $projectPath = $projectConf['path'];
            if (!is_dir($projectPath)) {
                $projectPath = '<error>'.$projectPath.'</error>';
                $lock_path = '<caution>'.$projectConf['lock_path'].'</caution>';
            } else {
                $lock_path = realpath($projectConf['path']).'/'.$projectConf['lock_path'];
                $lock_path = file_exists($lock_path) ? $projectConf['lock_path'] : '<error>'.$projectConf['lock_path'].'</error>';
            }
            
            $table->addRow([$projectName, $projectPath, $projectConf['check_dependencies'] ? 'true':'<error>false</error>', $projectConf['check_security'] ? 'true':'<error>false</error>', $projectConf['php_path'], $lock_path]);
        }

        $table->render();
    }
}
