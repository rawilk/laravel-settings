<?php

declare(strict_types=1);

use Rawilk\Settings\Drivers\DatabaseDriver;

/**
 * Note: Setting `false` as the team id in some calls is essentially like setting it to `false` in the config file.
 */
beforeEach(function () {
    config([
        'settings.driver' => 'database',
        'settings.teams' => true,
        'settings.team_foreign_key' => 'team_id',
    ]);

    $this->driver = new DatabaseDriver(
        connection: app('db')->connection(),
        table: 'settings',
        teamForeignKey: 'team_id',
    );

    migrateTeams();
    setDatabaseDriverConnection();
});

it('creates new entries', function () {
    $this->driver->set('foo', 'bar', false);

    $this->assertDatabaseCount('settings', 1);

    $this->assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => 'bar',
        'team_id' => null,
    ]);
});

it('creates new entries for teams', function () {
    $this->driver->set('foo', 'bar', 1);

    $this->assertDatabaseCount('settings', 1);

    $this->assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => 'bar',
        'team_id' => 1,
    ]);
});

it('updates existing values', function () {
    $this->driver->set('foo', 'bar', false);

    $this->assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => 'bar',
        'team_id' => null,
    ]);

    $this->driver->set('foo', 'updated value', false);

    $this->assertDatabaseCount('settings', 1);

    $this->assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => 'updated value',
        'team_id' => null,
    ]);
});

it('updates team values', function () {
    $this->driver->set('foo', 'no team value', null);
    $this->driver->set('foo', 'team value', 1);

    $this->assertDatabaseCount('settings', 2);

    $this->driver->set('foo', 'updated team value', 1);

    $this->assertDatabaseCount('settings', 2);

    $this->assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => 'no team value',
        'team_id' => null,
    ]);

    $this->assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => 'updated team value',
        'team_id' => 1,
    ]);
});

it('checks if a setting is persisted', function () {
    expect($this->driver->has('foo', false))->toBeFalse();

    $this->driver->set('foo', 'bar', false);

    expect($this->driver->has('foo', false))->toBeTrue();
});

it('checks if a team setting is persisted', function () {
    $this->driver->set('foo', 'no team value', null);
    expect($this->driver->has('foo', 1))->toBeFalse();

    $this->driver->set('foo', 'team value', 1);
    expect($this->driver->has('foo', 1))->toBeTrue();
});

it('gets a persisted setting value', function () {
    $this->driver->set('foo', 'some value', false);

    expect($this->driver->get(key: 'foo', teamId: false))->toBe('some value');
});

it('returns a default value for settings that are not persisted', function () {
    expect($this->driver->get(key: 'foo', default: 'my default value', teamId: false))->toBe('my default value');
});

it('gets a persisted team value', function () {
    $this->driver->set('foo', 'no team value', null);
    $this->driver->set('foo', 'team value', 1);

    expect($this->driver->get(key: 'foo', teamId: 1))->toBe('team value');
});

it('gets a default value for a team', function () {
    $this->driver->set('foo', 'no team value', null);

    expect($this->driver->get(key: 'foo', default: 'my default', teamId: 1))->toBe('my default');
});

it('removes persisted settings', function () {
    $this->driver->set('foo', 'bar', false);

    expect($this->driver->has('foo', false))->toBeTrue();

    $this->driver->forget('foo', false);

    expect($this->driver->has('foo', false))->toBeFalse();
});

it('removes persisted team values', function () {
    $this->driver->set('foo', 'team 1 value', 1);
    $this->driver->set('foo', 'team 2 value', 2);

    $this->assertDatabaseCount('settings', 2);

    $this->driver->forget('foo', 1);

    $this->assertDatabaseCount('settings', 1);

    $this->assertDatabaseMissing('settings', [
        'key' => 'foo',
        'team_id' => 1,
    ]);
});

it('can get all persisted settings', function () {
    $this->driver->set('one', 'value 1', false);
    $this->driver->set('two', 'value 2', false);

    $settings = $this->driver->all();

    expect($settings)->toHaveCount(2)
        ->first()->value->toBe('value 1')
        ->and($settings[1]->value)->toBe('value 2');
});

it('can get a subset of persisted settings', function () {
    $this->driver->set('one', 'value 1', false);
    $this->driver->set('two', 'value 2', false);
    $this->driver->set('three', 'value 3', false);

    $settings = $this->driver->all(keys: ['one', 'three']);

    expect($settings)->toHaveCount(2)
        ->first()->value->toBe('value 1')
        ->and($settings[1]->value)->toBe('value 3');
});

it('can get all of a teams persisted settings', function () {
    $this->driver->set('one', 'value 1', 1);
    $this->driver->set('two', 'value 2', 1);
    $this->driver->set('one', 'team 2 value 1', 2);

    $settings = $this->driver->all(teamId: 1);

    expect($settings)->toHaveCount(2)
        ->first()->value->toBe('value 1')
        ->and($settings[1]->value)->toBe('value 2');
});

it('can do partial lookups on all', function () {
    $this->driver->set('one:1', 'value 1');
    $this->driver->set('one:2', 'value 2_1');
    $this->driver->set('two:1', 'value 2');

    $settings = $this->driver->all(keys: ':1');

    expect($settings)->toHaveCount(2)
        ->first()->value->toBe('value 1')
        ->and($settings[1]->value)->toBe('value 2');
});

it('can delete all settings', function () {
    $this->driver->set('one', 'one', false);
    $this->driver->set('two', 'two', false);

    $this->assertDatabaseCount('settings', 2);

    $this->driver->flush();

    $this->assertDatabaseCount('settings', 0);
});

it('can delete all team settings', function () {
    $this->driver->set('one', 'one', 1);
    $this->driver->set('two', 'two', 1);
    $this->driver->set('two', 'team two', 2);

    $this->assertDatabaseCount('settings', 3);

    $this->driver->flush(teamId: 1);

    $this->assertDatabaseCount('settings', 1);
});

it('can flush a subset of settings', function () {
    $this->driver->set('one', 'one', false);
    $this->driver->set('two', 'two', false);
    $this->driver->set('three', 'three', false);

    $this->assertDatabaseCount('settings', 3);

    $this->driver->flush(keys: ['one', 'three']);

    $this->assertDatabaseCount('settings', 1);

    $this->assertDatabaseMissing('settings', [
        'key' => 'one',
    ]);

    $this->assertDatabaseMissing('settings', [
        'key' => 'three',
    ]);
});
