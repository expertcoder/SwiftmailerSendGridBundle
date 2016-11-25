# SwiftmailerSendGridBundle

Symfony 3 (version 2 not tested) bundle for SendGrid. Utilizes the SendGrid PHP Library https://github.com/sendgrid/sendgrid-php 
to make it compatiable with SwiftMailer.

##Installation Example

parameters.yml.dist
```
parameters:
    sendgrid_api_key: PleaseEnterSendGridApiKey
```

config.yml
```
swiftmailer:
    transport: sendgrid
    
expert_coder_swiftmailer_send_grid:
    api_key: %sendgrid_api_key%    
```

AppKernel.php
```
$bundles = [
    .......
    new ExpertCoder\Swiftmailer\SendGridBundle\ExpertCoderSwiftmailerSendGridBundle(),
];
```

composer.json
```
"require": {
    .....
    "expertcoder/symapi-security-bundle": "@dev"
}

```