<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializers\DotNotationContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\ReadableKeyGenerator;
use Rawilk\Settings\Tests\Support\Models\Team;

beforeEach(function () {
    config([
        'settings.driver' => 'database',
        'settings.table' => 'settings',
        'settings.cache' => false,
        'settings.cache_key_prefix' => 'settings.',
        'settings.encryption' => false,
        'settings.morphs' => true,
    ]);

    migrateTestTables();
    migrateMorphs();

    setDatabaseDriverConnection();

    Team::factory()->create();
});

test('morphs can be enabled and disabled', function () {
    // Should be enabled with the config value set to true
    expect(SettingsFacade::morphsAreEnabled())->toBeTrue();

    SettingsFacade::disableMorphs();
    expect(SettingsFacade::morphsAreEnabled())->toBeFalse();

    SettingsFacade::enableMorphs();
    expect(SettingsFacade::morphsAreEnabled())->toBeTrue();
});

test('team id can be set', function () {
    expect(SettingsFacade::getMorphId())->toBeNull();

    SettingsFacade::setMorphs(1);

    expect(SettingsFacade::getMorphId())->toBe(1);
});

it('sets a team id when saving', function () {
    $team = Team::first();
    SettingsFacade::setMorphs($team);

    SettingsFacade::set('foo', 'bar');

    $setting = DB::table('settings')->first();

    expect($setting->model_id)->toBe($team->id)
        ->and($setting->model_type)->toBe($team->getMorphClass());
});

it('updates team settings', function () {
    $team = Team::first();
    SettingsFacade::setMorphs($team);

    SettingsFacade::set('foo', 'bar');
    SettingsFacade::set('foo', 'updated');

    $this->assertDatabaseCount('settings', 1);

    $setting = DB::table('settings')->first();
    $value = unserialize($setting->value);

    expect($setting)->model_id->toBe($team->id)
        ->and($setting->model_type)->toBe($team->getMorphClass())
        ->and($value)->toBe('updated');
});

test('two teams can have the same setting', function () {
    $team1 = Team::first();
    $team2 = Team::factory()->create();

    SettingsFacade::setMorphs($team1);
    SettingsFacade::set('foo', 'team 1 value');

    SettingsFacade::setMorphs($team2);
    SettingsFacade::set('foo', 'team 2 value');

    $this->assertDatabaseCount('settings', 2);

    $setting1 = DB::table('settings')->where('model_id', $team1->id)->where('model_type', $team1->getMorphClass())->first();
    $setting2 = DB::table('settings')->where('model_id', $team2->id)->where('model_type', $team2->getMorphClass())->first();

    expect($setting1->model_id)->toBe($team1->id)
        ->and($setting1->model_type)->toBe($team2->getMorphClass())
        ->and(unserialize($setting1->value))->toBe('team 1 value')
        ->and($setting2->model_id)->toBe($team2->id)
        ->and($setting2->model_type)->toBe($team2->getMorphClass())
        ->and(unserialize($setting2->value))->toBe('team 2 value')
        ->and($setting1->key)->toBe($setting2->key);
});

it('checks if a team has a setting', function () {
    SettingsFacade::set('foo', 'null team');
    expect(SettingsFacade::has('foo'))->toBeTrue();

    $team = Team::first();

    SettingsFacade::setMorphs($team);
    expect(SettingsFacade::has('foo'))->toBeFalse();

    SettingsFacade::set('foo', 'team value');
    expect(SettingsFacade::has('foo'))->toBeTrue();
});

it('gets a team setting value', function () {
    $team = Team::first();
    $team2 = Team::factory()->create();

    // Also verify that no team id can be used
    SettingsFacade::setMorphs(null);
    SettingsFacade::set('foo', 'no team value');
    expect(SettingsFacade::get('foo'))->toBe('no team value');

    SettingsFacade::setMorphs($team);
    SettingsFacade::set('foo', 'team value');
    expect(SettingsFacade::get('foo'))->toBe('team value');

    SettingsFacade::setMorphs($team2);
    SettingsFacade::set('foo', 'team 2 value');
    expect(SettingsFacade::get('foo'))->toBe('team 2 value');
});

it('forgets the settings for a team', function () {
    $team = Team::first();

    SettingsFacade::set('foo', 'no team value');

    SettingsFacade::setMorphs($team);
    SettingsFacade::set('foo', 'team value');

    $this->assertDatabaseCount('settings', 2);

    SettingsFacade::forget('foo');

    $this->assertDatabaseCount('settings', 1);
    $this->assertDatabaseMissing('settings', [
        'model_id' => $team->id,
    ]);
    $this->assertDatabaseHas('settings', [
        'model_id' => null,
    ]);
});

