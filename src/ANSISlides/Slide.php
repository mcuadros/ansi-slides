<?php

namespace ANSISlides;

use Packaged\Figlet\Figlet;
use JakubOnderka\PhpConsoleColor\ConsoleColor;
use JakubOnderka\PhpConsoleHighlighter\Highlighter;
use Malenki\Ansi;

class Slide
{
    //From https://groups.google.com/forum/#!topic/iterm2-discuss/qACd1yHCXwo
    const ITERM2_BACKGROUND = 'osascript -e \'tell application "iTerm" to set background image path of current session of current terminal to "%s"\'';

    private $cols;
    private $lines;
    private $markdown;
    private $renderer;

    public function __construct($markdown)
    {
        $this->markdown = $markdown;
    }

    public function render($cols, $lines)
    {
        $this->cols = $cols;
        $this->lines = $lines;

        $markdown = $this->markdown;
        $markdown = $this->analyzeImage($markdown);
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
        foreach (explode(PHP_EOL, $text) as $line) {
            $total = $this->lenWithoutStyle($line);
            $toFill = ($this->cols - $total) / 2;

            $result[] = sprintf('%s%s%s',
                str_repeat(' ', ceil($toFill)),
                $line,
                str_repeat(' ', floor($toFill))
            );
        }

        return implode(PHP_EOL, $result);
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
            return $this->applyStyle($style, $markdown);
        }

        return $markdown;
    }

    protected function applyStyle(Ansi $style, $markdown)
    {
        $lines = explode(PHP_EOL, $markdown);
        foreach ($lines as &$line) {
            $line = (string) $style->value($line);
        }

        return implode(PHP_EOL, $lines);
    }

    protected function analyzeImage($markdown)
    {
        preg_match('|\!\[(.*)\]\((.*)\)|', $markdown, $matches);
        if (!$matches) {
            $this->clearBackground();

            return $markdown;
        }

        $this->setBackground($matches[2]);

        return '';
    }

    protected function setBackground($path)
    {
        $cmd = sprintf(self::ITERM2_BACKGROUND, __DIR__.$path);
        shell_exec($cmd);
    }

    protected function clearBackground()
    {
        $cmd = sprintf(self::ITERM2_BACKGROUND, '');
        shell_exec($cmd);
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

            $tmp = explode(PHP_EOL, $result);
            unset($tmp[count($tmp)]);
            unset($tmp[count($tmp)-1]);

            return implode(PHP_EOL, $tmp);
        }, $markdown);
    }

    protected function analyzeCodeLine($markdown)
    {
        return preg_replace_callback('|(```(.*)```)|s', function($matches) {
            $highlighter = new Highlighter(new ConsoleColor());
            $result = $highlighter->getWholeFileWithLineNumbers('<?php ' . PHP_EOL . $matches[2]);

            $block = substr($result, strpos($result, PHP_EOL) + 1);
            $lines = explode(PHP_EOL , $block);

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

            return implode(PHP_EOL, $lines);
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
        $total = count(explode(PHP_EOL, $text));
        $toFill = ($this->lines - $total) / 2;

        return sprintf('%s%s%s',
            str_repeat($line.PHP_EOL, ceil($toFill)),
            $text,
            str_repeat($line.PHP_EOL, floor($toFill))
        );
    }
}
