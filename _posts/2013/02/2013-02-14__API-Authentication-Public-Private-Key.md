---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: API Authentication: Public/Private Keys
tags: api,authentication,publickey,privatekey
summary: TBD
---

API Authentication: Public/Private Keys
--------------

{{ byline }}

> This is the first part of a series of posts on authentication methods you can
> use with your APIs. This article will cover the use of public/private key pairings
> to validate the request.

`
<?php
$publicKey = '';
$privateKey = '';
$content = json_encode(array(
    'test' => 'content'
));

$hash = hash_hmac('sha256', $content, $privateKey);

$headers = array(
    'X-Public: '.$publicKey,
    'X-Hash: '.$hash
);

$ch = curl_init('http://api.mycoolsite.com');
curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

$result = curl_exec($ch);

curl_close($ch);

var_dump($result);
?>
`

example with Slim

`
{
    "require": {
        "slim/slim":"2.*"
    }
}
`

`
<?php
$app = new \Slim\Slim();

$app->get('/', function() use ($app) {
    $request = $app->request();
    $userHash = $request->headers('X-Public');
    $contentHash = $request->headers('X-Hash');

    $user = User::find_by_hash($hash);

    $user->privateKey;
});
?>
`