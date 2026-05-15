<?php

declare(strict_types=1);

use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\TeamResolver;
use Rawilk\Settings\Tests\Support\Models\Team;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    config([
        'settings.cache' => false,
        'settings.encryption' => false,
        'settings.teams' => true,
    ]);

    Team::factory()->create();
});

test('teams can be enabled and disabled', function () {
    expect(app(TeamResolver::class)->disabled())->toBeFalse();

    settings()->disableTeams();
    expect(app(TeamResolver::class)->disabled())->toBeTrue();

    settings()->enableTeams();
    expect(app(TeamResolver::class)->disabled())->toBeFalse();
});

test('a default team can be set', function () {
    SettingsFacade::defaultTeam(1);

    settings()->set('foo', 'bar');

    assertDatabaseHas('settings', [
        'key' => 'foo',
        'team_id' => 1,
    ]);
});

test('default team can be scoped', function () {
    SettingsFacade::defaultTeam(2);

    SettingsFacade::defaultTeam(1, function () {
        settings()->set('team', 'team 1 value');
    });

    settings()->set('team', 'team 2 value');

    assertDatabaseCount('settings', 2);

    assertDatabaseHas('settings', [
        'key' => 'team',
        'value' => json_encode('team 1 value'),
        'team_id' => 1,
    ]);

    assertDatabaseHas('settings', [
        'key' => 'team',
        'value' => json_encode('team 2 value'),
        'team_id' => 2,
    ]);
});

test('a team can be set temporarily', function () {
    SettingsFacade::usingTeam(1)->set('team-name', 'Team 1 Name');
    SettingsFacade::usingTeam(2)->set('team-name', 'Team 2 Name');
    SettingsFacade::set('team-name', 'Global Name');

    assertDatabaseCount('settings', 3);

    assertDatabaseHas('settings', [
        'key' => 'team-name',
        'value' => json_encode('Global Name'),
        'team_id' => null,
    ]);

    assertDatabaseHas('settings', [
        'key' => 'team-name',
        'value' => json_encode('Team 1 Name'),
        'team_id' => 1,
    ]);

    assertDatabaseHas('settings', [
        'key' => 'team-name',
        'value' => json_encode('Team 2 Name'),
        'team_id' => 2,
    ]);
});

test('a temporarily set team overrides the default team', function () {
    SettingsFacade::defaultTeam(1);

    SettingsFacade::usingTeam(2)->set('team-name', 'Team 2 Name');
    SettingsFacade::set('team-name', 'Team 1 Name');

    assertDatabaseHas('settings', [
        'key' => 'team-name',
        'value' => json_encode('Team 1 Name'),
        'team_id' => 1,
    ]);

    assertDatabaseHas('settings', [
        'key' => 'team-name',
        'value' => json_encode('Team 2 Name'),
        'team_id' => 2,
    ]);
});

test('noTeam() can be used to set the team to null for the setting', function () {
    SettingsFacade::defaultTeam(1);

    SettingsFacade::noTeam()->set('foo', 'bar');

    assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => json_encode('bar'),
        'team_id' => null,
    ]);
});

test('usingTeam() can be scoped', function () {
    SettingsFacade::usingTeam(3, function () {
        settings()->set('foo', 'bar');
    });

    settings()->set('foo', 'bar');

    assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => json_encode('bar'),
        'team_id' => 3,
    ]);

    assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => json_encode('bar'),
        'team_id' => null,
    ]);
});

test('noTeam() can be scoped', function () {
    SettingsFacade::noTeam(function () {
        settings()->set('foo', 'bar');
    });

    assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => json_encode('bar'),
        'team_id' => null,
    ]);
});

test('usingTeam() accepts a model instance', function () {
    $team = Team::factory()->create();

    SettingsFacade::usingTeam($team)->set('foo', 'bar');

    assertDatabaseHas('settings', [
        'key' => 'foo',
        'value' => json_encode('bar'),
        'team_id' => $team->getKey(),
    ]);
});

it('checks if a team has a setting', function () {
    SettingsFacade::set('foo', 'bar');

    expect(SettingsFacade::usingTeam(1)->has('foo'))->toBeFalse();

    SettingsFacade::usingTeam(1)->set('foo', 'bar');

    expect(SettingsFacade::usingTeam(1)->has('foo'))->toBeTrue();
});

test('the cache is scoped for teams', function () {
    $this->storeSetting('my-setting', 'team 1', 1);
    $this->storeSetting('my-setting', 'team 2', 2);

    SettingsFacade::enableCache();

    $team1Value = SettingsFacade::usingTeam(1)->get('my-setting');
    $team2Value = SettingsFacade::usingTeam(2)->get('my-setting');

    expect($team1Value)->toBe('team 1')
        ->and($team2Value)->toBe('team 2');
});

test("all of a team's settings can be retrieved at once", function () {
    $team = Team::first();

    settings()->set('one', 'non-team value');
    settings()->context(new Context(['id' => 'foo']))->set('one', 'non-team value context 1');

    settings()->usingTeam($team, function () {
        settings()->set('one', 'team value');
        settings()->set('two', 'team value 2');
        settings()->context(new Context(['id' => 'foo']))->set('one', 'team value context 1');
    });

    $storedSettings = settings()->usingTeam($team)->all();

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
    $nonContextSettings = settings()->usingTeam($team)->context(false)->all();

    expect($nonContextSettings)->toHaveCount(2)
        ->and($nonContextSettings->pluck('value'))->not->toContain('team value context 1');
});

test("a team's settings can be flushed", function () {
    $team = Team::first();

    settings()->set('one', 'non-team value');

    settings()->defaultTeam($team);
    settings()->set('one', 'team value');

    assertDatabaseCount('settings', 2);

    settings()->flush();

    assertDatabaseCount('settings', 1);

    assertDatabaseMissing('settings', [
        'team_id' => $team->getKey(),
    ]);
});
