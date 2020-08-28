---
title: Settings Helper
sort: 3
---

If you prefer to use a helper function, you can use the `settings()` helper function. If you pass nothing in to the function,
it will return an instance of `Rawilk\Settings\Settings`, which you can then call any of its methods on as if you were using the
`Settings` facade.

```php
settings(); // Rawilk\Settings\Settings

settings()->context($context)->forget('foo');
```

## Retrieving values
Passing in a key as your first argument will return a persisted setting value for that key. You can pass a default value in
as the second argument and that will be returned if the setting is not persisted. If you need context, you can pass that in as
the third argument.

Of course, you can also just pass nothing in and chain the get method call on too.

```php
settings('foo'); // 'bar'
settings('not persisted', 'my default'); // 'my default'
settings('foo', 'default value', new Context(['user_id' => 1]); // user 1 value returned

// Via method chaining
settings()->get('foo');
settings()->context($context)->get('foo');
```

## Storing values
You can store values by passing in an array of key/value pairs as the first argument. If you need context, you can pass that in
as the third argument (pass in `null` as the second argument as it is ignored anyways in this case).

```php
settings(['foo' => 'bar']);
settings(['foo' => 'bar'], null, new Context(['user_id' => 1]);

// Via method chaining
settings()->set('foo', 'bar');
settings()->context($context)->set('foo', 'bar');
```
