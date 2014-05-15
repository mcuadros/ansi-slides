<?php

namespace ANSISlides;

use ANSISlides\Deck\Control;
use ANSISlides\Deck\Transition\Swap;
use Malenki\Ansi;

class Deck
{
    const SLIDE_DIVISOR = "---\n";

    private $control;
    private $markdown;
    private $renderer;
    private $slides = [];

    public function __construct($markdown)
    {
        $this->control = new Control();
        $this->markdown = $markdown;
        $this->buildSlides();
    }

    public function play($cols, $lines)
    {
        $style = new Ansi();
        $style->fg('yellow')->bg('black');
        $transition = new Swap($style);

        $max = count($this->slides);
        $position = 0;
        $prev = null;
        while(1) {
            $current = $this->slides[$position];
            $transition->play($prev, $current, $cols, $lines);
            $prev = $current;

            $action = $this->control->wait();
            switch ($action) {
                case Control::EVENT_NEXT:
                    $position++;
                    break;
                case Control::EVENT_PREV:
                    $position--;
                    break;
            }

            if ($position + 1 >= $max) {
                $position = $max - 1;
            } else if ($position < 0) {
                $position = 0;
            }
        }

        $this->cleanScreen();
    }

    protected function buildSlides()
    {
        foreach(explode(self::SLIDE_DIVISOR, $this->markdown) as $slideMarkdown) {
            $slide = new Slide($slideMarkdown);
            $this->slides[] = $slide;
        }
    }
}
