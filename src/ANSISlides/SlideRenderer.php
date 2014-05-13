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

    protected function align($text, $pad = STR_PAD_BOTH)
    {
        $result = [];
        foreach (explode("\n", $text) as $line) {
            $total = $this->lenWithoutStyle($line);
            $toFill = ($this->cols - $total) / 2;

            $result[] = sprintf('%s%s%s',
                str_repeat(' ', ceil($toFill)),
                $line,
                str_repeat(' ', floor($toFill))
            );
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
                    //ogre
                    $result = Figlet::create($matches[2], 'contributed/thin');
                   // echo($result); exit();
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

            $block = substr($result, strpos($result, PHP_EOL) + 1);
            $lines = explode("\n" , $block);

            $max = 0;
            foreach ($lines as $line) {
                $len = $this->lenWithoutStyle($line);
                if ($len > $max) {
                    $max = $len;
                }
            }

            foreach ($lines as &$line) {
                $fill = $max - $this->lenWithoutStyle($line);
                $line .= str_repeat(' ', $fill);
            }

            return implode("\n", $lines);
        }, $markdown);
    }

    protected function lenWithoutStyle($line)
    {
        $clean = preg_replace('/\x1b(\[|\(|\))[;?0-9]*[0-9A-Za-z]/', '', $line);

        return strlen($clean);
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
