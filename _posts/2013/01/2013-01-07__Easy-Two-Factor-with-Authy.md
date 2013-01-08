---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Easy Two-Factor Authentication with Authy
tags: twofactor,authentication,authy,api,webservice
summary: Using the Authy REST API, you can quickly and easily integrate two-factor auth into your system.
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
difficult to implement into a currently existing login system. 

There's another alternative that can act as an add-on to your current system and can provide
a "quick and easy" win to help protect your users - **two-factor authentication**.

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

The [Authy](https://www.authy.com/) service has one goal - to take the trouble of having
to handle the whole "send the codes to the user and verify them" process out of your
hands and into their secure, managed environment. They take care of managing the accounts
(linked to yours via an "Authy ID") and doing the sending/verification of the codes. They
even provide a smart phone application for iPhone, Android and Blackberry your users can 
grab keys from anytime they want, online or offline.

To make things even easier, they folks over at Authy have provided a 
[PHP library](https://packagist.org/packages/authy/php) to help with the integration 
with their [REST API](http://docs.authy.com). Here's how to install it via 
[Composer](http://getcomposer.org):

`
{
    "require": {
        "authy/php": "dev-master",
        "resty/resty": "dev-master"
    }
}
`

> **NOTE:** despite it having its own `vendor` directory with `Resty` in it,
> you'll still need to have it in the `composer.json` for now. Otherwise it doesn't 
> find it correctly for the autoloading. `Resty` is a basic REST client from the 
> [FictiveKin](https://github.com/fictivekin) folks. I have a pull request in to 
> fix the "required" field in their `composer.json` but it's not merged yet.

So, now that it's installed, lets see how to use it. First, though, you'll need to go 
[sign up](https://www.authy.com/signup) and create your application. Basically you
just give it a name and it will take you to a page with several pieces of important
information for both a production and testing instance:

- The name of your application (clickable to get your user list)
- The API key hashes to use in the requests
- The plan the account is set up on

If you're just wanting to test it out, you can just use the "Sandbox" version - it's
a free version (perfect for developers trying things out) and lets you have up to one 
thousand users with five hundred authentications per month total. You'll have to set up
your own device to identify yourself as a part of the signup process. If you're interested
in other plans, check out their [pricing page](https://www.authy.com/pricing) for more
information.

Okay, back to the code - with their [PHP library](https://packagist.org/packages/authy/php)
installed and working on your system:

`
<?php
require_once 'vendor/autoload.php';

$prod = false;
$apiKey = 'your-key-hash';
$apiUrl = ($prod == true) : 'https://api.authy.com' : 'http://sandbox-api.authy.com';

$api = new Authy_Api($apiKey, $apiUrl);
?>
`

The code above sets up your API connection. By default it will connect to the **production**
instance of your application, so be sure `$prod` is set to `false` as it is above if 
you want to test against your Testing API.

Now, let's see how to create a user in your system. The Authy API really only needs
a bit of information about your users to get them set up and doesn't care much about
your authentication process. Remember, the goal here is to be a drop-in solution that
enhances what you already have. So, lets create a "sample-user":

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
        echo 'Error on '.$field.': '.$error;
    }
}
?>
`

The Authy system uses the phone number as the unique identification item for the record,
so you can have multiple email addresses associated with one device. When you register 
a new user, they're sent an activation SMS message with information about how to download
the Authy application. 

When they load it up, they'll be asked to identify themselves then they can start getting
their codes immediately. They'll be given a screen similar to this:

![Authy phone application](http://blog.authy.com/assets/posts/phones.png)

There's a countdown below the number that shows how much longer it's good for. Of course,
the user can always choose to recycle it before the time's up, but the default is 20 seconds.

Remember that "Authy ID" I mentioned before? Well, when the user is created successfully, 
you'll get an ID set on the user object. This can be fetched via the `$user->id()` method
call. Don't worry, if you happen to miss it the first time, you can try to recreate the 
same user and their API will give you back the same Authy ID number each time (based on
the device, remember).

You can then store this ID in your database related to the user for later use in the API
verification process.

#### Verifying Codes

Now for the fun part - when a user logs into your system and you see they've enabled 
the two-factor authentication, you can present them with a text field to accept the latest
code from their Authy application. Once they've submitted, you can bounce a request off
the API with the information with both the `$token` and the user's `$authyId`:

`
<?php
$token = 'token-input-from-user';
$authyId = 12345;

// verify the token
$verify = $api->verifyToken($userAuthyId,$token);
if ($verify->ok() === true) {
    echo 'Verified!';
} else {
    foreach ($verify->errors() as $field => $error){
        echo 'Error on '.$field.': '.$error;
    }
}
?>
`

If everything goes well and the code the user gave can be authenticated for their account,
you'll get back a `true` for the `$verify->ok()` call. If not, you can grab the errors
on the `$verify->errors()` and loop through them. They'll look something like:

`
{
    "message": "token is invalid",
    "token": "is invalid"
}
`

#### One "Gotcha" with the Testing API

When I first started using their API, I was hitting the testing version of my application
and wondering why my codes were being denied over and over again, even if I manually 
regenerated them. If eventually found the problem related to this:

> If token is “0000000” a HTTP 200 will always be served when /verify/{TOKEN} API is 
> called, regardless of the id.

The testing API is good for being sure you're sending the right information over, but
it doesn't seem like you can verify actual codes against it. If you need that, you can 
easily just set the `$prod` variable to `true` in the first code example and switch to 
the production API.

That's it, really - that's all there is to integrating with the Authy API. Less than 
thirty lines of code later, you can have an easy to use, drop-in two-factor authentication 
system you can then offer to your users for their added security.

#### Resources

- [Two-factor auth on Wikipedia](http://en.wikipedia.org/wiki/Two-factor_authentication)
- [Authy.com](http://authy.com)
- [REST API documentation](http://docs.authy.com/)
- [Authy-php on Github](https://github.com/authy/authy-php)

