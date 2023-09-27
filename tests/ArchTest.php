<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Drivers\Factory;

it('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'ddd'])
    ->each->not->toBeUsed();

test('strict types are used')
    ->expect('Rawilk\Settings')
    ->toUseStrictTypes();

test('only interfaces are placed in the contracts directory')
    ->expect('Rawilk\Settings\Contracts')
    ->toBeInterfaces();

test('each driver implements the Driver contract')
    ->expect('Rawilk\Settings\Drivers')
    ->classes()
    ->toImplement(Driver::class)->ignoring(Factory::class)
    ->toHaveSuffix('Driver')->ignoring(Factory::class);

test('only facades are used in the Facades directory')
    ->expect('Rawilk\Settings\Facades')
    ->toExtend(Facade::class)
    ->classes()
    ->not->toHaveSuffix('Facade');

test('models are configured correctly and are extendable')
    ->expect('Rawilk\Settings\Models')
    ->classes()
    ->toExtend(Model::class)
    ->not->toBeFinal();
