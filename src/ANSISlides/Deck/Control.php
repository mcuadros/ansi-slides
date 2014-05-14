<?php

namespace ANSISlides\Deck;

class Control
{
    const EVENT_NEXT = 1;
    const EVENT_PREV = 2;

    private $stdin;

    public function __construct()
    {
        $this->stdin = fopen('php://stdin', 'r');
        system("stty -icanon");
    }

    public function wait()
    {
        $char = fgetc($this->stdin);
        return $this->input($char);
    }

    private function input($char)
    {
        if ($char == ' ') {
            return self::EVENT_NEXT;
        }

        return self::EVENT_PREV;
    }
}
