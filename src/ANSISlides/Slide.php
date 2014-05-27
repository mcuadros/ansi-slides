<?php

namespace ANSISlides;

use Packaged\Figlet\Figlet;
use JakubOnderka\PhpConsoleColor\ConsoleColor;
use ANSISlides\Deck\Transition;
use ANSISlides\Slide\Frame;

class Slide
{
    //From https://groups.google.com/forum/#!topic/iterm2-discuss/qACd1yHCXwo
    const ITERM2_BACKGROUND = 'osascript -e \'tell application "iTerm" to set background image path of current session of current terminal to "%s"\'';

    private $cols;
    private $lines;
    private $markdown;
    private $renderer;
    private $color;
    private $path;
    private $transition;
    private $number;
    private $total;
    private $hasBackground;
    private $showPagination;
    private $background;
    private $foreground;

    public function __construct($markdown, $fg = 'light_yellow', $bg = 'dark_gray') {
        $this->markdown = $markdown;
        $this->foreground = $fg;
        $this->background = 'bg_' . $bg;
        $this->transition = new Transition\None();

        $this->color = new ConsoleColor();
    }

    public function showPagination($bool)
    {
        $this->showPagination = $bool;
    }

    public function setNumberSlide($number)
    {
        $this->number = $number;
    }

    public function setTotalSlides($total)
    {
        $this->total = $total;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setTransition(Transition $transition)
    {
        $this->transition = $transition;
    }

    public function play($cols, $lines, Slide $prev = null)
    {
        $this->transition->play($prev, $this, $cols, $lines);
    }

    public function render($cols, $lines)
    {
        $this->cols = $cols;
        $this->lines = $lines;

        $markdown = $this->markdown;
        $markdown = $this->analyzeEmphasis($markdown);
        $markdown = $this->analyzeImage($markdown);
        $markdown = $this->analyzeHeaders($markdown);
        $markdown = $this->analyzeStyle($markdown);
        $markdown = $this->analyzeLonglines($markdown);
        $markdown = $this->analyzeCodeBlock($markdown);

        return $this->format($markdown);
    }

    protected function format($markdown)
    {
        foreach(explode(Deck::SLIDE_DIVISOR, $markdown) as $md) {
            $md = $this->valign($md);
            $md = $this->align($md);
            $md = $this->info($md);
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
            if ($toFill < 0) {
                $toFill = 0;
            }

            $result[] = sprintf('%s%s%s',
                str_repeat(' ', ceil($toFill)),
                $line,
                $this->applyColor(str_repeat(' ', floor($toFill)))
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
            $line = $this->applyColor($line);
        }

        return implode(PHP_EOL, $lines);
    }

    protected function info($markdown)
    {
        if (
            !$this->showPagination ||
            $this->number == 1 ||
            $this->hasBackground
        ) {
            return $markdown;
        }

        $info = $this->callInfoLine();

        $lines = explode(PHP_EOL, $markdown);
        array_shift($lines);
        $last = array_pop($lines);
        $lines[] = $info;
        $lines[] = $last;

        return implode(PHP_EOL, $lines);
    }

    private function callInfoLine()
    {
        $pagination = sprintf('%d/%d', $this->number, $this->total);

        return str_pad($pagination, $this->cols - 2, ' ', STR_PAD_LEFT) . '  ';
    }

    protected function analyzeStyle($markdown)
    {
        $markdown = preg_replace_callback('|(!\[(.*),(.*)\]\n)|', function($matches) {
            $this->foreground = $this->clearStyle($matches[2]);
            $this->background = 'bg_' . $this->clearStyle($matches[3]);

            return PHP_EOL;
        }, $markdown);

        return preg_replace_callback('|(!\[([a-z_]*),([a-z_]*)\])\((.*?)\)|', function($matches) {
            $style = [
                $this->clearStyle($matches[2]),
                'bg_' . $this->clearStyle($matches[3])
            ];

            return $this->color->apply($style, $matches[4]);
        }, $markdown);
    }

    protected function analyzeEmphasis($markdown)
    {
        $markdown = preg_replace_callback('|[*]{2}(.*?)[*]{2}|', function($matches) {
            return "\033[4m" . $matches[1] . "\033[24m";
        }, $markdown);

        $markdown = preg_replace_callback('|[*](.*?)[*]|', function($matches) {
            return "\033[1m" . $matches[1] . "\033[21m";
        }, $markdown);

        return $markdown;
    }

    protected function analyzeImage($markdown)
    {
        preg_match('|\!\[(\w)\]\((.*)\)|', $markdown, $matches);
        if (!$matches) {
            $this->clearBackground();

            return $markdown;
        }

        $this->foreground = 'default';
        $this->background = 'bg_default';

        $this->setBackground($matches[2]);
        $this->hasBackground = true;

        return '';
    }

    protected function setBackground($image)
    {
        $cmd = sprintf(self::ITERM2_BACKGROUND, $this->path . '/' .$image);
        shell_exec($cmd);
    }

    protected function clearBackground()
    {
        $cmd = sprintf(self::ITERM2_BACKGROUND, '');
        shell_exec($cmd);
    }

    protected function analyzeHeaders($markdown)
    {
        return preg_replace_callback('|(!\[.*\])?(#+) (.*)|', function($matches) {
            switch ($matches[2]) {
                case '#':
                    $result = Figlet::create($matches[3], '../../../../../fonts/ansi');                    break;
                case '##':
                    $result = $this->drawSecondaryHeader($matches[3]);
                    break;
                default:
                    $result = $matches[3];
                    break;
            }

            $output = '';
            foreach (explode(PHP_EOL, $result) as $line) {
                if (strlen(trim($line)) != 0) {
                    $output .= $matches[1] . $line . PHP_EOL;
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

    protected function analyzeCodeBlock($markdown)
    {
        return preg_replace_callback('|```(php)?\n(.*)```|s', function($matches) {
            $block = $matches[2];
            if ($matches[1] == 'php') {
                $block = $this->prepareCodeBlockPHP($block);
                $style = 'black';
            } else {
                $block = $this->prepareTextBlockPHP($block);
                $style = [$this->foreground, $this->background];
            }

            $lines = explode(PHP_EOL , $block);

            $max = 0;
            foreach ($lines as $line) {
                $len = $this->lenWithoutStyle($line);
                if ($len > $max) {
                    $max = $len;
                }
            }

            $blackSpace = $this->color->apply($style, ' ');

            $twoBlackSpace = $blackSpace . $blackSpace;

            foreach ($lines as &$line) {
                $fill = $max - $this->lenWithoutStyle($line);
                $line = $twoBlackSpace . $line . str_repeat($blackSpace, $fill) . $twoBlackSpace;
            }

            $emptyLine = str_repeat($blackSpace, $max + 4);
            array_unshift($lines, $emptyLine);

            if ($this->lenWithoutStyle(end($lines), true)) {
                array_push($lines, $emptyLine);
            }

            return implode(PHP_EOL, $lines);
        }, $markdown);
    }

    protected function prepareTextBlockPHP($markdown)
    {
        $style = [
            'white',
            $this->background
        ];

        $lines = explode(PHP_EOL , $markdown);
        foreach ($lines as &$line) {
            $line = $this->color->apply($style, $line);
        }

        return implode(PHP_EOL, $lines);
    }

    protected function prepareCodeBlockPHP($markdown)
    {
        $highlighter = new Highlighter('black');
        $result = $highlighter->getWholeFileWithLineNumbers(
            '<?php ' . PHP_EOL . trim($markdown, "\t\n\r")
        );

        return substr($result, strpos($result, PHP_EOL) + 1);
    }

    protected function analyzeLonglines($markdown)
    {
        $lines = explode(PHP_EOL , $markdown);
        foreach ($lines as &$line) {
            if ($this->lenWithoutStyle($line, true) > $this->cols - 12) {
                $line = $this->wordWrapLine($line, $this->cols - 12);
            }
        }

        return implode(PHP_EOL, $lines);
    }

    private function wordWrapLine($text)
    {
        preg_match('|(!\[.*\])?(.*)|', $text, $matches);
        if (strlen($matches[2]) > $this->cols - 12) {
            $break = PHP_EOL . $matches[1];

            return $matches[1] . wordwrap($matches[2], $this->cols - 12, $break);
        }

        return $text;
    }

    protected function lenWithoutStyle($line, $trim = false)
    {
        $clean = $this->clearStyle($line);
        if ($trim) {
            $clean = trim($clean);
        }

        return mb_strlen($clean, 'UTF-8');
    }

    protected function clearStyle($line)
    {
        return preg_replace('/\x1b(\[|\(|\))[;?0-9]*[0-9A-Za-z]/', '', $line);
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

    protected function applyColor($value)
    {
        $style = [$this->foreground, $this->background];

        return $this->color->apply($style, $value);
    }
}
