<?php

declare(strict_types=1);

namespace System\Core;

final class Route
{
    public function __construct(private int $index)
    {
    }

    public function name(string $name): self
    {
        Router::setRouteName($this->index, $name);
        return $this;
    }
}
