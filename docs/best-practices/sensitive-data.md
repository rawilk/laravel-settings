---
title: Sensitive Data
sort: 3
---

If your application needs to store any kind of sensitive data in the database for a setting, you should enable encryption for settings in `config/settings.php`.
Encryption is enabled by default, but you can always disable encryption if you don't need to store any sensitive settings.

You can also just use the default config system and reference the sensitive data via the `env()` helper in your config files if you don't need to store these
settings in the database.
