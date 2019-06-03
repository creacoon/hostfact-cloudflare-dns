# Hostfact module for Cloudflare DNS management

[![StyleCI](https://styleci.io/repos/189969610/shield?branch=master)](https://styleci.io/repos/189969610)
[![Quality Score](https://img.shields.io/scrutinizer/g/creacoon/hostfact-cloudflare-dns.svg?style=flat-square)](https://scrutinizer-ci.com/g/creacoon/hostfact-cloudflare-dns)

This module allows you to manage Cloudflare DNS records from HostFact.

## Installation

Copy the `cloudflare` folder to your HostFact installation at `Pro/3rdparty/modules/dns/dnsmanagement/integrations/`.
Go to the modules overview in Hostfact and make sure you fill in all settings.

## Usage

Add a nameserver group that matches you Cloudflare nameservers under `DNS Management` in HostFact.
Open a domain using these nameservers to edit the records.

### Testing

Copy `TestVariables.php.dist` to `TestVariables.php` and fill in your credentials.

``` bash
php test.php
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email support@creacoon.nl instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Creacoon, Pasweg 11, 6097NJ Heel, The Netherlands.

## Credits

- [Tom Coonen](https://github.com/tomcoonen)
- [All Contributors](../../contributors)

## Support us

[Creacoon](https://spatie.be/opensource) is a software company based in The Netherlands.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
