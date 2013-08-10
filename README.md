# Roundcube - Bruteforce Breaker

This plugin will ban IP address for X minutes after several consecutive failed login attemps.

When an IP is banned, RoundCube will return invalid credentials.

## Requirements

There is no dependency. This plugin was developped for Roundcube 0.9.0+ but it might be compatible with earlier versions.

## Installation

 * Upload the plugin in your RoundCube installation into `/plugins/`.
 * Rename the plugin folder into `bruteforcebreaker`.
 * Add the plugin in your plugins array in `/config/main.inc.php` :
 
```php
$rcmail_config['plugins'] = array('bruteforcebreaker');
```
 * It's already working ! :)

## Configuration

You can override default settings by copying `config.inc.php.dist` into `config.inc.php`.

 * keep_trace: Log login attemps.
 * nb_attemps: Number of login attemps before ban
 * duration: Ban duration in seconds
 * whitelist: An array of whitelist IP (they can't be banned)

## Changelog

### 1.1

Whitelist added in config file.

### 1.0

Initial commit.

## License

This software is distributed under [the MIT license](http://git.hoa.ro/arthur/rc-plugin-bruteforce-breaker/blob/master/LICENSE.md) by Arthur Hoaro.

Thanks to [SebSauvage](https://github.com/sebsauvage/) for the inspiration.