---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Google's Two-Factor Auth - Online or Offline
tags: twofactor,authentication,google,authenticator
summary: The Google Authenticator smartphone application makes two-factor auth simple, even without a connection.
---

Google's Two-Factor Auth - Online or Offline
--------------

{{ byline }}

This is the third part in our "two-factor authentication" series (part one on Authy [is here](/2013/01/07/Easy-Two-Factor-with-Authy.html)
and part two with Duo Security [is here](/2013/01/09/Two-Factor-Auth-Integration-with-Duo-Security.html)).
In this tutorial, I'll be looking at a stand-alone tool that comes out of Google that you can use for any
application (web or not really) to provide two-factor authentication for your users - the [Google Authenticator](http://code.google.com/p/google-authenticator/) project.

#### An Overview

Where the other two services that I've talked about before now are remote solutions that include a bit
of user management to go along with the two-factor verification, the Google Authenticator project is
a bit more "base level". The Authenticator only really cares about one thing - the initialization key
that's configured in your application. There's no user-specific functionality, it's all just about
code generation and validation.

Because of this, the Google Authenticator can be used just about anywhere without having to have a network
connection to function. Codes are generated based on a calculation involving your initialization key
and a timestamp to provide limited-time tokens.

As with the other services, there's a client for your smart phone that you can use to generate these tokens:

![Google Authenticator Clients](http://guides.webbynode.com/articles/security/images/phones.png)

*Image courtesy of webbynode.com*

This client will refresh the codes automatically for you and supports multiple applications, so
you're not just limited to using one initialization code across multiple systems. When you set up
the client on your device, you'll be asked for either a code or to scan in the QR code to
link to the account. There's [plenty of examples](http://lmgtfy.com/?q=google+authenticator+qr+code)
out there of how to use the Google Charts service to generate these, so I won't get into
that here.

I am, however, going to show you how to implement the code checking into your application using a
simple script I put together, making it essentially two or three lines of code to be
able to validate what the user enters.

### Using GAuth

The PHP [GAuth library](https://github.com/enygma/gauth) can be installed via [Composer](http://getcomposer.org)
off of Github by adding the following into the `require` of your `composer.json` and running an update:

`
{
    "require": {
        "enygma/gauth": "dev-master"
    }
}
`

This will get the library installed and ready for use via the Composer autoloader. If you're
implementing this for the first time, chances are you'll need what they call an "initialization
key" to set things up. This key is like a unique fingerprint to your application and is the
code you'll share with your users to link their clients to your application for code generation.

The library provides an easy way to generate one of these codes:

`
<?php
$g = new \GAuth\Auth();
$code = $g->generateCode();
echo 'Generated code: '.$code;
?>
`

The result will come out looking something like "PXFML6IQDBECBP4Z" - a seventeen character string
that you'll need to store somewhere on your side for later use. This value is used during the
computation and verification process when the user enters their code, so it needs to be
a static value somewhere. You don't want to re-initialize this value each time as that'll
break the link the user's set up on their client.

You *can*, however, set up different initialization keys on a per-user basis and have them
set up that code. This offers another level of complexity on top of the usual two-factor
authentication and prevents someone getting in and finding out your "master" value and
using it to trick your users into submitting their codes for their own use.

#### Validating Codes

So, the next obvious step is to want to validate the codes that the user gives you. *GAuth*
makes this simple too - it's two lines of code:

`
<?php
$g = new \GAuth\Auth('your-initialization-key');
echo ($g->validateCode('code-inputted-by-user')) ? 'Validated!' : 'Invalid!';
?>
`

The first line creates the `Auth` object based off of your initialization key. This is why
you need to store that value when it's generated. If you don't set this (either when the
object is constructed or later with the `setInitKey` method), your computations will be
all off and none of the codes the user enters will be valid.

The `validateCode` method is then called on the user input and a boolean (`true` or `false`) is
returned based on the pass/fail status.

This class was heavily influenced by [this class](http://www.idontplaydarts.com/wp-content/uploads/2011/07/ga.php_.txt)
and implements a feature that can be useful to your users depending on the system they're
using and any possible latency involved. It offers a "window" of a few seconds on either
side of the current timestamp to see if their code is a match. The default for this value is
set to **two seconds** but it can be changed with the `setRange` method if you'd like to
customize it:

`
<?php
$g = new \GAuth\Auth();
// set this value in seconds - resetting to 4 seconds
$g->setRange(4);

/* go on with the code validation */
?>
`

Obviously you don't want to set this too high or you'll potentially be validating a lot of
codes when a user hits your application to verify.

### How it Works

Since the goal of the library was to make the validation as simple as possible, it hides
away most of the "hard stuff" inside the class, but here's a basic summary of what happens
when a user's code is validated:

1. The code is passed in from the user input into the `validateCode` method
2. This method then does a `base32` decode on the initialization key (yes, there's a custom `base32_decode` method in there)
   to get the binary representation of the value.
3. This is then used, along with the current (or specified) timestamp in the `generateOneTime` method to create the set of
   codes to check the user input against.

There's some mechanics internal to the class on how the hashes are generated with a few other
methods involved, but that gives you a basic idea of the flow.

#### Summary

I hope that using this tool - and seeing how simple it is to implement - will encourage you
to work this into your authentication process and make life more secure for your users. Yes,
you're putting faith in Google to not change things up on their Authenticator project, but
consider this - this is also the same technology they use to validate users for their own
services (most notably Google Mail).


#### Resources

- [Easy Two-Factor Authentication with Authy](/2013/01/07/Easy-Two-Factor-with-Authy.html)
- [Two-Factor Auth Integration with Duo Security](/2013/01/09/Two-Factor-Auth-Integration-with-Duo-Security.html)
- [Google Authenticator on Google Code](http://code.google.com/p/google-authenticator/)
- [PAM module for Google Authenticator](http://code.google.com/p/google-authenticator/source/browse/#hg%2Flibpam)
- [Google Authenticator plugin for Chrome](https://chrome.google.com/webstore/detail/gauth-authenticator/ilgcnhelpchnceeipipijaljkblbcobl)


