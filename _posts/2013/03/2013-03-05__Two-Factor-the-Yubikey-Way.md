---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Two-Factor the Yubikey Way
tags: twofactor,yubikey,api
summary: The Yubikey USB hardware token makes two-factor authentication as easy as pushing a button.
---

Two-Factor the Yubikey Way
--------------

{{ byline }}

There's lots of options when it comes to integrating two-factor authentication with 
your application's authentication process. There's everything from the [Google Authenticator](/2013/01/11/Googles-Two-Factor-Auth-Online-Offline.html), a free to use tool that doesn't even require a network connection 
out to something like [Duo Security](/2013/01/09/Two-Factor-Auth-Integration-with-Duo-Security.html)
that offers not only TFA integration on a web application level but also for lots of other
environments (like server authentication and VPN). There's another option that falls somewhere
in between the "free" of Google Authenticator and the commercial Duo Security, though. The 
[YubiKey](http://www.yubico.com/products/yubikey-hardware/yubikey/) is a hardware device 
sold by Yubico that generates a randomized code that can be authenticated via a call to their API.
The "key" is a USB device you can put on a keyring or carry with you easily and is as simple
to use as pushing a button:

<img src="/assets/img/yubikey.jpg" height="120" width="120" alt="Image courtsey of Yubico website"/>

Here's how it works - when you order your key (the base model is about $25 USD) they configure it 
so that every time you put it into your USB slot and touch that gold button, it generates a
unique one-time password. One of the best things about the key too is that most computers will
just recognize it as a "keyboard" and accept the input directly from it. This means that all 
you need to do to fill in a field is to click into it and tap the button. Pretty simple, right?

#### Validating the code

The next logical step is to want to be able to authenticate the codes you get from the Yubikeys
of your users. Thankfully, the Yubico API makes it simple. There's only really one endpoint, 
the `verify` request, and submitting the code is a single `GET` request away.

You will, however, need to do one thing before you can use their API...and that requires a 
Yubikey to access. Once you have your key, you'll need to go over to [their API signup page](https://upgrade.yubico.com/getapikey/) and enter an email address and use your key to create a `client ID` and 
`API key`. The `client ID` is a number that's used by the API to locate your API key to 
validate the signature on your request. The `API key` is a base64 encoded value that you'll
need to pass along with the request for validation purposes.

Once you have these two pieces of information you can start making requests. Be sure keep 
them safe as you can't get them back once you're left that page. If you lose them or forget
them, you'll have to regenerate new ones, expiring the older set.

I'm going to show you an example of a request to their API using the [Guzzle](http://guzzlephp.org)
HTTP client for PHP. If you're a Composer user, you can get it by putting this in your
`composer.json`:

`
{
    "require": { "guzzle/guzzle": "3.0.*@dev" }
}
`

Run a `php composer.phar install` and it will download the latest version and get it set up.
The `require_once` in the example script below assumes the use of a Composer setup. Let's 
look at the code first, then I'll come back and explain what it's doing:

`
<?php

require_once 'vendor/autoload.php';

class Validate
{
    private $host = 'https://api2.yubico.com';

    public function check($otp, $apiKey, $clientId)
    {
        $client = new \Guzzle\Http\Client();

        $nonce = md5(mt_rand());

        $params = array(
            'id' => $clientId,
            'otp' => trim($otp),
            'nonce' => $nonce,
            'timestamp' => '1'
        );
        ksort($params);
        $signature = preg_replace(
            '/\+/', '%2B', 
            base64_encode(hash_hmac('sha1', http_build_query($params), $apiKey, true))
        );

        $url = '/wsapi/2.0/verify?'.http_build_query($params).'&h='.$signature;

        $request = $client->get($url);
        $response = $request->send();

        return (strpos($response->getBody(true), 'status=OK')) ? true : false;
    }
}
?>
`

This `Validate` class is a simple one-method example showing a `verify` request being made
to their `api2.yubico.com` API server (they have five of them). Here's the steps of the request:

1. The new `Guzzle` HTTP client is created
2. A "nonce" (unique key used to identify the request) is randomly generated
3. The parameters for the request are setup in `$params` and sorted by key (required by the API)
4. This array of data is then made into a URL encoded string via `http_build_query` 
5. A "signature" is made for the request by HMAC SHA1 hashing the string with the API key as the key
6. All of the values are then appended to the URL and the `get` and `send` methods are called to make the request

The response that comes back will look something like this:

`
h=ZQcVu5xEo9Pp3/PPp3T4SBRIY6g=
t=2013-03-02T22:30:06Z0516
otp=ccccccbjnrirvueblhvurldblghiuihtnifktjllndrb
nonce=4076bc6db18d9727a4add255bb634b68
sl=25
timestamp=3214002
sessioncounter=10
sessionuse=26
status=OK
`

There's a lot of data here, but what you're really looking for is that last line, the 
`status=OK`. If you see that in the response, your validation call was successful and they've
entered a key that actually came from a real Yubikey. Some of the other values are also
useful for validation purposes too - you can compare the response values for `otp`, `nonce` and
`h` (the signature you generated for the request). You can find out more about the data
in the response and other possible input parameters on their [Google Code wiki](https://code.google.com/p/yubikey-val-server-php/wiki/GettingStartedWritingClients). 

One thing to note, the examples they provide show that you **do not** have to generate the 
signature and append it to the request, but this should *always* be done as it helps ensure
the integretiy of the message.

#### A Yubikey library

In the other [two-factor authentication articles](/tagged/twofactor), I've provided either a 
link or an example of a simple script you can drop in and use in your application to implement
that particular flavor of TFA. There' several out there for working with the codes the Yubikey
produces, but ones I've created makes it dead easy to drop in and go (and can be installed via
Composer). To install it, you can put this in your `composer.json`:

`
{
    "require": { "enygma/yubikey": "dev-master" }
}
`

This will pull in the [Yubikey library](https://github.com/enygma/yubikey.git) that lets you
just use a few lines to authenticate the codes. Here's an example of its use:

`
<?php
require_once 'vendor/autoload.php';

$apiKey = 'dGVzdGluZzEyMzQ1Njc4OTA=';
$clientId = '12345';
$usercode = 'code-from-the-user';

$v = new \Yubikey\Validate($apiKey, $clientId);
$response = $v->check($userCode);
echo ($response->success()) ? 'Good to go!' : 'Fail.';
?>
`

You just pass in the `API key` and `client ID` when the object is created and it handles
the rest for you. It will generate the request, make the signature, select a random server
from the Yubico API server set and make the `GET` request. The `success()` method that's called
on the `$response` checks a few things:

- that the value of `otp` in the response is the same as what we gave
- that the `nonce` value is the same as the request
- that the `status` is "OK"

All of these criteria have to be met in order for it to be a successful validation. The code
example from earlier in this article shows only a check for the "status=OK" part of the message
and not the other two checks, potentially leaving it open to things like a Man-in-the-Middle
attack. Additionally, the library makes the request over `HTTPS` by default to help ensure the
safety of the contents of the request.

#### Google and the Yubikey

There's one more thing I wanted to add about some recent advancements surrounding Yubikey use. There's been 
some talk recently about Google and its never ending struggles to try to provide the best security to 
the users of its suite of online applications. They made an announcement that they'd be 
[testing out using the key](http://www.digitalspy.co.uk/tech/news/a452882/google-trialling-yubikey-password-free-security.html) to authenticate users for their service. It's part of an initiative to help protect their users 
and the large amounts of data they have with the company. Google previously implemented two-factor 
authentication through a smartphone either using a SMS message containing a six-digit code or one 
from their own Google Authenticator application.

#### Resources

- [Purchase a Yubikey](http://www.yubico.com/products/)
- [Yubico API documentation](https://code.google.com/p/yubikey-val-server-php/wiki/GettingStartedWritingClients)
- [API signup page](https://upgrade.yubico.com/getapikey/)
- [Yubikey PHP library](https://github.com/enygma/yubikey.git)
