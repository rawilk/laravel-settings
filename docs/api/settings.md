---
title: Settings
sort: 1
---

### context
<x-code lang="php">
/**
 * Set the context for the current operation.
 * Omit or set $context to null to remove context.
 *
 * @param \Rawilk\Settings\Support\Context|null $context
 * @return \Rawilk\Settings\Settings
 */
public function context(Context $context = null): self
</x-code>

### forget
<x-code lang="php">
/**
 * Remove a persisted setting from storage.
 *
 * @param string $key
 * @return void
 */
public function forget($key)
</x-code>

### get
<x-code lang="php">
/**
 * Retrieve a setting from storage.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
public function get(string $key, $default = null)
</x-code>

### has
<x-code lang="php">
/**
 * Determine if a setting has been persisted to storage.
 *
 * @param string $key
 * @return bool
 */
public function has($key): bool
</x-code>

### set
<x-code lang="php">
/**
 * Persist a setting to storage.
 * Updates already persisted settings.
 *
 * @param string $key
 * @param mixed $value
 * @return void
 */
public function set(string $key, $value = null)
</x-code>

### isFalse
<x-code lang="php">
/**
 * Determine if a setting is set to a false value.
 * Returns true if the value is false, '0', or 0.
 *
 * @param string $key
 * @param bool|int|string $default
 * @return bool
 */
public function isFalse(string $key, $default = false): bool
</x-code>

### isTrue
<x-code lang="php">
/**
 * Determine if a setting is set to a truthy value.
 * Returns true if the value is true, '1', or 1.
 *
 * @param string $key
 * @param bool|int|string $default
 * @return bool
 */
public function isTrue(string $key, $default = true): bool
</x-code>
