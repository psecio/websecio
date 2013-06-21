---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: The Plight of the Password
tags: password,twofactor,federated,identity
summary: Passwords must die, find out how to help that along in your own applications.
---

The Plight of the Password
--------------

{{ byline }}

It's no secret, especially in security circles, that there's a problem with passwords.
Any security professional worth their salt can probably ramble off some of the most comon
reasons why still using passwords in current systems, regardless of their entropy, is 
a bad idea. Their answers would probably include at least one or two of the following:

- **Password reuse:** Users are creatures of habit. Once they get a password they like
or think is clever, they stick with it. They use it across ten different online services,
usually with the same username if they can get it. All it takes is one of these systems
to be compromised...

- **Bad Passwords:** Along with being creatures of habit, users also love simplicity. They
think that acceptable passwords include things like their middle name, date of birth or
some that are just flat out bad...like "password" or "12345" (yes, it happens). People
just aren't geared to come up with strong passwords easily.

- **Passwords aren't stored correctly:** This one is the fault of the service than the user
but still contributes to the overall "password problem" applications are facing today. It
seems like there's a new news story each week about some service that was compromised and
their database of user data was harvested - including passwords. Now, if you're being bad, 
you're not doing responsible things like hashing and salting the data you're storing for
users. If you or your company are storing anything related to passwords in plain-text anywhere
(not just databases) step away from the keyboard and ask yourself what would happen if all
that data was dumped to a public server somewhere. Fun, huh?

- **Strong passwords aren't encouraged:** There's lots of services out there that offer you
guidelines about what your password should contain. They require you to have things like 
"at least one uppercase letter" or "more than one number and a special character." These
kinds of things can help, but the definition of a "strong password" is a pretty ambiguous
thing right now. There's a camp that thinks you shouldn't even bother with something you 
can remember and go random. There's another that suggests that by including enough restrictions,
they can help you pick a better password. There's valid arguments on both sides, but only 
more recently are services including things like a "password strength" meter as you're 
signing up to help you judge just how safe your password of choice is.

