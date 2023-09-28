<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Facade;
use Rawilk\Settings\Contracts\ContextSerializer;
use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Contracts\KeyGenerator;
use Rawilk\Settings\Contracts\ValueSerializer;
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

test('context serializers are configured correctly')
    ->expect('Rawilk\Settings\Support\ContextSerializers')
    ->toBeClasses()
    ->classes()
    ->toImplement(ContextSerializer::class)
    ->toExtendNothing()
    ->toHaveSuffix('Serializer');

test('key generators are configured correctly')
    ->expect('Rawilk\Settings\Support\KeyGenerators')
    ->toBeClasses()
    ->classes()
    ->toImplement(KeyGenerator::class)
    ->toExtendNothing()
    ->toHaveSuffix('Generator');

test('value serializers are configured correctly')
    ->expect('Rawilk\Settings\Support\ValueSerializers')
    ->toBeClasses()
    ->classes()
    ->toImplement(ValueSerializer::class)
    ->toExtendNothing()
    ->toHaveSuffix('Serializer');

test('events are configured correctly')
    ->expect('Rawilk\Settings\Events')
    ->toBeClasses()
    ->classes()
    ->toExtendNothing()
    ->toBeFinal()
    ->toUse([
        Dispatchable::class,
        SerializesModels::class,
    ]);
