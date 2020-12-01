<?php

namespace Rawilk\Settings\Drivers;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Contracts\Setting as SettingContract;

class Factory
{
    protected array $drivers = [];
    protected array $customCreators = [];

    public function __construct(protected Application $app)
    {
    }

    public function driver(string $driver = null): Driver
    {
        return $this->resolveDriver($driver);
    }

    public function extend(string $driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    protected function createDatabaseDriver(array $config): DatabaseDriver
    {
        return new DatabaseDriver(
            $this->app['db']->connection(Arr::get($config, 'connection')),
            $this->app['config']['settings.table']
        );
    }

    protected function createEloquentDriver(): EloquentDriver
    {
        return new EloquentDriver(app(SettingContract::class));
    }

    protected function getDefaultDriver(): string
    {
        return $this->app['config']['settings.driver'];
    }

    public function setDefaultDriver(string $driver): void
    {
        $this->app['config']['settings.driver'] = $driver;
    }

    protected function getDriverConfig(string $driver): ?array
    {
        return $this->app['config']["settings.drivers.{$driver}"];
    }

    protected function resolveDriver(string $driver = null): Driver
    {
        $driver = $driver ?: $this->getDefaultDriver();

        return $this->drivers[$driver] = $this->resolve($driver);
    }

    protected function resolve(string $driver): Driver
    {
        if (isset($this->drivers[$driver])) {
            return $this->drivers[$driver];
        }

        $driverConfig = $this->getDriverConfig($driver);

        if (! $driverConfig) {
            throw new InvalidArgumentException(
                "Missing settings driver config for '{$driver}'."
            );
        }

        if (isset($this->customCreators[$driverConfig['driver']])) {
            return $this->callCustomCreator($driverConfig);
        }

        $method = 'create' . ucfirst($driverConfig['driver']) . 'Driver';
        if (! method_exists($this, $method)) {
            throw new InvalidArgumentException(
                "Unsupported settings driver: {$driverConfig['driver']}."
            );
        }

        return $this->$method($driverConfig);
    }

    protected function callCustomCreator(array $config): Driver
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }
}
