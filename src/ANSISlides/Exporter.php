<?php

namespace ANSISlides;

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use SensioLabs\AnsiConverter\Theme\Theme;

class Exporter
{
    private $theme;
    private $converter;
    private $slidesHTML = [];
    private $deckTemplate;
    private $slideTemplate;

    public function __construct()
    {
        $this->theme = new Theme();
        $this->converter = new AnsiToHtmlConverter($this->theme);

        $this->deckTemplate = $this->loadTemplate('deck.html');
        $this->slideTemplate = $this->loadTemplate('slide.html');
    }

    private function loadTemplate($template)
    {
        return file_get_contents(__DIR__ . '/../../resource/' . $template);
    }

    public function slideToHTML($content)
    {
        $html = $this->converter->convert($content);
        $html = sprintf($this->slideTemplate, $html);

        $this->slidesHTML[] = $html;
    }

    private function applyTemplate($template, $html)
    {
        return sprintf($this->$template, $html);
    }

    public function save($output)
    {
        $html = sprintf(
            $this->deckTemplate,
            $this->theme->asCSS(),
            implode(PHP_EOL, $this->slidesHTML)
        );

        file_put_contents($output, $html);
    }
}
