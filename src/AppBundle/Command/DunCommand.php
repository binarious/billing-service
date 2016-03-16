<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dun:bills')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Bill Dunning');

        $billService = $this->getContainer()->get('bill_service');
        $due = $billService->getDue();

        $output->writeln('Found due bills: ' . count($due));

        foreach ($due as $bill) {
            $output->writeln('Dunning bill ' . $bill->getName() . '(#' . $bill->getId() . ')');
            $billService->dun($bill);
        }
    }
}