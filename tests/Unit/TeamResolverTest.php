<?php

declare(strict_types=1);

use Rawilk\Settings\Support\TeamResolver;
use Rawilk\Settings\Tests\Support\Models\Team;

it('will resolve the provided override callback', function () {
    app(TeamResolver::class)->resolveUsing(fn () => 'foo');

    $team = app(TeamResolver::class)->resolve();

    expect($team)->toBe('foo');
});

it('will scope a team with withTeam()', function () {
    $team = Team::factory()->create();
    $resolver = app(TeamResolver::class);

    $result = $resolver->withTeam($team, function () use ($resolver) {
        return $resolver->resolve();
    });

    expect($result)->toBe($team->getKey());
});

it('restores the previous team after withTeam()', function () {
    [$team1, $team2] = Team::factory()->count(2)->create();
    $resolver = app(TeamResolver::class);

    $resolver->setDefaultTeam($team1);

    $resolver->withTeam($team2, function () use ($resolver, $team2) {
        expect($resolver->resolve())->toBe($team2->getKey());
    });

    expect($resolver->resolve())->toBe($team1->getKey());
});

it('restores the previous team after withTeam() even when an exception is thrown', function () {
    [$team1, $team2] = Team::factory()->count(2)->create();
    $resolver = app(TeamResolver::class);

    $resolver->setDefaultTeam($team1);

    try {
        $resolver->withTeam($team2, function () {
            throw new RuntimeException('test');
        });
    } catch (Throwable) {
    }

    expect($resolver->resolve())->toBe($team1->getKey());
});

test('setTeam() takes priority over resolveUsing()', function () {
    $resolver = app(TeamResolver::class);

    $resolver->resolveUsing(fn () => 1);
    $resolver->setTeam(2);

    expect($resolver->resolve())->toBe(2);
});

test('setTeam() takes priority over default team', function () {
    $resolver = app(TeamResolver::class);
    $resolver->setDefaultTeam(1);
    $resolver->setTeam(2);

    expect($resolver->resolve())->toBe(2);
});
