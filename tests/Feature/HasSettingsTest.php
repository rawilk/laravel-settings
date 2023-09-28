<?php

declare(strict_types=1);

use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializers\DotNotationContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\ReadableKeyGenerator;
use Rawilk\Settings\Tests\Support\Models\Company;
use Rawilk\Settings\Tests\Support\Models\CustomUser;
use Rawilk\Settings\Tests\Support\Models\User;

beforeEach(function () {
    migrateTestTables();

    User::factory(2)->create();
});

test('a model can have its own custom context', function () {
    $user1 = User::first();
    $user2 = User::where('id', '>', $user1->getKey())->first();

    $expectedUser1Context = new Context([
        'model' => $user1::class,
        'id' => $user1->getKey(),
    ]);
    $actualUser1Context = $user1->context();
    expect($actualUser1Context)->toBeInstanceOf(Context::class)
        ->and($actualUser1Context->has('model'))->toBeTrue()
        ->and($actualUser1Context->has('id'))->toBeTrue()
        ->and($actualUser1Context->get('model'))->toBe($expectedUser1Context->get('model'))
        ->and($actualUser1Context->get('id'))->toBe($expectedUser1Context->get('id'));

    $user2Context = $user2->context();
    expect($user2Context->get('id'))->not()->toBe($actualUser1Context->get('id'))
        ->and($user2Context->get('id'))->toBe($user2->getKey());
});

test('a model can add its own custom properties to its context', function () {
    // Custom user adds in an "email" key in the "contextArguments" method.
    $user = CustomUser::first();
    $context = $user->context();

    expect($context->has('email'))->toBeTrue()
        ->and($context->get('email'))->toBe($user->email)
        ->and($context->has('id'))->toBeTrue()
        ->and($context->get('id'))->toBe($user->getKey());
});

test('models can have their own settings', function () {
    $user1 = User::first();
    $user2 = User::where('id', '>', $user1->getKey())->first();
    $company = Company::factory()->create();

    $company->settings()->set('program.name', $company->name);
    $context = new Context([
        'model' => $company::class,
        'id' => $company->getKey(),
    ]);

    expect(Settings::context($context)->get('program.name'))->toBe($company->name)
        ->and($user1->settings()->has('program.name'))->toBeFalse();

    $user1->settings()->set('program.name', $user1->email);
    $user2->settings()->set('program.name', $user2->email);

    expect($user1->settings()->has('program.name'))->toBeTrue();

    $user1Context = new Context([
        'model' => $user1::class,
        'id' => $user1->getKey(),
    ]);
    $user2Context = new Context([
        'model' => $user2::class,
        'id' => $user2->getKey(),
    ]);

    expect(Settings::context($user1Context)->get('program.name'))->toBe($user1->email)
        ->and(Settings::context($user2Context)->get('program.name'))->toBe($user2->email);
});

test("a model's settings are flushed when the model is deleted", function () {
    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $user1 = User::first();
    $user2 = User::where('id', '>', $user1->getKey())->first();

    Settings::set('user.email', 'foo');

    $user1->settings()->set('user.email', $user1->email);
    $user2->settings()->set('user.email', $user2->email);

    $this->assertDatabaseCount('settings', 3);

    $user1->delete();

    $this->assertDatabaseCount('settings', 2);

    $this->assertDatabaseMissing('settings', [
        'key' => 'user.email:c:::model:user::id:' . $user1->getKey(),
    ]);
});

test('a model can be configured to not flush settings on delete', function () {
    $settings = settings();
    (fn () => $this->keyGenerator = (new ReadableKeyGenerator)->setContextSerializer(new DotNotationContextSerializer))->call($settings);

    $company = Company::factory()->create();
    $company->settings()->set('foo', 'bar');

    $this->assertDatabaseCount('settings', 1);

    $company->delete();

    $this->assertDatabaseCount('settings', 1);
});

test("a model's settings will not be flushed if the md5 key generator is being used", function () {
    $user = User::first();

    $user->settings()->set('foo', 'bar');

    $this->assertDatabaseCount('settings', 1);

    $user->delete();

    $this->assertDatabaseCount('settings', 1);
});
