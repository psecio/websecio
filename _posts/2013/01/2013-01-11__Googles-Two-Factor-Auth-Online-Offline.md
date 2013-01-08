---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Google's Two-Factor Auth - Online or Offline
tags: twofactor,authentication,authy,api,webservice
summary: Using the Authy REST API, you can quickly and easily integrate two-factor auth into your system.
---

Google's Two-Factor Auth - Online or Offline
--------------

{{ byline }}

`
{
    "require": {
        "enygma/gauth": "dev-master"
    }
}
`

Example:

`
<?php

$g = new \GAuth\Auth();
$code = $g->generateCode();
echo 'Generated code: '.$code;

?>
`


`
<?php

$code = 'code-inputted-by-user';
$g = new \GAuth\Auth();
echo ($g->validateCode($code)) ? 'Validated!' : 'Invalid!';

?>
`