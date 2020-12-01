<?php

namespace Rawilk\Settings\Support;

use Countable;
use JetBrains\PhpStorm\Pure;
use OutOfBoundsException;

class Context implements Countable
{
    protected array $arguments = [];

    public function __construct(array $arguments = [])
    {
        foreach ($arguments as $name => $value) {
            $this->set(name: $name, value: $value);
        }
    }

    public function get(string $name)
    {
        if (! $this->has($name)) {
            throw new OutOfBoundsException(
                sprintf('"%s" is not part of the context.', $name)
            );
        }

        return $this->arguments[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->arguments[$name]);
    }

    public function remove(string $name): self
    {
        unset($this->arguments[$name]);

        return $this;
    }

    public function set(string $name, $value): self
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    #[Pure]
    public function count(): int
    {
        return count($this->arguments);
    }
}
