---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: The External CSRF Threat & Protecting Your App
tagged: csrf, introduction
---

The External CSRF Threat & Protecting Your App
--------------

{{ byline }}

There was a [recent podcast](http://www.sitepoint.com/podcast-185-csrf-is-the-new-sql-injection/)
from SitePoint that suggested that CSRF (Cross-Site Request Forgeries) are the "New SQL Injection".
They cite a re different articles (including [this one ](http://thenextweb.com/insider/2012/10/22/hackers-have-a-new-favorite-attack-vector-cross-site-scripting-up-69/)) where they talk about CSRF attacks (or "[confused deputy](http://en.wikipedia.org/wiki/Confused_Deputy)" attack) being on the rise. With so much talk about SQL injection 
and the dangers of it, it's not surprising that the number of these attacks have been trending 
down in the past months. Developers and site admins are getting wiser about protecting their 
data from malicious user input and, as a result, have dropped the chances that bad data gets 
past their defenses. 

So, for those new to things, let me give a brief overview of what CSRF is and how it can
be used maliciously to Do Bad Things to your web applications. We'll start with the obligatory
[Wikipedia link](http://en.wikipedia.org/wiki/Cross-site_request_forgery) to general information 
about the attack:

> Cross-site request forgery, also known as a one-click attack or session riding and 
> abbreviated as CSRF (sometimes pronounced sea-surf) or XSRF, is a type of malicious 
> exploit of a website whereby unauthorized commands are transmitted from a user that 
> the website trusts.

#### A Sample Attack - The Diaper Cat

These attacks are a little bit tricky to explain, but here's an example: Say you're logged
in to your favorite social networking site and are browsing around to fun links from the
people you follow. One of them posts a link to a site that sounds interesting, so you 
click on over and check out the hilarious video of a cat wearing a diaper doing back flips.
You chuckle while the video plays, but there's something else at work here. Just by visiting
this page (that your friend probably got from a friend and them from a friend...) you've
unknowingly executed a script that has used your login to make a post on the social networking
site about the cat video website, all without you knowing.

How could this have happened? You only visited the page and didn't even click on anything
while you were there! Well, this is a (simplistic) example of a Cross-Site Request Forgery
that took advantage of a flaw in the social networking site's code to post as you, using
your current login. Here's the trick, when you visit the cat video, a hidden HTML element,
maybe an `img` tag, was crafted to hit a URL on the social site with the link to the video's
website. The site's developers have made an assumption that, because the user is logged in,
they can just assume that anything that comes in via that URL is safe and can be posted.
Unfortunately for them, they were wrong:

`
<img src="http://mycoolsite.com/post.php?title=Check%20out%20this%20cat&url=http://funnycat.com" />
`

In our example the site on `funnycat.com` has the malicious `img` tag and, because of how
browsers request the resources on the page, the "image" is fetched and the `post.php` is
executed with the `title` and `url` given. The post ends up in your stream, other people
click on it and it spreads like wildfire (because really, who doesn't love a cat in a diaper).

#### Preventing the Attack

Thankfully, preventing CSRF attacks is a relatively simple thing to do. One point I want
to make before getting into it, though, is something that has its roots in what the HTTP
verbs are for. In my above example, I've specified items on a `GET` string. You should **never**
write your application so that something can make changes with just a `GET` request. You 
should at least use `POST` or `PUT` for that kind of thing. You'll see how this comes into 
play in a bit.

There's two things that you can do to help prevent these sorts of attacks, one being a sort
of "first line" and weaker check and the other a bit more solid: referrer checking and the
inclusion of a token in the request that must be matched to be successful.

##### Checking Referrers

One thing that can help protect your application from some of the low end CSRF attacks is 
something simple - validating that the value in the `$_SERVER['HTTP_REFERER']` comtains what
you're expecting. If an attacker's script is coming from another site (as it is with the Diaper
Cat) this sort of check is a cheap way to catch it. Simply check it and deny if it doesn't match:

`
<?php
$referer = $_SERVER['HTTP_REFER'];
if (preg_match('/^http:\/\/mycoolsite\.com/') == false) {
    header('HTTP/1.0 401 Unauthorized');
}
?>
`

When doing your checks, be sure you're looking at the right part of the referrer and not
just trying to find it in any part of the string. You wouldn't be preventing much if you
look for `mycoolsite` anywhere and the URL has `?var1=mycoolsite` in it.

##### Prevention with Tokens

After you've checked your referrer and all seems well, there's another line of defense 
the request should go through before being accepted. This is another check, but this time
it's based on something you control - a token that's included with the request (`GET` or 
`POST`) that's validated against an internal hash or a session value.

Here's a classic example:

`
<?php
$_SESSION['csrf-token'] = sha1(time().$secretHash);

if (isset($_POST['submit'])) {
    // check the token
    if (isset($_POST['token']) && $_POST['token'] === $_SESSION['csrf-token']) {
        return true;
    } else {
        return false;
    }
}
?>
`

Obviously, this is a pretty simplistic example of securing a `POST` request with a token.
In this case, the `POST`ed values may have come from a form with a `token` field that was
populated with the `csrf-token` value from the session. When the resource is `POST`ed,
the values are compared and evaluated for a match.

In our hash, we used a two different pieces of information - the time in seconds, a `$secretHash`
that's defined in our application's configuration. You can use all sorts of methods to generate
these tokens, but here's a few suggestions to get you started:

- Be sure you use something randomized (maybe it's the result from `time()` or a `uniqid`)
  to help prevent replay attacks. Since the token is probably generated first then injected 
  into the page and session, matching them shouldn't be an issue.

- If your user is logged in, use something from their profile in the hashing to promote 
  even more unique hashing.

- Always provide a hashed value as the token, never just append strings together. Hashing 
  isn't the most secure, but it does at least make it more trouble than its worth to
  break the hash back into its original value(s).

- Be sure to use both an `isset` and `===` to compare the hashes to one another. If the
  token happens to not be in the session (maybe due to session poisoning due to 
  [unencrypted sessions](/2012/09/10/Encrypted-Sessions-with-PHP.html)) and the token
  isn't set in the request, PHP would see both as `null` values and let the request though.

I've been talking about putting it into the session up until now, but it's entirely possible
that your hash is something you can regenerate when the content is submitted to. Take for
example something like:

`
<?php
if ($_SERVER['HTTP_METHOD'] == 'GET') {
    $token = sha1($_SERVER['SCRIPT_FILENAME'].'|'.$secretHash.'|'.implode('|',$_GET));
    if (isset($_GET['token']) && $_GET['token'] === $token) {
        return true;
    } else {
        return false;
    }
}
?>
`

Using this method, we can, on our side, regenerate the hash based on the URL we want to
request and have things match up. Notice that I still kept the `$secretHash` in there. 
I'm a strong believer that having a [high entropy](http://en.wikipedia.org/wiki/Entropy_(information_theory))
string be a part of the hash generation is crucial for making good hashes.

#### Fixing Our Example

So, let's see how we can take our example with the Diaper Cat above and fix it with the 
above methods:

`
<?php
// this is on the uber cool social networking site, in post.php
if (isset($_GET['token'])) {
    $token = sha1($_SERVER['SCRIPT_FILENAME'].'|'.$secretHash.'|'.implode('|',$_GET));
    return ($token === $_GET['token']) ? true : false;
} else {
    header('HTTP/1.0 401 Unauthorized');
}
?>
`

and a valid request might be: 

`http://mycoolsite.com/post.php?token=97b44fab94c5d0d3d0ee2ff7eee0b96e94a4004e&title=Security%20ownz!&url=http://websec.io`

When this request comes into the `post.php` script, the value for `$token` is then generated
and compared to the one sent with the request. If there's a match, the request continues. If
not, they get back a HTTP 401 "Not Authorized" status code preventing them from finishing out
the request.

Now, to get back to the HTTP verb thing I mentioned above...allowing this sort of thing 
via a `GET` request is a bad idea. For things that change the state of your data, you should 
always use a `POST` or `PUT`. As per the definition of a RESTful API, `GET` requests are for
retrieving data and `POST`/`PUT` are for saving/updating data. Thankfully, this also makes
it easier for you to incorporate CSRF checking into your application's `POST` handling, making
it work for both normal `POST`ed requests and for the forms on your site.

There's a few tools out there (and some [suggestions](http://www.tonybibbs.com/2008/04/Protection-Against-CSRF/))
about ways you can automatically add CSRF tokens to your forms too, making it even easier to 
implement the checking without having to think about it. Some PHP frameworks, like [Symfony](http://symfony.com)
even come with CSRF token support built into the forms it generates.

When making the `POST` with a CSRF token, there's two ways to handle it. You can either:

- Include it along with the rest of the `POST`ed data and check it there or
- Set it as a header in the request (something like `X-CSRF` maybe). This has the added
  benefit of being able to work with directly accessed API methods as well to be integrated
  into its authentication scheme.

#### One Last Note - Beware the XSS

Protection from Cross-Site Request Forgeries in your own code is good, but there's other
threats to worry about at the same time. For example, say your code injects a CSRF hash into
your forms for you automatically, but the same form has a [Cross-Site Scripting](http://websecio.localhost/2012/08/10/OWASP-Top-Ten-Cross-Site-Scripting.html)
issue in it. Using this exploit, an attacker could bypass your CSRF if they have access
to extract a token from another page (within the same session) via a `GET` request and use
it to submit the form. Check out [this site](http://www.christian-schneider.net/CsrfAndSameOriginXss.html)
for more details on that.

This is a reminder that you **cannot treat security threats in isolation**. You have to consider
the entire attack surface of your application and consider what happens if *nothing* goes right.
Using tokens is good, but incorporating it into a more complete application security policy
is even better.


#### Resources

- Wikipedia on [Cross-Site Request Forgery](http://en.wikipedia.org/wiki/Cross-site_request_forgery)
- [OWASP on CSRF](https://www.owasp.org/index.php/Cross-Site_Request_Forgery_%28CSRF%29)
- WebAppSec's page [about CSRF](http://projects.webappsec.org/w/page/13246919/Cross%20Site%20Request%20Forgery)
