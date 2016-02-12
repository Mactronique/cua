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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composerPath = $this->getApplication()->getComposerPath();

        if ($input->getOption('composer')) {
            $composerPath = $input->getOption('composer');
        }

        if (!file_exists($composerPath)) {
            throw new \Exception("Invalid composer path ".$composerPath, 1);
        }
        $projects = $this->getApplication()->getProjects();

        //Chargement du projet via la ligne de commande
        if ($this->getOption('project')) {
            if (null === $this->getArgument('name')) {
                throw new \Exception("Please set the name of project", 1);
            }
            $projects = [$this->getArgument('name')=>$this->getOption('project')];
        }

        foreach ($projects as $projectName => $projectPath) {
            $output->writeln(sprintf('Check <info>%s</info> at <comment>%s</comment>', $projectName, $projectPath));
            $process = new Process($composerPath.' update --dry-run --no-ansi');
            $process->setWorkingDirectory($projectPath);
            $process->mustRun();

            $sortie = $process->getErrorOutput();

            $resultProject = [
                'install'=>[],
                'uninstall'=>[],
                'update'=>[],
                'abandoned'=>[],
            ];

            if (preg_match('/Nothing to install or update/', $sortie)) {
                $output->writeln('Rien à mettre à jour');
            }

            if (preg_match_all('/- Installing ([a-zA-Z0-9\-_\.\/]*) \((.*)\)/', $sortie, $install)) {
                $r = $install[1];
                foreach ($r as $key => $lib) {
                    $resultProject['install'][]=['library'=>$lib, 'version'=>$install[2][$key]];
                }

            }

            if (preg_match_all('/- Uninstalling ([a-zA-Z0-9\-_\.\/]*) \((.*)\)/', $sortie, $uninstall)) {
                $r = $uninstall[1];
                foreach ($r as $key => $lib) {
                    $resultProject['uninstall'][]=['library'=>$lib, 'version'=>$uninstall[2][$key]];
                }
            }

            if (preg_match_all('/- Updating ([a-zA-Z0-9\-_\.\/]*) \((.*)\) to ([a-zA-Z0-9\-_\.\/]*) \((.*)\)/', $sortie, $update)) {
                $r = $update[1];
                foreach ($r as $key => $lib) {
                    $resultProject['update'][]=['from_library'=>$lib, 'from_version'=>$update[2][$key], 'to_library'=>$update[3][$key], 'to_version'=>$update[4][$key]];
                }
            }

            if (preg_match_all('/Package ([a-zA-Z0-9\-_\.\/]*) is abandoned/', $sortie, $abandoned)) {
                $resultProject['abandoned']= $abandoned[1];
            }
            $output->writeln(sprintf(
                'Result <info>%d</info> to install, <info>%d</info> to update, <info>%d</info> to remove, <error> %d </error> abandonned',
                count($resultProject['install']),
                count($resultProject['update']),
                count($resultProject['uninstall']),
                count($resultProject['abandoned'])
            ));

            $this->getApplication()->setProjectResult($projectName, $resultProject);
            $this->getApplication()->saveResult();

        }
        $output->writeln('Fin !');
    }
}
