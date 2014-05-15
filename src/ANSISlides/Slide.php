<?php

namespace ANSISlides;

use Packaged\Figlet\Figlet;
use Malenki\Ansi;

class Slide
{
    //From https://groups.google.com/forum/#!topic/iterm2-discuss/qACd1yHCXwo
    const ITERM2_BACKGROUND = 'osascript -e \'tell application "iTerm" to set background image path of current session of current terminal to "%s"\'';

    private $cols;
    private $lines;
    private $markdown;
    private $renderer;
    private $style;

    public function __construct(
        $markdown,
        $foreground = 'black',
        $background = 'yellow'
    ) {
        $this->markdown = $markdown;
        $this->foreground = $foreground;
        $this->background = $background;

        $this->style = new Ansi();
        $this->style
            ->fg($this->foreground)
            ->bg($this->background);
    }

    public function render($cols, $lines)
    {
        $this->cols = $cols;
        $this->lines = $lines;

        $markdown = $this->markdown;
        $markdown = $this->analyzeImage($markdown);
        $markdown = $this->analyzeHeaders($markdown);
        $markdown = $this->analyzeCodeLine($markdown);
        //$markdown = $this->analyzeList($markdown);

        return $this->format($markdown);
    }

    protected function format($markdown)
    {
        foreach(explode(Deck::SLIDE_DIVISOR, $markdown) as $md) {
            $md = $this->valign($md);
            $md = $this->align($md);
            $md = $this->style($md);

            yield $md;
        }
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
                $this->style->value(str_repeat(' ', floor($toFill)))
            );
        }

        return implode(PHP_EOL, $result);
    }

    protected function style($markdown)
    {
        return $this->applyStyle($markdown);
    }

    protected function applyStyle($markdown)
    {
        $lines = explode(PHP_EOL, $markdown);
        foreach ($lines as &$line) {
            $line = (string) $this->style->value($line);
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

        $this->style = new Ansi();
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
                    $result = Figlet::create($matches[2], '../../../../../fonts/ansi');                    break;
                case '##':
                    $result = $this->drawSecondaryHeader($matches[2]);
                    break;
                default:
                    $result = $matches[2];
                    break;
            }

            $output = '';
            foreach (explode(PHP_EOL, $result) as $line) {
                if (strlen(trim($line)) != 0) {
                    $output .= $line . PHP_EOL;
                }
            }

            return $output;
        }, $markdown);
    }

    protected function drawSecondaryHeader($title)
    {
        $len = mb_strlen($title, 'UTF-8');

        $line = [];
        $line[] = '┌' . str_repeat('─', $len + 2) . '┐';
        $line[] = '│ ' . $title . ' │';
        $line[] = '└' . str_repeat('─', $len + 2) . '┘';

        return implode(PHP_EOL, $line);
    }

    protected function analyzeCodeLine($markdown)
    {
        return preg_replace_callback('|```(php)?\n(.*)```|s', function($matches) {
            $style = new Ansi();
            $style->bg('black');

            $method = 'getWholeFile';
            if ($matches[1] == 'php') {
                $method = 'getWholeFileWithLineNumbers';
            }

            $highlighter = new Highlighter('black');
            $result = $highlighter->$method(
                '<?php ' . PHP_EOL . trim($matches[2])
            );

            $block = substr($result, strpos($result, PHP_EOL) + 1);
            $lines = explode(PHP_EOL , $block);

            $max = 0;
            foreach ($lines as $line) {
                $len = $this->lenWithoutStyle($line);
                if ($len > $max) {
                    $max = $len;
                }
            }

            $blackSpace = (string) $style->value(' ');
            $twoBlackSpace = $blackSpace . $blackSpace;

            foreach ($lines as &$line) {
                $fill = $max - $this->lenWithoutStyle($line);
                $line = $twoBlackSpace . $line . str_repeat(' ', $fill) . $twoBlackSpace;
            }

            $emptyLine = str_repeat($blackSpace, $max + 4);
            array_unshift($lines, $emptyLine);

            if ($this->lenWithoutStyle(end($lines), true)) {
                array_push($lines, $emptyLine);
            }

            return  implode(PHP_EOL, $lines);
        }, $markdown);
    }

    protected function analyzeList($markdown)
    {
        $tokens = [];
        $markdown = preg_replace_callback('|\* (.*)|', function($matches) use (&$tokens) {
            $token = uniqid();
            $tokens[$token] = $matches[1];
            return $token;
        }, $markdown);

        if (!$tokens) {
            return $markdown;
        }

        $output = [];
        foreach ($tokens as $token => $value) {
            $output[] = str_replace($token, $value, $markdown);
        }

        return implode(Deck::SLIDE_DIVISOR, $output);
    }

    protected function lenWithoutStyle($line, $trim = false)
    {
        $clean = preg_replace('/\x1b(\[|\(|\))[;?0-9]*[0-9A-Za-z]/', '', $line);
        if ($trim) {
            $clean = trim($clean);
        }

        return mb_strlen($clean, 'UTF-8');
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
