<?php

namespace Rapid\Voyager\Console;

class Progress
{

    public int $value = 0;
    public string $text = '';

    public function __construct(
        protected int $max = 100,
        protected string $title = '',
    )
    {
    }

    protected int $lastLength = 0;
    protected bool $isShown = false;

    public function show()
    {
        if ($this->isShown)
        {
            echo @str_repeat("\r", $this->lastLength);
        }
        else
        {
            $this->isShown = true;
            echo "\n{$this->title}\n";
        }

        $echo = "[";
        $complete = round(($this->value / $this->max) * 15);
        $complete = max(min($complete, 15), 0);
        $echo .= @str_repeat('|', $complete);
        $echo .= @str_repeat(' ', 15 - $complete);
        $echo .= "]";

        $percent = round(($this->value / $this->max) * 100);
        $echo .= " {$percent}%  {$this->text}";
        if (strlen($echo) < $this->lastLength)
        {
            $echo .= str_repeat(' ', $this->lastLength - strlen($echo));
        }

        echo $echo;
        $this->lastLength = strlen($echo);
    }

    public function finish()
    {
        $this->value = $this->max;
        $this->show();
        echo "\n";
    }

}