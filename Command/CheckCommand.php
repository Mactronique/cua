<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jbnahan@gmail.com>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class CheckCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('check')
            ->setDescription('Execute les tests')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Nom du projet à vérifier'
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
        $service = new \Mactronique\CUA\Service\CheckUpdateService($composerPath);
        
        $projects = $this->getApplication()->getProjects();

        //Chargement du projet via la ligne de commande
        if ($input->getOption('project')) {
            if (null === $input->getArgument('name')) {
                throw new \Exception('Please set the name of project', 1);
            }
            $projects = [$input->getArgument('name') => $input->getOption('project')];
        }

        //Pas de fichier de config donc fichier de sortie obligatoire
        if ($input->hasParameterOption(['--no-config'], true) && null === $input->getOption('output')) {
            throw new \Exception("Please set the output file option -o or --output", 1);
        }

        $outputFile = $input->getOption('output');
        $installedService = new \Mactronique\CUA\Service\InstalledLibraryService();

        foreach ($projects as $projectName => $projectPath) {
            
            $output->writeln(sprintf('Check <info>%s</info> at <comment>%s</comment>', $projectName, $projectPath));
            $resultProject = $service->checkcomposerUpdate($projectPath);

            if($resultProject['error']!= ''){
                $output->writeln(sprintf("<error> %s </error>", $resultProject['error']));
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
