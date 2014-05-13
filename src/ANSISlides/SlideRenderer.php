<?php

namespace ANSISlides;

use Packaged\Figlet\Figlet;
use JakubOnderka\PhpConsoleColor\ConsoleColor;
use JakubOnderka\PhpConsoleHighlighter\Highlighter;
use Malenki\Ansi;

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
        $markdown = $this->analyzeHeaders($markdown);
        $markdown = $this->analyzeCodeLine($markdown);

        return $this->style(
            $this->align(
                $this->valign($markdown)
            )
        );
    }

    protected function align($text, $path = STR_PAD_RIGHT)
    {
        $result = [];
        foreach (explode("\n", $text) as $line) {
            if (strlen($line) > 0 && $line[0] != ' ') {
                $line = ' ' . $line;
            }

            $result[] = str_pad($line, $this->cols, '     ');
        }

        return implode("\n", $result);
    }

    protected function style($markdown)
    {
        $style = null;
        $markdown = preg_replace_callback('|(\[(.*),(.*)\])|',
            function($matches) use (&$style) {
                $style = new Ansi();
                $style->fg($matches[2])->bg($matches[3]);

                return str_repeat(' ', strlen($matches[0]));
            }
        , $markdown);

        if ($style) {
            return $style->value($markdown);
        }

        return $markdown;
    }

    protected function analyzeHeaders($markdown)
    {
        return preg_replace_callback('|(#+) (.*)|', function($matches) {
            switch ($matches[1]) {
                case '#':
                    $result = Figlet::create($matches[2], 'standard');
                    break;
                case '##':
                    $result = Figlet::create($matches[2], 'mini');
                    break;
                default:
                    $result = $matches[2];
                    break;
            }

            $tmp = explode("\n", $result);
            unset($tmp[count($tmp)]);
            unset($tmp[count($tmp)-1]);

            return implode("\n", $tmp);
        }, $markdown);
    }

    protected function analyzeCodeLine($markdown)
    {
        return preg_replace_callback('|(```(.*)```)|s', function($matches) {
            $highlighter = new Highlighter(new ConsoleColor());
            $result = $highlighter->getWholeFileWithLineNumbers('<?php ' . PHP_EOL . $matches[2]);

            return substr($result, strpos($result, PHP_EOL) + 1);
        }, $markdown);
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
