<?php

namespace ANSISlides;

use ANSISlides\Deck\Control;
use ANSISlides\Deck\Transition;
use Malenki\Ansi;
use RuntimeException;

class Deck
{
    const SLIDE_DIVISOR = "---\n";

    private $control;
    private $markdown;
    private $slides = [];
    private $path = __DIR__;
    private $position = 0;

    public function __construct($markdown)
    {
        $this->control = new Control();
        $this->markdown = $markdown;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setTransition(Transition $transition)
    {
        $this->transition = $transition;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function build()
    {
        $slides = explode(self::SLIDE_DIVISOR, $this->markdown);
        foreach($slides as $slideMarkdown) {
            $slide = new Slide($slideMarkdown);
            $slide->setPath($this->path);
            $slide->setTransition($this->transition);

            $this->slides[] = $slide;
        }
    }

    public function play($cols, $lines)
    {
        if (!count($this->slides)) {
            throw new RuntimeException('call to Deck::build before play it');
        }

        do {
            $this->doPlay($cols, $lines);
        } while($this->wait());

        $this->cleanScreen();
    }

    private function wait()
    {
        $max = count($this->slides);
        $action = $this->control->wait();
        switch ($action) {
            case Control::EVENT_NEXT:
                $this->position++;
                break;
            case Control::EVENT_PREV:
                $this->position--;
                break;
            case Control::EVENT_QUIT:
                return false;
        }

        if ($this->position + 1 >= $max) {
            $this->position = $max - 1;
        } else if ($this->position < 0) {
            $this->position = 0;
        }

        return true;
    }

    private function doPlay($cols, $lines)
    {
        $prev = null;
        if (isset($this->slides[$this->position - 1])) {
            $prev = $this->slides[$this->position - 1];
        }

        if (!isset($this->slides[$this->position])) {
            throw new RuntimeException(sprintf(
                'Unable to find %d slide' , $this->position
            ));
        }

        $this->slides[$this->position]->play($cols, $lines, $prev);
    }

    protected function cleanScreen()
    {
        print chr(27) . "[2J" . chr(27) . "[;H";
    }
}
