---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Easy Two-Factor Authentication with Authy
tags: twofactor,authentication,authy,api,webservice
summary: Needs a summary
---

Easy Two-Factor Authentication with Authy
--------------

{{ byline }}

One of the recent trends in the security arena is the idea that simple username/password
logins just aren't secure enough. Sure, they're better than nothing, but there's just too 
many problems with them. Here's a few:

- Passwords can be cracked with relatively simple automated tools
- Users tend to use the same password with multiple services (the same username too)
- User account information is easy to socially engineer - humans are often the weakest link in the chain

To help combat some of these issues, there's been a number of different approaches that
have been implemented to help protect user logins and the services they're for. Tools like
[Persona](/2012/10/01/Using-Mozilla-Persona-with-PHP-jQuery.html) or [OAuth](http://oauth.net/)
aim to decentralize the authentication for your application. Unfortunately, they can be 
difficult to implement into a currently exisiting login system. 

There's another alternative that can act as an add-on to your current system and can provide
a "quick and easy" win to help protect your users - **two-factor authetnication**.

#### Two-Factor Auth Defined

The key concept behind most of the two-factor authentication systems you'll see these days
is pretty simple. The user has the normal username and password combination, but another 
form of proof of the identity is also required. One of the most popular methods (and one 
that several services, even Google use) is a combination of a web site and a cell phone-based
application or text message. 

Here's an example of the flow of a process that uses two-factor authentication...in this case
it's Google's method:

1. The user signs up for an account with a username and password as usual
2. The user can then go in and enable two-factor authentication for that login
3. The application asks for the number to use to connect with the user
4. When the user tries to log in from a machine that isn't "known" by the service, a message
is sent to the device with a confirmation code.
5. The user enters this code into the Google site, proving themselves as the correct user

Google has their own internal mechanisms to handle this whole flow, but what happens if you
don't want the overhead of having to worry about all of the technology or steps that happen 
behind the scenes. Is there an alternative?

#### Enter Authy



`
{
    "require": {
        "authy/php": "dev-master",
        "resty/resty": "dev-master"
    }
}
`


`
<?php
require_once 'vendor/autoload.php';

$prod = false;
$apiKey = 'your-key-hash';
$apiUrl = ($prod == true) : 'https://api.authy.com' : 'http://sandbox-api.authy.com';

$api = new Authy_Api($apiKey, $apiUrl);
?>
`

`
<?php
$userEmail = 'sample-user@websec.io';
$userPhone = '214-555-1234';
$userCountryCode = 1;
$user = $api->registerUser($userEmail, $userPhone, $userCountryCode);

if ($user->ok()) {
    echo 'Authy ID for user "'.$userEmail.'": '.$user->id()."\n";
} else {
    foreach ($user->errors() as $field => $error){
        echo 'Error on '.$field.': '.$error."<br/>";
    }
}
?>
`

`
<?php
$token = 'token-input-from-user';
$userAuthyId = 12345;

// verify the token
$verify = $api->verifyToken($userAuthyId,$token);
if ($verify->ok() === true) {
    echo 'Verified!';
} else {
    foreach ($verify->errors() as $field => $error){
        echo 'Error on '.$field.': '.$error."<br/>";
    }
}
?>
`

