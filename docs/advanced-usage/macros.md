---
title: Macros
sort: 3
---

`Rawilk\Settings\Settings` is Macroable, so you can add any custom functionality you want to the class. The
best place to do so would be in a service provider.

<x-code lang="php">
use Rawilk\Settings\Settings;

Settings::macro('getWithSuffix', function ($key, $suffix) {
    // Inside this closure you can call any method available on `Settings`.
    $value = $this->get($key);

    return $value . '_' . $suffix;
});
</x-code>

Using the macro:

<x-code lang="php">
use Rawilk\Settings\Facades\Settings;

Settings::set('foo', 'bar');

Settings::getWithSuffix('foo', 'some_suffix'); // 'bar_some_suffix'

// Or
settings()->getWithSuffix('foo', 'some_suffix');
</x-code>
