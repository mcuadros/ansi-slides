<?php

namespace ANSISlides\Tests;

use ANSISlides\Slide;
use ANSISlides\SlideRenderer;

class SlideRendererTest extends TestCase
{
    public function testRender()
    {
        $this->clearScreen();

        $slide = new Slide();
        $slide->setMarkdown('# Foo
FF');

        $renderer = new SlideRenderer(
            (int) exec('tput cols'),
            (int) exec('tput lines')
        );

        $renderer->render($slide);
    }

    private function clearScreen()
    {
        print chr(27) . "[2J" . chr(27) . "[;H";
    }
}
