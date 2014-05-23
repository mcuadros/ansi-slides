<?php

namespace ANSISlides\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ANSISlides\Deck;
use ANSISlides\Exporter;
use ANSISlides\Deck\Transition;
use RuntimeException;

class Export extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('export')
            ->setDescription('Export a presentation')
            ->addArgument(
                'input',
                InputArgument::REQUIRED,
                'presentation to be exported'
            )
            ->addArgument(
                'output',
                InputArgument::REQUIRED,
                'destination file'
            )
            ->addOption(
               'pagination',
               null,
               InputOption::VALUE_NONE,
               'Enable pagination'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = getcwd() . '/' . $input->getArgument('input');
        $markdown = $this->getMarkdown($file);

        $exporter = new Exporter();
        $transition = new Transition\Export($exporter);

        $deck = new Deck($markdown);
        $deck->setPath(dirname($file));
        $deck->setTransition($transition);
        $deck->showPagination((bool) $input->getOption('pagination'));

        $deck->build();
        do {
            $deck->playCurrentSlide((int) exec('tput cols'), (int) exec('tput lines'));
            $output->writeln(sprintf(
                '<info>Saving slide</info> %d',
                $deck->getCurrentPosition()
            ));

            $deck->next();
        } while(!$deck->isEndReached());

        $destination = getcwd() . '/' . $input->getArgument('output');
        $exporter->save($destination);

        $output->writeln(sprintf(
            '<info>Deck saved to</info> "%s"',
            $destination
        ));
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
