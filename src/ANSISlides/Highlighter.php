<?php

namespace ANSISlides;

use JakubOnderka\PhpConsoleColor\ConsoleColor;
use JakubOnderka\PhpConsoleHighlighter\Highlighter as BaseHighlighter;

class Highlighter extends BaseHighlighter
{
    private $defaultTheme = [
        self::TOKEN_STRING => 'red',
        self::TOKEN_COMMENT => 'yellow',
        self::TOKEN_KEYWORD => 'green',
        self::TOKEN_DEFAULT => 'white',
        self::TOKEN_HTML => 'cyan',
        self::ACTUAL_LINE_MARK  => 'red',
        self::LINE_NUMBER => 'dark_gray',
    ];

    public function __construct($bgColor)
    {
        $bgColor = 'bg_' . $bgColor;

        $color = new ConsoleColor();

        foreach ($this->defaultTheme as $name => $styles) {
            if (!$color->hasTheme($name)) {
                $color->addTheme($name, [$bgColor, $styles]);
            }
        }

        parent::__construct($color);
    }
}
