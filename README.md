![Freelance Banner](https://s3.eu-central-1.amazonaws.com/static.expertcoder.io/github-banner/banner.png)

# SwiftmailerSendGridBundle

[![Latest Version](https://img.shields.io/github/release/expertcoder/SwiftmailerSendGridBundle.svg?style=flat-square)](https://github.com/expertcoder/SwiftmailerSendGridBundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/expertcoder/SwiftmailerSendGridBundle.svg?style=flat-square)](https://travis-ci.org/expertcoder/SwiftmailerSendGridBundle)

Symfony bundle for SendGrid. Utilizes the SendGrid PHP Library https://github.com/sendgrid/sendgrid-php 
to make it compatiable with SwiftMailer.

**Older version (1.x) can be found here:** https://github.com/expertcoder/SwiftmailerSendGridBundle/tree/1.x

## Installation

`composer require expertcoder/swiftmailer-send-grid-bundle`

**or manually**

*composer.json*
```json
"require": {
    ...
    "expertcoder/swiftmailer-send-grid-bundle": "~2.0"
}

```

*config/packages/swiftmailer.yaml*
```yml
swiftmailer:
    transport: expertcoder_swift_mailer.send_grid
```

Don't forget to set your Sendgrid API Key in your *.env* file, and that you can set your mail's categories from `config/packages/expert_coder_swiftmailer_send_grid.yaml`

Applications that don't use Symfony Flex
----------------------------------------

*AppKernel.php*
```php
$bundles = [
    // ...
    new ExpertCoder\Swiftmailer\SendGridBundle\ExpertCoderSwiftmailerSendGridBundle(),
];
```

*parameters.yml.dist*
```yml
parameters:
    sendgrid_api_key: PleaseEnterSendGridApiKey
```

*config.yml*
```yml
swiftmailer:
    transport: expertcoder_swift_mailer.send_grid
    
expert_coder_swiftmailer_send_grid:
    api_key: %sendgrid_api_key%
    categories: [my_category] # optional, will be added to all mails sent (can be seen on sendgrid dashboard)
```
Symfony 3
---------

Since Symfony 3.2, you must name the custom transport service swiftmailer.mailer.transport.< name > so you can use one of the solutions below:

*services.yml*
```yml
services:
    swiftmailer.mailer.transport.expertcoder_swift_mailer.send_grid.transport:
      alias: expertcoder_swift_mailer.send_grid.transport
```
**OR**

*config.yml*
```yml
swiftmailer:
    transport: 'swiftmailer.mailer.transport.expertcoder_swift_mailer.send_grid'
```

## Important !

Following RFC 1341, section 7.2, if either `text/html` or `text/plain` are to be sent in your email: `text/plain` needs to be first, followed by `text/html`, followed by any other content.


For more informations, please see [SwiftMailer](https://swiftmailer.symfony.com/docs/messages.html#quick-reference) and [RFC 1341](https://www.w3.org/Protocols/rfc1341/7_2_Multipart.html)
