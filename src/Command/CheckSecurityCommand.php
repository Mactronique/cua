<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */

namespace Mactronique\CUA\Command;

use Mactronique\CUA\Service\SecurityCheckService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckSecurityCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('security')
            ->setDescription('Run security check')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Nom du projet à vérifier'
            )
            ->addOption(
                'checker',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to security-checker'
            )
            ->addOption(
                'project',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to project'
            )
            ->addOption(
                'lock_path',
                null,
                InputOption::VALUE_REQUIRED,
                'The composer.lock path relative to project',
                './composer.lock'
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
        $securityChecker = $this->getApplication()->getSecurityChecker();

        if ($input->getOption('checker')) {
            $securityChecker = $input->getOption('checker');
        }

        if (!file_exists($securityChecker) && $securityChecker !== SecurityCheckService::INTERNAL) {
            throw new \Exception('Invalid security-checker path '.$securityChecker, 1);
        }
        $service = new \Mactronique\CUA\Service\SecurityCheckService($securityChecker);

        $projects = $this->getApplication()->getProjects();

        //Chargement du projet via la ligne de commande
        if ($input->getOption('project')) {
            if (null === $input->getArgument('name')) {
                throw new \Exception('Please set the name of project', 1);
            }
            $projects = [$input->getArgument('name') => ['path' => $input->getOption('project'), 'check_security'=> true, 'lock_path'=>$input->getOption('lock_path'), 'php_path'=>'php']];
        }

        //Pas de fichier de config donc fichier de sortie obligatoire
        if ($input->hasParameterOption(['--no-config'], true) && null === $input->getOption('output')) {
            throw new \Exception('Please set the output file option -o or --output', 1);
        }

        $outputFile = $input->getOption('output');
        $installedService = new \Mactronique\CUA\Service\InstalledLibraryService();

        foreach ($projects as $projectName => $projectConf) {
            $projectPath = $projectConf['path'];
            $lockPath = $projectConf['lock_path'];
            
            $output->writeln(sprintf('Check Security <info>%s</info> at <comment>%s</comment>', $projectName, $projectPath));

            if (!$projectConf['check_security']) {
                $output->writeln('<info>Skip</info>');
                continue;
            }
            
            if (isset($projectConf['private_dependencies']) && count($projectConf['private_dependencies'])) {
                $tmpPath = tempnam(sys_get_temp_dir(), 'cua');
                $lock = file_get_contents($projectPath.'/'.$lockPath);

                foreach ($projectConf['private_dependencies'] as $libraryName) {
                    $findName = sprintf('"%s"', $libraryName);
                    if (false === $pos = mb_strpos($lock, $findName)) {
                        $output->writeln('Private dependency not found in composer.lock : <info>'.$findName.'</info> ');
                        continue;
                    }
                    $result = mb_ereg_replace(''.$findName.'', sprintf('"%s"', md5($libraryName.uniqid())), $lock);
                    if ($result === false) {
                        $output->writeln('<error>Error in remplacement of private library</error>');
                        continue;
                    }

                    if (false !== $pos = mb_strpos($result, $findName)) {
                        $output->writeln('<error>Error : The new lock file contains always the replaced private library '.$findName.'</error>');
                    }

                    $lock = $result;
                }
                file_put_contents($tmpPath, $lock);
                $lockPath = $tmpPath;

                $output->writeln('Replace lock file by modified version : <info>'.$lockPath.'</info>');
            }

            $resultProject = $service->checkSecurity($projectPath, $lockPath, $projectConf['php_path']);
            if (isset($tmpPath)) {
                @unlink($tmpPath);
            }

            if ($resultProject['error'] != '') {
                $output->writeln(sprintf('<error> %s </error>', $resultProject['error']));
                continue;
            }

            $output->writeln(sprintf(
                'Result <error> %d </error> dependency with security issue',
                count($resultProject['result'])
            ));

            $this->getApplication()->setProjectSecurityResult($projectName, $resultProject['result']);
            $this->getApplication()->saveSecurityResult($outputFile);
        }
    }
}
