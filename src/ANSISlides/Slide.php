<?php

namespace ANSISlides;

class Slide
{
    private $markdown;
    private $renderer;

    public function setMarkdown($markdown)
    {
        $this->markdown = $markdown;
    }

    public function getGeometry()
    {
        return [$this->width, $this->heigth];
    }

    public function getMarkdown()
    {
        return $this->markdown;
    }

    public function setRenderer(SlideRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function toString()
    {
        return $this->renderer->render($this);
    }
}
