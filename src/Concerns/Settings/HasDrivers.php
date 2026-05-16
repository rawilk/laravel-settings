<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use Closure;
use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Drivers\Factory;
use UnitEnum;

trait HasDrivers
{
    protected Driver $driver;

    public function driver(null|string|UnitEnum $driver = null): Driver
    {
        if (is_null($driver)) {
            return $this->driver;
        }

        return app(Factory::class)->driver($driver);
    }

    public function setDriver(Driver $driver): static
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Use a specific driver for a single callback.
     */
    public function usingDriver(Driver|string|UnitEnum $driver, Closure $callback): mixed
    {
        $previous = $this->driver;

        $this->setDriver($driver instanceof Driver ? $driver : $this->driver($driver));

        try {
            return $callback($this);
        } finally {
            $this->setDriver($previous);
        }
    }

    /**
     * Register a custom driver creator Closure.
     */
    public function extend(string $driver, Closure $callback): static
    {
        app(Factory::class)->extend($driver, $callback);

        return $this;
    }
}
