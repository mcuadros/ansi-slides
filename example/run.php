<?php

require __DIR__.'/../vendor/autoload.php';

use ANSISlides\Deck;

$deck = new Deck(file_get_contents('example/slides.md'));
$deck->play((int) exec('tput cols'), (int) exec('tput lines'));
