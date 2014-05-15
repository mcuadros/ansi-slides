<?php

namespace ANSISlides\Deck\Transition;

use ANSISlides\Deck\Transition;
use ANSISlides\Slide;
use Malenki\Ansi;

class FadeIn extends Transition
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
            $this->printContent($contentTo->current());
            return;
        }

        $this->fadeOut($from, $cols, $lines);
        $this->fadeIn($to, $cols, $lines);
    }

    protected function fadeOut(Slide $slide, $cols, $lines)
    {
        $contentFrom = $slide->render($cols, $lines);
        for ($i = 0; $i < $lines; $i++) {
            $content = $this->fillContent($contentFrom->current(), $cols, $lines, $i);
            $this->printContent($content);
        }
    }

    protected function fadeIn(Slide $slide, $cols, $lines)
    {
        $contentTo = $slide->render($cols, $lines);
        for ($i = $lines; $i > 0; $i--) {
            $content = $this->fillContent($contentTo->current(), $cols, $lines, $i);
            $this->printContent($content);
        }

        foreach ($contentTo as $current) {
            $this->printContent($current);
        }
    }

    protected function fillContent($content, $cols, $lines, $to)
    {
        $output = explode(PHP_EOL, $content);
        for ($i = 0; $i < $to; $i++) {
            $char = $this->calcChar($i, $to, $lines);
            $output[$i] = (string) $this->style->value(str_repeat($char, $cols));
        }

        return implode(PHP_EOL, $output);
    }

    protected function calcChar($line, $to, $lines)
    {
        $hidden = $lines - $to;

        $steps = intval($lines / 3);
        if ($line + $hidden > $steps*2) return self::CHAR_178;
        if ($line + $hidden > $steps*1) return self::CHAR_177;
        return self::CHAR_176;
    }
}
