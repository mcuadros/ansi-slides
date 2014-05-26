<?php

namespace ANSISlides;

use ANSISlides\Deck\Control;
use ANSISlides\Deck\Transition;
use Malenki\Ansi;
use RuntimeException;

class Deck
{
    const SLIDE_DIVISOR = "---\n";

    private $id;
    private $control;
    private $markdown;
    private $slides = [];
    private $path = __DIR__;
    private $position = 0;
    private $previuos = -1;
    private $showPagination;

    public function __construct($id, $markdown)
    {
        $this->id = $id;
        $this->control = new Control();
        $this->markdown = $markdown;

        $this->position = $this->getPositionFromCache();
    }

    private function getPositionFromCache()
    {
        $cache = sys_get_temp_dir() . '/ansislides/' . $this->id;
        if (file_exists($cache)) {
            return (int) file_get_contents($cache);
        }

        return 0;
    }

    public function showPagination($bool)
    {
        $this->showPagination = $bool;
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
            $slideMarkdown = $this->prepareMarkdown($slideMarkdown);
            $i = 0;
            foreach ($slideMarkdown as $subSlideMarkdown) {
                $slide = $this->buildSlide($subSlideMarkdown);
                if ($i++ == 0) {
                    $slide->setTransition($this->transition);
                }

                $slide->setTotalSlides(count($slides) + 1);
                $slide->setNumberSlide(count($this->slides) + 1);

                $this->slides[] = $slide;
            }
        }
    }

    private function prepareMarkdown($markdown)
    {
        $slides = $this->analyzeList($markdown);

        return $slides;
    }

    protected function analyzeList($slideMarkdown)
    {
        $tokens = [];
        $markdown = preg_replace_callback('|((!\[.*\])?)(\- .*)|', function($matches) use (&$tokens) {
            $token = uniqid();
            $tokens[$token] = $matches[0];

            return $token;
        }, $slideMarkdown);

        if (!$tokens || $this->transition instanceOf Transition\Export) {
            return [$slideMarkdown];
        }

        $output = [];
        $keys = array_keys($tokens);
        $max = count($tokens);
        for ($i=-1;$i<$max-1;$i++) {
            $tmp = explode($keys[$i+1], $markdown);
            $newSlide = $tmp[0];

            foreach ($tokens as $token => $value) {
                $newSlide = str_replace($token, $value, $newSlide);
            }

            $clean = preg_replace('|(!\[.*\])|', '', $newSlide);
            if (strlen(trim($clean))) {
                $output[] = $newSlide;
            }
        }

        $output[] = $slideMarkdown;

        return $output;
    }

    private function buildSlide($markdown)
    {
        $slide = new Slide($markdown);
        $slide->setPath($this->path);
        $slide->showPagination($this->showPagination);

        return $slide;
    }

    public function play($cols, $lines)
    {
        if (!count($this->slides)) {
            throw new RuntimeException('call to Deck::build before play it');
        }

        do {
            $this->playCurrentSlide($cols, $lines);
        } while($this->wait());

        $this->cleanScreen();
    }

    public function playCurrentSlide($cols, $lines)
    {
        $prev = null;
        if (isset($this->slides[$this->previuos])) {
            $prev = $this->slides[$this->previuos];
        }

        if (!isset($this->slides[$this->position])) {
            throw new RuntimeException(sprintf(
                'Unable to find %d slide' , $this->position
            ));
        }

        $this->slides[$this->position]->play($cols, $lines, $prev);
        $this->savePositionToCache();
    }

    private function savePositionToCache()
    {
        $path = sys_get_temp_dir() . '/ansislides/';
        if (!file_exists($path)) {
            mkdir($path);
        }

        file_put_contents($path . $this->id, $this->position);
    }

    private function wait()
    {
        $action = $this->control->wait();
        switch ($action) {
            case Control::EVENT_NEXT:
                $this->next();
                break;
            case Control::EVENT_PREV:
                $this->prev();
                break;
            case Control::EVENT_QUIT:
                return false;
            default:
                return $this->wait();
        }

        if ($this->isEndReached()) {
            $this->position = count($this->slides) - 1;
        } else if ($this->position < 0) {
            $this->position = 0;
        }

        return true;
    }

    public function next()
    {
        $this->previuos = $this->position;
        $this->position++;
        $this->transition->setDirection(Transition::DIR_FORWARD);
    }

    public function prev()
    {
        $this->previuos = $this->position;
        $this->position--;
        $this->transition->setDirection(Transition::DIR_BACKWARD);
    }

    public function isEndReached()
    {
        return $this->getCurrentPosition() >= count($this->slides);
    }

    public function getCurrentPosition()
    {
        return $this->position + 1;
    }

    protected function cleanScreen()
    {
        print chr(27) . "[2J" . chr(27) . "[;H";
    }
}
