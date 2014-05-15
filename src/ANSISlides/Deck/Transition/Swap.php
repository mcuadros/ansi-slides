<?php

namespace ANSISlides\Deck\Transition;

use ANSISlides\Slide;
use Malenki\Ansi;

class Swap
{
    public function play(Slide $from = null, Slide $to, $cols, $lines)
    {
        if (!$from) {
            $contentTo = $to->render($cols, $lines);
            $this->printContent($contentTo->current());
            return;
        }

        $a = $from->render($cols, $lines)->current();
        $b = $to->render($cols, $lines)->current();

        $this->animate($a, $b, $lines);
    }

    protected function animate($a, $b, $lines)
    {
        $a = explode(PHP_EOL, $a);
        $b = explode(PHP_EOL, $b);

        for ($i = $lines; $i >= 0; $i--) {
            $content = array_merge(
                array_slice($a, $lines - $i, $lines),
                array_slice($b, 0, $lines - $i)
            );

            $this->printContent(implode(PHP_EOL, $content));
        }
    }

    protected function printContent($content)
    {
        $this->cleanScreen();
        echo $content;

        $this->sleep();
    }

    protected function cleanScreen()
    {
        print chr(27) . "[2J" . chr(27) . "[;H";
    }

    protected function sleep()
    {
        usleep(20000);
    }
}