These are just a small sampling of the many reasons that [passwords must die](http://lauren.vortex.com/archive/001035.html) and shouldn't be considered as a valid authentication option for any kind of application and, 
more relevant to this article, web applications. The venerable "username and password" combo
has stood the test of time, but it's time to retire it in favor of other methods that provide
better identity management and, frankly, do a better job of what they're intended for - 
*proving that you're you*.

So, what's an application developer to when it comes to securing their applications these days?
If the password shouldn't be relied on as a secure identification method, what're the next
logical step? Well, there's two popular theories out there (two of many) about solutions that
can help with this dilemma. They both still involve passwords in a way, but they're a bit
more robust and more correctly solve the identity problem - *federated identity* and
*multi-factor authentication*.

The rationale behind these is that the password is no longer a single point of failure. Passwords,
by their nature, are a "guessable" kind of thing. If someone gets lucky enough, they can 
become you and potentially cause all sorts of havoc. These two methods help prevent that
by adding extra layers on top of the password-based "you" in different ways.

#### Federated Identity

We'll start off by looking at the concept of *federated identity* and how it helps prevent
some of the password-related issues.

> A "federated" identity is a token or other authentication mechanism that's used across
> multiple systems or groups and is trusted to correctly identify the user making the 
> request.

There's a pretty common example of this that most people have at least had some kind of
casual experience with - Single Sign On (SSO). While it seems to pop up a bit more in 
corporate environments, SSO has been adopted by several services (such as Google) that
need a way to connect the user with multiple smaller applications without forcing them 
to re-authenticate or generate a username/password for each of the services. In company
circles, this is often achieved via something like an LDAP server that everything can 
validate against.

Web applications could use these same methods, but there's some more base-level protocols
that have emerged to help with this exact kind of thing: [OpenID](http://en.wikipedia.org/wiki/OpenID) 
and [OAuth](http://en.wikipedia.org/wiki/OAuth). While these aren't the only federated
solutions out there [Persona anyone?](/2012/10/01/Using-Mozilla-Persona-with-PHP-jQuery.html) they
are some of the most popular, especially with Open Source applications. The procedures involved in each 
could take up whole articles in their own right, so let me just give you an overview of 
the point behind each:

##### OpenID

The basic idea behind OpenID is one of providers and requesters. Where OAuth is more about
authorizing access based on the identity from another system, OpenID focuses more around
independent identity providers with validated user information.

Think of OpenID as a large, decentralized authentication system. Here's the common flow
for using OpenID for an application:

1. User comes to *Application #1* and hits the "Log in with OpenID"
2. They select from the provider they already have an account with (validated through maybe
    an email address or something outside this process)
3. They're redirected to a page on that provider's site and asked to log in with their 
    single user credentials
4. This validates that the user is correct and information is sent back to *Application #1*
    along with data about the user that the OpenID provider has

Using this information, *Application #1* then knows it has a valid user and proceeds with
letting them access the application. Chances are it has some of its own permissioning 
surrounding the user's access, but the authentication piece has been handed off and 
abstracted out from its responsibilities.

Also, because of its decentralized nature, OpenID can also be used as a login across
lots of services - basically anything that chooses to implement OpenID as a part of its
login process. It's not an "all or nothing" thing either. I've seen several services that
offer both the usual username/password kind of login right next to the OpenID option.

##### OAuth

OAuth is a protocol that's designed for services to talk back to an authorized source to
validate the user. Essentially, the user has one verified account with this service and
the applications that need more info about them hit up that service. For a user, the common
flow is something like:

1. Access *Application #1* and clicks on the "Login using Github" button
2. They're redirected to a special page on the Github site that asks them if it's okay for
    *Application #1* to connect
3. If they okay the request, a session is created and they're sent back to *Application #1*
    and authorized under their Github account

In this way, Application #1 never needs to know the user's credential information. That
becomes the job of Github. This protects the user's login information and has the added
benefit of knowing what applications your identity is connected to. Also, depending on how
Application #1 is set up, you could also define the level of access the connection can
have (read only, only update contact information, etc). It really just depends on how
the system wants to handle it.

#### Multi-factor Authentication

Another identity technology that's been exploding over the last year is the idea of 
*multi-factor authentication*. Simply put:

> Multi-factor authentication provides an additional layer of identity validation
> based on more than at least two or more factors: "something they know", "something 
they have" and "something they are".

In previous years, these other authentication factors came in lots of different shapes 
and forms (it's not a new concept, after all). Common techniques have been:

- tokens with rotating codes
- ID badges run through a card swiper
- keys
- biometrics (thumbprints, iris recognition, etc)

With the prevalence of cell phones and other smart devices, another method has come into
play and is gaining a lot of traction in some of the major players on the web. Previously,
if you wanted to have some kind of token or key based system, you needed to carry an 
additional (little) piece of hardware with you - a physical token. Unfortunately, these
have a bad habit of getting lost. Their size makes them easier to carry but also can make
them harder to find.

Someone smart looked around them and noted that more and more people are already carrying
devices that could easily reproduce this same functionality and have a much less chance of 
being lost as they're important for other reasons - smart phones. Even Google got into the
game with their own [Google Authenticator](/2013/01/11/Googles-Two-Factor-Auth-Online-Offline.html) 
tool that runs on just about any smart device out there. Other services have started popping
up as well - ones that provide two-factor authentication as a service through their apps
via things like one time codes or push notifications.

This is all well and good, but what about those out there with phones that can't install 
applications? Not everyone has a smart phone (though it seems like it) so there had to be
a way to handle those people as well. Well, what's one feature that you can do with just
about any phone these days and is very widely used? Text messaging, of course. Most of the 
services that have their own smart phone apps have realized this and they also provide a 
"one time code via SMS" as a part of their system.

So, how does it work? Here's the basic flow:

- User goes to *Site #1* and enters their login and password (yes, there's that pesky password)
- Once *Site #1* validates the password, they find that user's device information in their settings
- Depending on the method they use, either a one-time code is generated and SMSed or a push
    notification is sent to their device
- *Site #1* then either asks for the code the user just received or waits for the push notification
    to be accepted
- Once this second method is confirmed, the user is successfully logged into the system

Hopefully it's pretty obvious how this helps with protecting a user's account more than 
just the password alone. The user of the system has to have two things - the "something they
know" in the form of a password and the "something they have" in the form of their device 
(smart phone or otherwise). Without both, the user is unable to access the service.

If you're interested in finding out more about some of the current two-factor services and
tools out there, I suggest taking a look at the [two-factor series](/tagged/twofactor) of
posts that will give you a more technical look at implementing them.

Unfortunately, because of the requirement of something physical to do the secondary identification,
the same loss issues that came into play with the tokens are still a possibility. Granted,
people are much less likely to leave their iPhone laying around than a single-purpose
token, but it can definitely still happen.

Then you come to the other downside - what happens if your device is stolen or does get lost
somehow? If your accounts are linked to that device, are there policies or tools in place 
where you can report the loss to the provider? Not if they require a login to use...you
can see the dilemma.

#### No Silver Bullet

Much like anything to do with security (or technology in general for that matter) there's 
not a silver bullet when it comes to trying to get rid of the password. Even the solutions
mentioned here still have it as a part of their process, there's just a few extra things around
it that enhance the protection level. 

These things also come at a price - whether that be grumpy users from having to do yet another
step "just to check their email" or the business cost that comes with their implementation. There'll
always be a trade-off and balance to find when implementing security for your user base. Remember
one of the goals with all of this protection:

> The goal of security is to make a problem disappear and use positive, not negative, 
> reinforcement to encourage it with end users.

#### Resources

- [Two-factor authentication series](/tagged/twofactor)
- [The Difference between OpenID and OAuth](http://stackoverflow.com/questions/1087031/whats-the-difference-between-openid-and-oauth)
- ["Die Passwords! Die!" - Lauren Weinstein](http://lauren.vortex.com/archive/001035.html)
