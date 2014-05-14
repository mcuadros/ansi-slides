<?php

require __DIR__.'/../vendor/autoload.php';

use ANSISlides\Deck;
use ANSISlides\Slide;
use ANSISlides\SlideRenderer;

$renderer = new SlideRenderer(
    (int) exec('tput cols'),
    (int) exec('tput lines')
);

$deck = new Deck(file_get_contents('example/slides.md'));
$deck->setRenderer($renderer);
$deck->play();
