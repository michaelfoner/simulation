<?php


namespace App\Command;

use App\Service\Generate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'simulation:generate';

    /**
     * Configure command
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Generate organism')
        ;

        $this
            ->addArgument('template', InputArgument::OPTIONAL, 'Time for loop')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templateType = $input->getArgument('template');
        $generateService = new Generate($templateType);
        $generateService->generate();
    }

}