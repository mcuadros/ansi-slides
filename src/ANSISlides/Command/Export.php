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
               'p',
               InputOption::VALUE_NONE,
               'Enable pagination'
            )
            ->addOption(
               'resolution',
               'r',
               InputOption::VALUE_REQUIRED,
               'Terminal resouliton <cols>x<lines>',
               '85x30'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($cols, $lines) = explode('x', $input->getOption('resolution'));
        $cols = (int) $cols;
        $lines = (int) $lines;
        $output->writeln(sprintf(
            '<comment>Terminal size %dx%d</comment>',
            $cols, $lines
        ));

        $file = getcwd() . '/' . $input->getArgument('input');
        $markdown = $this->getMarkdown($file);

        $exporter = new Exporter();
        $transition = new Transition\Export($exporter);

        $deck = new Deck(md5($file), $markdown);
        $deck->setPath(dirname($file));
        $deck->setTransition($transition);
        $deck->setPosition(0);
        $deck->showPagination((bool) $input->getOption('pagination'));

        $deck->build();
        do {
            $deck->playCurrentSlide($cols, $lines);
            $output->writeln(sprintf(
                '<info>Saving slide</info> %d',
                $deck->getCurrentPosition()
            ));

            $deck->next();
        } while(!$deck->isEndReached());

        $destination = getcwd() . '/' . $input->getArgument('output');
        $exporter->save($destination);

        $output->writeln(sprintf(
            '<comment>Deck saved to "%s"</comment>',
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
