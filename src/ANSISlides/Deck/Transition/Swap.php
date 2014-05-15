<?php

namespace ANSISlides\Deck\Transition;

use ANSISlides\Deck\Transition;
use ANSISlides\Slide;
use Malenki\Ansi;

class Swap extends Transition
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

        if ($this->direction == self::DIR_BACKWARD) {
            for ($i = 0; $i <= $lines; $i++) {
                $this->printMix($b, $a, $i, $lines);
            }
        } else if ($this->direction == self::DIR_FORWARD) {
            for ($i = $lines; $i >= 0; $i--) {
                $this->printMix($a, $b, $i, $lines);
            }
        }
    }

    protected function printMix($a, $b, $i, $lines)
    {
        $content = array_merge(
            array_slice($a, $lines - $i, $lines),
            array_slice($b, 0, $lines - $i)
        );

        $this->printContent(implode(PHP_EOL, $content));
    }
}