test('the cache is scoped for teams', function () {
    $team = Team::first();
    $team2 = Team::factory()->create();

    SettingsFacade::setMorphs($team);
    SettingsFacade::set('foo', 'team 1 value');

    SettingsFacade::setMorphs($team2);
    SettingsFacade::set('foo', 'team 2 value');

    SettingsFacade::enableCache();

    expect(SettingsFacade::get('foo'))->toBe('team 2 value');

    SettingsFacade::setMorphs($team);

    expect(SettingsFacade::get('foo'))->toBe('team 1 value');
});

test("all of a team's settings can be retrieved at once", function () {
    $team = Team::first();

    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $settings->set('one', 'non-team value');
    $settings->context(new Context(['id' => 'foo']))->set('one', 'non-team value context 1');

    $settings->setMorphs($team);

    $settings->set('one', 'team value');
    $settings->set('two', 'team value 2');
    $settings->context(new Context(['id' => 'foo']))->set('one', 'team value context 1');

    $storedSettings = $settings->all();

    expect($storedSettings)->toHaveCount(3)
        ->and($storedSettings[0]->key)->toBe('one')
        ->and($storedSettings[0]->model_id)->toBe($team->id)
        ->and($storedSettings[0]->value)->toBe('team value')
        ->and($storedSettings[1]->key)->toBe('two')
        ->and($storedSettings[1]->model_id)->toBe($team->id)
        ->and($storedSettings[1]->value)->toBe('team value 2')
        ->and($storedSettings[2]->key)->toBe('one')
        ->and($storedSettings[2]->model_id)->toBe($team->id)
        ->and($storedSettings[2]->value)->toBe('team value context 1')
        ->and($storedSettings[2]->original_key)->toBe('one:c:::id:foo');

    // Can get all team settings without context attached to them.
    $nonContextSettings = $settings->context(false)->all();

    expect($nonContextSettings)->toHaveCount(2)
        ->and($nonContextSettings->pluck('value'))->not->toContain('team value context 1');
});

test("a team's settings can be flushed", function () {
    $team = Team::first();

    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $settings->set('one', 'non-team value');

    $settings->setMorphs($team);
    $settings->set('one', 'team value');

    $this->assertDatabaseCount('settings', 2);

    $settings->flush();

    $this->assertDatabaseCount('settings', 1);

    $this->assertDatabaseMissing('settings', [
        'model_id' => $team->id,
    ]);
});

test('when a team is set, a different team can be used for a single call', function () {
    $team = Team::first();
    $otherTeam = Team::factory()->create();

    SettingsFacade::setMorphs($team);
    SettingsFacade::set('foo', 'team 1');

    SettingsFacade::usingMorph($otherTeam)->set('foo', 'team 2');

    $this->assertDatabaseCount('settings', 2);
    expect(SettingsFacade::getMorphId())->toBe($team->id)
        ->and(SettingsFacade::get('foo'))->toBe('team 1')
        ->and(SettingsFacade::usingMorph($otherTeam)->get('foo'))->toBe('team 2');
});

test('when a team is set, team id can be set to null temporarily', function () {
    $team = Team::first();

    SettingsFacade::setMorphs($team);
    SettingsFacade::set('foo', 'team 1');

    SettingsFacade::withoutMorphs()->set('foo', 'global');

    $this->assertDatabaseCount('settings', 2);
    expect(SettingsFacade::getMorphId())->toBe($team->id)
        ->and(SettingsFacade::get('foo'))->toBe('team 1')
        ->and(SettingsFacade::withoutMorphs()->get('foo'))->toBe('global');
});

test('a team can be used temporarily when teams are not enabled', function () {
    SettingsFacade::disableMorphs();
    SettingsFacade::set('foo', 'global');

    $team = Team::first();
    SettingsFacade::usingMorph($team)->set('foo', 'team 1');

    $this->assertDatabaseCount('settings', 2);
    expect(SettingsFacade::getMorphId())->toBeNull()
        ->and(SettingsFacade::morphsAreEnabled())->toBeFalse()
        ->and(SettingsFacade::get('foo'))->toBe('global')
        ->and(SettingsFacade::usingMorph($team)->get('foo'))->toBe('team 1');
});
