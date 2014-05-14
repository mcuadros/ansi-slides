<?php

namespace ANSISlides\Deck\Transition;

use ANSISlides\Slide;
use Malenki\Ansi;

class FadeIn
{
    const CHAR_176 = '░';
    const CHAR_177 = '▒';
    const CHAR_178 = '▓';
    const CHAR_219 = '█';

    private $style;

    public function __construct(Ansi $style)
    {
        $this->style = $style;
    }

    public function play(Slide $from = null, Slide $to, $cols, $lines)
    {
        if (!$from) {
            $contentTo = $to->render($cols, $lines);
            $this->printContent($contentTo);
            return;
        }

        $this->fadeOut($from, $cols, $lines);
        $this->fadeIn($to, $cols, $lines);
    }

    protected function fadeOut(Slide $slide, $cols, $lines)
    {
        $contentFrom = $slide->render($cols, $lines);
        for ($i = 0; $i < $lines; $i++) {
            $content = $this->fillContent($contentFrom, $cols, self::CHAR_176, 0, $i);
            $this->printContent($content);
        }
    }

    protected function fadeIn(Slide $slide, $cols, $lines)
    {
        $contentTo = $slide->render($cols, $lines);
        for ($i = $lines; $i > 0; $i--) {
            $content = $this->fillContent($contentTo, $cols, self::CHAR_176, 0, $i);
            $this->printContent($content);
        }

        $this->printContent($contentTo);
    }

    protected function fillContent($content, $cols, $char, $from, $to)
    {
        $fill = (string) $this->style->value(str_repeat($char, $cols));
        $lines = explode(PHP_EOL, $content);

        for ($i = $from; $i < $to; $i++) {
            $lines[$i] = $fill;
        }

        return implode(PHP_EOL, $lines);
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
        usleep(10000);
    }
}
