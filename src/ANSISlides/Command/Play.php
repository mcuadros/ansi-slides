<?php

namespace ANSISlides\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ANSISlides\Deck;
use ANSISlides\Deck\Transition;
use RuntimeException;

class Play extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('play')
            ->setDescription('Play a presentation')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'presentation to be played'
            )
            ->addOption(
               'no-transitions',
               null,
               InputOption::VALUE_NONE,
               'This will off the slide transitions'
            )->addOption(
               'position',
               null,
               InputOption::VALUE_REQUIRED,
               'Start position'
            )->addOption(
               'pagination',
               null,
               InputOption::VALUE_NONE,
               'Enable pagination'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = getcwd() . '/' . $input->getArgument('file');
        $markdown = $this->getMarkdown($file);
        $transition = $this->getTransition($input);

        $deck = new Deck(md5($file), $markdown);
        $deck->setPath(dirname($file));
        $deck->setTransition($transition);
        if ($input->getOption('position')) {
            $deck->setPosition((int) $input->getOption('position'));
        }

        $deck->showPagination((bool) $input->getOption('pagination'));

        $deck->build();
        $deck->play((int) exec('tput cols'), (int) exec('tput lines'));
    }

    private function getTransition(InputInterface $input)
    {
        if ($input->getOption('no-transitions')) {
            return new Transition\None();
        }

        return new Transition\Swap();
    }

    private function getMarkdown($file)
    {
        if (!file_exists($file)) {
            throw new RuntimeException(sprintf(
                'Unable to find file %s', $file
            ));
        }

        return file_get_contents($file);
    }
}
