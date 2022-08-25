<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://mailcarrier.app/images/logos/logo-light.png">
        <img src="https://mailcarrier.app/images/logos/logo-dark.png" alt="MailCarrier logo" width="400">
    </picture>
</p>

<h2 align="center">User friendly, provider-aware, mailing platform with templates and logs included.</h2>


Design global layouts, compose your template, preview your emails and send them with your desired provider (SES, MailGun etc.) through intuitive and friendly API endpoint protected by your desired auth guard. Then, inspect them through logs.  

### Features

- 🎨 **Beautiful syntax**: Explore a beautiful, expressive template syntax similar to JS thanks to [Twig by Symfony](https://twig.symfony.com).
- 🧩 **Provider aware**: Bring your desired provider to send email, such as Amazon SES, MailGun, SendGrid etc.  
- ✨ **Friendly APIs**: Use a friendly and well documented API endpoint to send your emails.
- 🔐 **Secure by default**: Both authentication and API endpoint are always secure: use one of the pre-built auth system or bring your own.
- 📎  **Attachments**: Upload or retrieve attachments from a remote source such *S3*, *Spaces* etc.
- 🪄 **Hackable**: MailCarrier relies on [Laravel](https://laravel.com/) and [Filament](https://filamentphp.com/), that means that over 30K packages are available to customise your MailCarrier instance.
- ⏳ **Queues**: You can choose whether or not to send emails in a enqueued, background jobs, to not block the user experience.  

## Installation

Please follow https://mailcarrier.app/docs/getting-started/installation.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/mailcarrierapp/.github/blob/master/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Danilo Polani](https://github.com/danilopolani)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
