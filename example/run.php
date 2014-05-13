<?php

require __DIR__.'/../vendor/autoload.php';

use ANSISlides\Slide;
use ANSISlides\SlideRenderer;

$renderer = new SlideRenderer(
    (int) exec('tput cols'),
    (int) exec('tput lines')
);


$i=0;

foreach (explode(
    '---',
    file_get_contents('example/slides.md')
) as $value) {
    print chr(27) . "[2J" . chr(27) . "[;H";

    $slide = new Slide();
    $slide->setMarkdown($value);

    $render = $renderer->render($slide);
    echo $render;
    sleep(2);
    if ($i >= 2) $i = 0;
}
