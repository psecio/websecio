---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Adding custom two-factor auth
tags: twofactor,custom
summary: @todo
---

Adding custom two-factor auth
--------------

{{ byline }}

It seems like a week doesn't go by without some major service announcing that they're adding two-factor
authentication to their service. They're taking the typical username/password methods they've implemented
and dropping in this extra layer of protection for their users.

#### Two-Factor Advantages

#### Two-Factor Disadvantages

#### Pre-existing Services

There's a number of pre-existing services out there that you might look at before trying to implement something
yourself (not "reinventing the wheel" so to speak). They're pretty similar in what they offer as far as the actual
two-factor part of the solution, but differ in the services around that. Here's a list of some of the more
popular services:

##### [Google Autheticator](https://code.google.com/p/google-authenticator/)

While not specifically a service, it is
    a pre-existing two-factor solution. I've already [written about how to implement it](/2013/01/11/Googles-Two-Factor-Auth-Online-Offline.html) using a custom library that should make it easy to drop in, but there's still a little work
    involved in getting it to work in your system.

##### [Yubikey](http://www.yubico.com/)

Again, not technically a service, but it is a technology that can be used as a
    secondary auth mechanism for your users. There's another article [on this site](/2013/03/05/Two-Factor-the-Yubikey-Way.html) about using the hardware device and the API YubiCo offers to validate it.

##### [Duo Security](http://duosec.com)

This is the first in the list that's more of a true service. As I've [written in a previous post](/2013/01/09/Two-Factor-Auth-Integration-with-Duo-Security.html) they offer a more complete user management
    and two-factor authorization solution with lots of handy features like SMS messaging and push authorization requests.

##### [Authy](http://authy.com)

This is another service (and another one I've [written an article about](/2013/01/07/Easy-Two-Factor-with-Authy.html)) with less features than what Duo Security offers but is still a solid service. It's definitely
    growing in popularity right along with some of the others, though, and supports a wide range of smartphones/devices.

##### [Clef](http://getclef.org)

Clef is a relative newcomer to the world of two-factor authentication. They've taken their
    own approach to the typical two-factor interface and moved away from the typical one time password code to what they
    call a "wave" (like a moving barcode). You can find out more about them [in this article](/2013/09/26/Two-Factor-with-Wave-Using-Clef.html).

##### Others

There's other options out there as well with their own features a niches in the two-factor market including:

- [Toopher](https://www.toopher.com/)
- [Tokenizer](https://www.tokenizer.com/)
- [Rublon](https://rublon.com/)

#### Creating the Library

#### Integrating into your Authentication

#### Resources