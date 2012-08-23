---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: OWASP Top Ten: Broken Authentication and Session Management
---

OWASP Top Ten: Broken Authentication and Session Management
--------------

{{ byline }}

I'm back with another part of my [OWASP](http://owasp.com) "Top Ten" series - this time
with a focus on one of the more difficult issues to face web application developers: 
user authentication and session management.

This is a huge topic and could take many, many (*many*) articles to cover and a lot of 
what it boils down to is "it depends". There's so many methods for authentication out
there and so many different kinds of permissioning, there's not a **One Right Way** to
handle things. There are, however, some good practices to follow when you're working 
with user authentication. Here's some of the best of them you really should be following
in your development:

1. **Don't reinvent the wheel**: Yes, I know your authentication is as different from the 
next guys as Vogons are from mice, but that doesn't mean you have to write it all yourself.
There's tons of helpful libraries out there for your language of choice that have been
vetted over time and improved. I'm willing to bet that, because of some of the common 
mistakes (exactly the ones that the OWASP points to as bad practices) will come up...
and you'll miss them. Pick your head up out of the code for an hour and take a look around.
I bet you'll find something, if not close, exactly what you're looking for and - even better - 
you don't have to maintain it!

2. **Use SSL**: If you value the security of your application at all, you'll be much better
off in the long run of you take a little time and get your site set up to use an SSL 
certificate and HTTPS as its primary protocol. In case you're curious about what kind
of advantages HTTPS offers over HTTP, check out articles like [this](http://blogs.msdn.com/b/securitytipstalk/archive/2011/04/04/http-vs-https-what-s-the-difference.aspx) or
[this](http://www.biztechmagazine.com/article/2007/07/http-vs-https) to get the lowdown.
For the lazy out there, the real key to it is that HTTPS uses SSL (Secure Socket Layer)
to transmit the data over the wire. This makes use of a certificate on both the server
and client side to encrypt the data and decrypt it when it's received. This encryption
heps prevent a lot of the problems that can come up with trasmitting data in the clear
via HTTP. Remember, though - it's not a cure all and it definitely doesn't mean you can
get sloppy with how you handle your user's data!

3. **Use (and enforce) strong passwords**: Sure, you can only protect your users from 
themselves so far, but this is one thing you definitely have control over. When a user
signs up for your site (assuming you're using something like a traditional user/password
auth, not OpenID or anything) enforce harder standards on their passwords before they 
can successfully sign up. How complex you go is really up to you, but there are some 
recommendations as to what makes stronger passwords:

    - At the least, 8 to 10 characters
    - At least one capital letter
    - At least one lowercase letter
    - At least one digit and special character
    - Do not allow one that's been used in the last year (on "forgot password") or similar
    - Anything listed on any page in [these results](https://www.google.com/?q=most+common+passwords)
    - Not containing any part of the username
    - Is not a sequence of letters on the keyboard (ex. "qwerty" or "12345678")
    - Don't exclude characters

  Of course, you should always inform the user of these restrictions and provide them
  with immediate feedback as to the strength of their password (and if it meets your 
  requirements).

4. **Store passwords hashed**: As tempting as it is to store the passwords as-is when
you get them from the user directly to the database, this is essentially the same as 
inviting any attackers in and giving them the keys to the kingdom. You have a responsibility
not only to the user data that you're protecting with your authorization but also to 
the data for your users on other systems. People are creatures of habit and will almost
always use the same login information across multiple sites. Imagine the damage someone
with even minimal skills could do with plain-text passwords at their disposal.
  
  When a user signs up, *at the very least* hash their password against a static app-wide
  hash that you've defined before putting the values into whatever data store you're using.
  An even better mechanism would be to create a unique hash for each user on signup 
  that you could use as the salt to generate their hashed password. This salt could be 
  combined with other information in your application to evaluate the user when they
  log in. 

  This is not the pinacle of security when it comes to password storage, but you'd be
  suprised at how many developers (and companies - big ones too) don't even bother with
  something as small as this. If you want to take things to the next level, consider 
  replacing the hashing option and encrypting the passwords instead.

5. **For high security, whitelist**: If your application requires an even higher level
of secuity and you're particularly concerned about users accessing things they shouldn't,
consider moving from a "blacklist" to a "whitelist" solution for access control. In the 
typical "blacklist" solution, you look at the resource they are trying to access to see 
if they're prevented from using it. While this sort of checking can be easier, it can 
also lead to unintended consequences if you forget a single resource along the way. Consider
instead taking the "whitelist" approach and granting them access to resources and parts
of the application, not restricting them. More often than not this makes for a tighter
security model.

6. **Use short-lived hashes**: If you have something like a "Forgot Password" or "Forgot
Username" feature of your application (if you do, guard it with your life) be sure that 
you include some sort of time-related hash attached to the link you provide them for the 
reset. If you only provide them with a link that relates to their user, that leaves the 
door wide open for any attacker to come in and abuse it. 

  By having the hash and checking it on submit, you can ensure 1) that the user in 
  question is the correct one that has the valid link and 2) the decreased chance that 
  the service can be abused.

7. **Define a "lockout threshold"**: One thing you can do as a little extra protection
is track the actions that are taken as they relate to things like logins, password resets
and any other external facing user functionality. By tracking this kind of usage, you 
can provide a "lockout" if a high volume of requests come in around a certain user. Once
a user's actions hit this threshold (lower for more security, obviously) you just lock 
it out of any further actions for a certain time period. More often than not, unless there's
something malicious going on, your users will never see the problem.

  This can get a bit more tricky if you're dealing with something like an API that's designed
  for handling autmated access, but that's where fun things like request throttling and 
  limitations can come in...a whole different topic.

#### Summary

Hopefully I've given you some good things to consider (and maybe bring back up) to help 
you protect your users from some of the more common authetication issues. The OWASP has
some other great suggestions too - be sure to check out the resources for more information
on those.

#### Resources
* [OWASP page on User Auth](https://www.owasp.org/index.php/Top_10_2010-A3)
* [Dos and Dont's of Client Authentication](http://pdos.csail.mit.edu/papers/webauth%3asec10.pdf)
* [Authentication - Security Best Practices: Palisade](http://palizine.plynt.com/issues/2004Jul/safe-auth-practices/)

