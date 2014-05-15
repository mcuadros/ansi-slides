<?php

namespace ANSISlides\Deck\Transition;

use ANSISlides\Deck\Transition;
use ANSISlides\Slide;
use Malenki\Ansi;

class None extends Transition
{
    public function play(Slide $from = null, Slide $to, $cols, $lines)
    {
        $contentTo = $to->render($cols, $lines);
        $this->printContent($contentTo->current());
    }
}
