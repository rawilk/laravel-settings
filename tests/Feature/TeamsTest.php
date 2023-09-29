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
        'settings.teams' => true,
        'settings.team_foreign_key' => 'team_id',
    ]);

    migrateTestTables();
    migrateTeams();

    setDatabaseDriverConnection();

    Team::factory()->create();
});

test('teams can be enabled and disabled', function () {
    // Should be enabled with the config value set to true
    expect(SettingsFacade::teamsAreEnabled())->toBeTrue();

    SettingsFacade::disableTeams();
    expect(SettingsFacade::teamsAreEnabled())->toBeFalse();

    SettingsFacade::enableTeams();
    expect(SettingsFacade::teamsAreEnabled())->toBeTrue();
});

test('team id can be set', function () {
    expect(SettingsFacade::getTeamId())->toBeNull();

    SettingsFacade::setTeamId(1);

    expect(SettingsFacade::getTeamId())->toBe(1);
});

it('sets a team id when saving', function () {
    $team = Team::first();
    SettingsFacade::setTeamId($team);

    SettingsFacade::set('foo', 'bar');

    $setting = DB::table('settings')->first();

    expect($setting->team_id)->toBe($team->id);
});

it('updates team settings', function () {
    $team = Team::first();
    SettingsFacade::setTeamId($team);

    SettingsFacade::set('foo', 'bar');
    SettingsFacade::set('foo', 'updated');

    $this->assertDatabaseCount('settings', 1);

    $setting = DB::table('settings')->first();
    $value = unserialize($setting->value);

    expect($setting)->team_id->toBe($team->id)
        ->and($value)->toBe('updated');
});

test('two teams can have the same setting', function () {
    $team1 = Team::first();
    $team2 = Team::factory()->create();

    SettingsFacade::setTeamId($team1);
    SettingsFacade::set('foo', 'team 1 value');

    SettingsFacade::setTeamId($team2);
    SettingsFacade::set('foo', 'team 2 value');

    $this->assertDatabaseCount('settings', 2);

    $setting1 = DB::table('settings')->where('team_id', $team1->id)->first();
    $setting2 = DB::table('settings')->where('team_id', $team2->id)->first();

    expect($setting1->team_id)->toBe($team1->id)
        ->and(unserialize($setting1->value))->toBe('team 1 value')
        ->and($setting2->team_id)->toBe($team2->id)
        ->and(unserialize($setting2->value))->toBe('team 2 value')
        ->and($setting1->key)->toBe($setting2->key);
});

it('checks if a team has a setting', function () {
    SettingsFacade::set('foo', 'null team');
    expect(SettingsFacade::has('foo'))->toBeTrue();

    $team = Team::first();

    SettingsFacade::setTeamId($team);
    expect(SettingsFacade::has('foo'))->toBeFalse();

    SettingsFacade::set('foo', 'team value');
    expect(SettingsFacade::has('foo'))->toBeTrue();
});

it('gets a team setting value', function () {
    $team = Team::first();
    $team2 = Team::factory()->create();

    // Also verify that no team id can be used
    SettingsFacade::setTeamId(null);
    SettingsFacade::set('foo', 'no team value');
    expect(SettingsFacade::get('foo'))->toBe('no team value');

    SettingsFacade::setTeamId($team);
    SettingsFacade::set('foo', 'team value');
    expect(SettingsFacade::get('foo'))->toBe('team value');

    SettingsFacade::setTeamId($team2);
    SettingsFacade::set('foo', 'team 2 value');
    expect(SettingsFacade::get('foo'))->toBe('team 2 value');
});

it('forgets the settings for a team', function () {
    $team = Team::first();

    SettingsFacade::set('foo', 'no team value');

    SettingsFacade::setTeamId($team);
    SettingsFacade::set('foo', 'team value');

    $this->assertDatabaseCount('settings', 2);

    SettingsFacade::forget('foo');

    $this->assertDatabaseCount('settings', 1);
    $this->assertDatabaseMissing('settings', [
        'team_id' => $team->id,
    ]);
    $this->assertDatabaseHas('settings', [
        'team_id' => null,
    ]);
});

test('the cache is scoped for teams', function () {
    $team = Team::first();
    $team2 = Team::factory()->create();

    SettingsFacade::setTeamId($team);
    SettingsFacade::set('foo', 'team 1 value');

    SettingsFacade::setTeamId($team2);
    SettingsFacade::set('foo', 'team 2 value');

    SettingsFacade::enableCache();

    expect(SettingsFacade::get('foo'))->toBe('team 2 value');

    SettingsFacade::setTeamId($team);

    expect(SettingsFacade::get('foo'))->toBe('team 1 value');
});

test("all of a team's settings can be retrieved at once", function () {
    $team = Team::first();

    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $settings->set('one', 'non-team value');
    $settings->context(new Context(['id' => 'foo']))->set('one', 'non-team value context 1');

    $settings->setTeamId($team);

    $settings->set('one', 'team value');
    $settings->set('two', 'team value 2');
    $settings->context(new Context(['id' => 'foo']))->set('one', 'team value context 1');

    $storedSettings = $settings->all();

    expect($storedSettings)->toHaveCount(3)
        ->and($storedSettings[0]->key)->toBe('one')
        ->and($storedSettings[0]->team_id)->toBe($team->id)
        ->and($storedSettings[0]->value)->toBe('team value')
        ->and($storedSettings[1]->key)->toBe('two')
        ->and($storedSettings[1]->team_id)->toBe($team->id)
        ->and($storedSettings[1]->value)->toBe('team value 2')
        ->and($storedSettings[2]->key)->toBe('one')
        ->and($storedSettings[2]->team_id)->toBe($team->id)
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

    $settings->setTeamId($team);
    $settings->set('one', 'team value');

    $this->assertDatabaseCount('settings', 2);

    $settings->flush();

    $this->assertDatabaseCount('settings', 1);

    $this->assertDatabaseMissing('settings', [
        'team_id' => $team->id,
    ]);
});
