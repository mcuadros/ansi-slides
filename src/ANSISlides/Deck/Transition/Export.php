<?php

namespace ANSISlides\Deck\Transition;

use ANSISlides\Deck\Transition;
use ANSISlides\Slide;
use ANSISlides\Exporter;


class Export extends Transition
{
    private $exporter;

    public function __construct(Exporter $exporter )
    {
        $this->exporter = $exporter;
    }

    public function play(Slide $from = null, Slide $to, $cols, $lines)
    {
        $contentTo = $to->render($cols, $lines);
        $this->exporter->slideToHTML($contentTo->current());
    }
}
