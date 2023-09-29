---
title: Custom Generators
sort: 3
---

## Introduction

Settings ships with a few key generators and value serializers out-of-the-box, however you may wish to implement your own. You can easily do so by following
the steps listed below for each type.

## KeyGenerator

A key generator is responsible for generating a key suitable for storage on the `key` column of a setting. By default, the package uses the `Md5KeyGenerator` class,
which generates an md5 hash of a given key/context combination. This key generator is only default, however, to prevent a breaking change from
upgrading from v2 of this package. We recommend using the `ReadableKeyGenerator` class instead, which will generate a key that is both readable for the key,
and allows for searching for the key by context possible.

> {note} The `ReadableKeyGenerator` key generator (or a custom one) is required for using the `all` and `flush` methods on the `Settings` facade, as well as flushing
> a model's settings when it is deleted.

If you'd like to use your own KeyGenerator, you may do so by implementing the `Rawilk\Settings\Contracts\KeyGenerator` interface. Here is what the interface looks like:

Here's what a custom key generator might look like:

```php
use Rawilk\Settings\Contracts\KeyGenerator;
use Rawilk\Settings\Contracts\ContextSerializer;
use Rawilk\Settings\Support\Context;
use Illuminate\Support\Str;

class CustomKeyGenerator implements KeyGenerator
{
    protected ContextSerializer $contextSerializer;
    
    public function generate(string $key, Context $context = null): string
    {
        $key = strtoupper($key);
        
        if ($context) {
            $key .= $this->contextPrefix() . $this->serializer->serialize($context);
        }
        
        return $key;    
    }
    
    public function removeContextFromKey(string $key): string
    {
        return Str::before($key, $this->contextPrefix());
    }
    
    public function setContextSerializer(ContextSerializer $serializer): self
    {
        $this->serializer = $serializer;
        
        return $this;
    } 
    
    /**
     * This prefix is how we will determine that a database record has a context when
     * flushing/retrieving all settings from the setting drivers.
     */
    public function contextPrefix(): string
    {
        return '|context|';
    }
}
```

Notice that the class requires a `ContextSerializer` object to be passed into a setter. This kind of generator is responsible for converting the context object into a string
suitable for storage. See [ContextSerializer](#user-content-contextserializer) for more information.

After defining your class, you need to add it the settings config file:

```php
// config/settings.php
'key_generator' => CustomKeyGenerator::class,
```

## ContextSerializer

The context serializer is responsible for taking a `Rawilk\Settings\Support\Context` object and converting it into a string suitable for storage. By default, the package will use
the `ContextSerializer` class, which will use php's `serialize` method to convert the context into a string. If you're using the `ReadableKeyGenerator`, or a custom one of your own,
we recommend using the `DotNotationContextSerializer` class instead, which doesn't rely on php's `serialize` method. You may also make your own context serializer by implementing the
`Rawilk\Settings\Contracts\ContextSerializer` interface. Here is what a custom context serializer might look like:

```php
use Rawilk\Settings\Contracts\ContextSerializer;
use Rawilk\Settings\Support\Context;

class CustomContextSerializer implements ContextSerializer
{
    public function serialize(Context $context = null): string
    {
        if (is_null($context)) {
            return '';
        }        
        
        return json_encode($context->toArray());
    }
}
```

After defining your class, you need to add it the settings config file:

```php
// config/settings.php
'context_serializer' => CustomContextSerializer::class,
```

## ValueSerializer

The value serializer is responsible for preparing a value for storage. By default, the package uses the `ValueSerializer` class, which will use php's `serialize` method to convert
the value into a string, and then `unserialize` to return the original value. You can alternatively use the `JsonValueSerializer` class, which will use php's `json_encode` and `json_decode`
instead.

You are also free to create your own value serializer by implementing the `Rawilk\Settings\Contracts\ValueSerializer` interface. Here is what a custom value serializer might look like:

```php
use Rawilk\Settings\Contracts\ValueSerializer;

class CustomValueSerializer implements ValueSerializer
{
    public function serialize($value): string
    {
        return json_encode($value);
    }
    
    public function unserialize(string $serialized): mixed
    {
        return json_decode($serialized, true);
    }
}
```

After defining your class, you need to add it the settings config file:

```php
// config/settings.php
'value_serializer' => CustomValueSerializer::class,
```
