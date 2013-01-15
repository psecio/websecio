---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Encruption with Zend\Crypt
tags: encrption,bcrypt,password,hash
summary: 
---

Encryption with Zend\Crypt
--------------

{{ byline }}

test

`
<?php
require_once 'vendor/autoload.php';

use Zend\Crypt\Password\Bcrypt;

$bcrypt   = new Bcrypt();
$password = $bcrypt->create($input);
?>
`

#### Resources

- [Zend\Crypt Documentation](http://framework.zend.com/apidoc/2.0/namespaces/Zend.Crypt.html)
- [Cryptography made easy with Zend Framework](http://www.zimuel.it/en/english-cryptography-made-easy-with-zend-framework/)
