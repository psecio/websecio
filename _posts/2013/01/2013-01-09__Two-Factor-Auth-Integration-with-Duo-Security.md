---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Two-Factor Auth Integration with Duo Security
tags: twofactor,authentication,duosecurity,api,webservice
summary: 
---

Two-Factor Auth Integration with Duo Security
--------------

{{ byline }}

The first part of this series looked at [using Authy](/2013/01/07/Easy-Two-Factor-with-Authy.html)
for two-factor authentication for your web applications. In this second part of the series,
I'll show you another service that offers something similar with a different level of offerings.

#### Enter Duo Security

[Duo Security](http://duosecurity.com) is a two-factor authentication provider with a bit 
more up its sleeve than just a REST API for the integration. Sure, they offer the API, but
it's just one kind of integration methods they offer. Being that this series is about 
web app integration (and APIs are perfect for that), I'm going to focus on that, but if 
you want to do two factor authentication on things like VPN, Unix logins and Remote Desktop
you should [check out what they have to offer](https://www.duosecurity.com/solutions/overview).

Much like with [Authy](http://authy.com), you'll need to sign up for an account before you 
can use their API. You can sign up from their [pricing page](https://www.duosecurity.com/pricing)
and choose the "Personal" option to get started at no cost. They use two-factor on their
own logins, so you'll need to provide them with a number they can send a verification
SMS to. This plan allows you up to ten users on your account and an unlimited number of 
"integrations". 

This plan also includes an initial one thousand "telephony credits" credited towards the
account. These credits are deducted each time you do things like have the service call
a user's phone or send an SMS. Another batch of one thousand only costs about $10 USD,
so it's not too bad to charge it back up if you need it.

Much like Authy, though, Duo Security also has [their own mobile client](https://www.duosecurity.com/docs/authentication)
that can be used without charge by your users. All they have to do is download it from 
their app store for their platform of choice and fire it up to generate the codes they need.

![Duo Auth mobile client](https://www.duosecurity.com/static/images/docs/authentication/duo_mobile_on_phones.png)

There's also a feature they call "[Duo Push](https://www.duosecurity.com/duo-push)*" that works 
for this same smart phone application and lets you push a notification to a user, asking 
them to authenticate the application immediately. Using this method also has the benefit 
of not using any telephony credits.

#### So, enough about the service - let's get started!

Now, Duo Security does offer their own [web application integration](https://www.duosecurity.com/docs/duoweb)
method that uses a Javascript script to handle the secondary authentication. Most sites 
make use of Javascript in one form or another these days, but the REST API option is a bit
more flexible and offers more information than just the pass/fail of the Javascript option.

If you want to get an idea of all of the information you can get from their API, check out
the documentation for the [Authorization](https://www.duosecurity.com/docs/duorest) and
[Administration](https://www.duosecurity.com/docs/adminapi) API endpoints.

They do offer their [own PHP library](https://github.com/duosecurity/duo_php) for integration,
but it's pretty limited. I decided that this wasn't good/easy enough for most developers
out there to work with, so I came up with [my own library - DuoAuth](https://github.com/enygma/duoauth).

You can install it through Composer using:

`
{
    "require": {
        "enygma/duoauth": "dev-master"
    }
}
`

There's a little bit of setup you'll need to do to get it all working. First and foremost, 
you'll need to log in to your Duo Security account and set up an "integration" for the REST API.
You can find the steps for this (and lots more info about DuoAuth) in [the project's wiki](https://github.com/enygma/duoauth/wiki).

Now, the normal REST API lets you do some of the most basic tasks:

- ping the service to make sure its up
- check to be sure your keys are correct
- check to see if the user is valid
- check the code that a user gives for validity

Let's test it out with a sample user - log in to your account and click on the *Users* link on
the sidebar. You can add a new user from the *New User* button in the top right of that page.
You'll be prompted for a username then, when submitted, you will be asked to add a phone 
for the user. You can also add a hardware token here if that's what you'd like to use instead.

Now that we have a user, we'll need their username to be able to authenticate the user and
match their code. With the `duoauth.json` configuration in place (see the wiki for info), 
you should be able to connect to your REST API integration and validate their code with
this little snippet:

`
<?php
require_once 'vendor/autoload.php';

$code = 'user-inputted-code';
$username = 'username';

$user = new \DuoAuth\User();
$valid = $user->validateCode($code, $username);
var_dump($valid);
?>
`

If everything goes well (and the code provided is good) your script should output: `bool(true)`.
You can also call the other actions on the *Authentication* API like the `preauth`:

`
<?php
$username = 'username';

$user = new \DuoAuth\User();
$auth = $user->preauth($username);
var_dump($auth);
?>
`

#### That's great, but how do I work with my users?

So, one thing that you'll notice missing from the Duo Security API that Authy has is adding
users through the API. Thankfully, there's an answer to this for Duo Security - the 
[Admin API](https://www.duosecurity.com/docs/adminapi) - and to make the DuoAuth tool 
complete, it also supports user management, including creating new users:

`
<?php

$user = new \DuoAuth\User();
$user->username = 'testuser1';
$user->fullname = 'Test User1';
$user->save();

?>
`

The code above will create a user, but won't assign a phone to them. You'll need to do 
that separately:

`
<?php
$phone = new \DuoAuth\Devices\Phone();
$phone->number = '+12145551234';
$phone->name = 'Galaxy S2';
$phone->platform = 'Android';
$phone->save();

// with the phone successfully saved, you can associate it with a user we made before
$user->associateDevice($phone);

// now send it an SMS with information on getting the smart phone app:
$phone->smsActivation();
?>
`

The DuoAuth library offers a lot more functionality for managing users, devices and getting
information from the Duo Security APIs about your account. Take a look at 
[the wiki](https://github.com/enygma/duoauth/wiki) for complete info and code examples.

#### Summary

There's a lot of things about these APIs that weren't mentioned here, but you can poke around
the documentation to find out more about them. The Duo Security service has a lot more to 
offer than Authy, but with that comes a little more overhead in getting things set up correctly.
Hopefully the DuoAuth library helps to take a bit of the pain out of the process, but I'd
recommend looking into their service if you need something more robust than just the API 
interface.


#### Resources

- [Duo Security main site](http://duosecurity.com)
- [Auth API documentation](https://www.duosecurity.com/docs/duorest)
- [Admin API documentation](https://www.duosecurity.com/docs/adminapi)
- [DuoAuth PHP library](https://github.com/enygma/duoauth)
- [Easy Two-Factor Authentication with Authy](/2013/01/07/Easy-Two-Factor-with-Authy.html)


