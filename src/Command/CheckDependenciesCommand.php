<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\CUA\Command;

use Mactronique\CUA\Service\CheckUpdateService;
use Mactronique\CUA\Service\InstalledLibraryService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckDependenciesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('check')
            ->setDescription('Run dependencies check')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'The project name to check'
            )
            ->addOption(
                'composer',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to composer'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to project'
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'The path to output file'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composerPath = $this->getApplication()->getComposerPath();

        if ($input->getOption('composer')) {
            $composerPath = $input->getOption('composer');
        }

        if (!file_exists($composerPath)) {
            throw new \Exception('Invalid composer path '.$composerPath, 1);
        }
        $service = new CheckUpdateService($composerPath);

        $projects = $this->getApplication()->getProjects();

        $customName = $input->getArgument('name');
        // Check one project found in configuration
        if (null !== $customName && !$input->getOption('project')) {
            if (!isset($projects[$customName])) {
                throw new \Exception(sprintf('The name of project %s is not configured', $customName), 1);
            }
            $output->writeln(sprintf('Check for one specific project <info>"%s"</info>', $customName));
            $projects = [$customName => $projects[$customName]];
        }

        //Load the project config from the commmand line
        if ($input->getOption('project')) {
            if (null === $customName) {
                throw new \Exception('Please set the name of project', 1);
            }
            $customPath = $input->getOption('project');
            $output->writeln(
                sprintf(
                    'Check for one custom project <info>"%s"</info> at <info>"%s"</info>',
                    $customName,
                    $customPath
                )
            );
            $projects = [$customName => ['path' => $customPath, 'check_dependencies' => true, 'php_path'=>'php']];
        }

        //Pas de fichier de config donc fichier de sortie obligatoire
        if ($input->hasParameterOption(['--no-config'], true) && null === $input->getOption('output')) {
            throw new \Exception('Please set the output file option -o or --output', 1);
        }

        $outputFile = $input->getOption('output');
        $installedService = new InstalledLibraryService();

        foreach ($projects as $projectName => $projectConf) {
            $projectPath = $projectConf['path'];
            $output->writeln(sprintf('Check <info>%s</info> at <comment>%s</comment>', $projectName, $projectPath));
            
            if (!$projectConf['check_dependencies']) {
                $output->writeln('<info>Skip</info>');
                continue;
            }

            $resultProject = $service->checkcomposerUpdate($projectPath, $projectConf['php_path']);

            if ($resultProject['error'] != '') {
                $output->writeln(sprintf('<error> %s </error>', $resultProject['error']));
            }

            $output->writeln(sprintf(
                'Result <info>%d</info> to install, <info>%d</info> to update, <info>%d</info> to remove, <error> %d </error> abandonned',
                count($resultProject['install']),
                count($resultProject['update']),
                count($resultProject['uninstall']),
                count($resultProject['abandoned'])
            ));
            $resultProject['installed'] = $installedService->getInstalledLibrary($projectPath);
            $this->getApplication()->setProjectResult($projectName, $resultProject);
            $this->getApplication()->saveResult($outputFile);
        }
    }
}
