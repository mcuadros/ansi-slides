<?php

namespace ANSISlides\Deck;

class Control
{
    const EVENT_QUIT = 1;
    const EVENT_NEXT = 2;
    const EVENT_PREV = 3;

    private $stdin;

    public function __construct()
    {
        $this->stdin = fopen('php://stdin', 'r');
        system("stty -icanon -echo");
    }

    public function wait()
    {
        $char = '';
        do {
            $char .= fgetc($this->stdin);
            $event = $this->input($char);
        } while($event === false);

        return $event;
    }

    private function input($char)
    {
        switch ($char) {
            case "\033\033":
            case 'q':
                return self::EVENT_QUIT;
            case ' ':
            case "\n":
            case "\033[B":
            case "\033[C":
                return self::EVENT_NEXT;
            case "\033[A":
            case "\033[D":
                return self::EVENT_PREV;
            case "\033":
            case "\033[":
                return false;
        }

        return null;
    }
}
