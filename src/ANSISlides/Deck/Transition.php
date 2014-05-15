<?php

namespace ANSISlides\Deck;

use ANSISlides\Slide;

abstract class Transition
{
    abstract public function play(Slide $from = null, Slide $to, $cols, $lines);

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
