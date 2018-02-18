# SwiftmailerSendGridBundle

[![Latest Version](https://img.shields.io/github/release/expertcoder/SwiftmailerSendGridBundle.svg?style=flat-square)](https://github.com/expertcoder/SwiftmailerSendGridBundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/expertcoder/SwiftmailerSendGridBundle.svg?style=flat-square)](https://travis-ci.org/expertcoder/SwiftmailerSendGridBundle)

Symfony 2/3 bundle for SendGrid. Utilizes the SendGrid PHP Library https://github.com/sendgrid/sendgrid-php 
to make it compatiable with SwiftMailer.

## Installation Example

`composer require expertcoder/swiftmailer-send-grid-bundle`

**or**

*composer.json*
```json
"require": {
    ...
    "expertcoder/swiftmailer-send-grid-bundle": "~1.0"
}

```

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
    transport: expertcoder_swift_mailer.send_grid.transport
    
expert_coder_swiftmailer_send_grid:
    api_key: %sendgrid_api_key%
    categories: [my_category] # optional, will be added to all mails sent
```
#### Symfony 3

Since Symfony 3.2, you must name the custom transport service swiftmailer.mailer.transport.< name >

*services.yml*
```yml
services:
    swiftmailer.mailer.transport.expertcoder_swift_mailer.send_grid.transport:
      alias: expertcoder_swift_mailer.send_grid.transport
```
*config.yml*
```yml
imports:
    - { resource: services.yml }
```
## Important !

Following RFC 1341, section 7.2, if either `text/html` or `text/plain` are to be sent in your email: `text/plain` needs to be first, followed by `text/html`, followed by any other content.


For more informations, please see [SwiftMailer](https://swiftmailer.symfony.com/docs/messages.html#quick-reference) and [RFC 1341](https://www.w3.org/Protocols/rfc1341/7_2_Multipart.html)
