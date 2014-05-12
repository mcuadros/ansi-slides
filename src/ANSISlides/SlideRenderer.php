<?php

namespace ANSISlides;

use Packaged\Figlet\Figlet;

class SlideRenderer
{
    private $cols = 0;
    private $lines = 0;

    public function __construct($cols, $lines)
    {
        $this->cols = $cols;
        $this->lines = $lines;
    }

    public function render(Slide $slide)
    {
        $markdown = $slide->getMarkdown();

        return $this->valign($this->analyzeLines($markdown));
    }

    protected function analyzeLines($markdown)
    {
        $result = [];
        foreach (explode("\n", $markdown) as $line) {
            $result[] = $this->align($this->analyzeLine($line));
        }

        return implode("\n", $result);
    }

    protected function align($text, $path = STR_PAD_BOTH)
    {
        $result = [];
        foreach (explode("\n", $text) as $line) {
            $result[] = str_pad($line, $this->cols, ' ', $path);
        }

        return implode("\n", $result);
    }

    protected function analyzeLine($line)
    {
        preg_match('|(#+) (.*)|', $line, $matches);
        if (!$matches) {
            return $line;
        }

        switch ($matches[1]) {
            case '#':
                return Figlet::create($matches[2], 'standard');
            case '##':
                return Figlet::create($matches[2], 'mini');
            default:
                return $matches[2];
        }
    }


    protected function valign($text)
    {
        $line = str_repeat(' ', $this->cols);
        $total = count(explode("\n", $text));
        $toFill = ($this->lines - $total) / 2;

        return sprintf('%s%s%s',
            str_repeat($line."\n", ceil($toFill)),
            $text,
            str_repeat($line."\n", floor($toFill))
        );
    }
}
