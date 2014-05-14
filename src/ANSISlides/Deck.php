<?php

namespace ANSISlides;

use ANSISlides\Deck\Control;

class Deck
{
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

    public function setRenderer(SlideRenderer $renderer)
    {
        $this->renderer = $renderer;
        foreach ($this->slides as $slide) {
            $slide->setRenderer($this->renderer);
        }
    }

    public function play()
    {
        $max = count($this->slides);
        $current = 0;
        while(1) {
            $this->cleanScreen();
            echo $this->slides[$current]->toString();

            $action = $this->control->wait();
            switch ($action) {
                case Control::EVENT_NEXT:
                    $current++;
                    break;
                case Control::EVENT_PREV:
                    $current--;
                    break;
            }

            if ($current + 1 >= $max) {
                $current = $max - 1;
            } else if ($current < 0) {
                $current = 0;
            }
        }

        $this->cleanScreen();
    }

    protected function cleanScreen()
    {
        print chr(27) . "[2J" . chr(27) . "[;H";
    }

    protected function buildSlides()
    {
        foreach(explode('---', $this->markdown) as $slideMarkdown) {
            $slide = new Slide($slideMarkdown);

            $this->slides[] = $slide;
        }
    }
}
