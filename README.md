# Postman [![Build Status](https://travis-ci.org/k1LoW/Postman.svg?branch=master)](https://travis-ci.org/k1LoW/Postman)

## Censorship mode

```php
Configure::write('Postman.censorship.mode', true);
Configure::write('Postman.censorship.config', array(
    'transport' => 'Smtp',
    'from' => array('form@mailback.me' => 'from'),
    'to' => 'to+postman@fusic.co.jp',
    'host' => 'mail.mailback.me',
    'port' => 25,
    'timeout' => 30,
    'log' => false,
    'charset' => 'utf-8',
    'headerCharset' => 'utf-8',
));
```
