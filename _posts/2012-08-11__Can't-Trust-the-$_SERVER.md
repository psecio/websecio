---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Can't Trust the $_SERVER
---

Can't Trust the $_SERVER
------------

{{ byline }}

As most developers discover within their first week of using PHP, there's these handy
things called "superglobals" that can make life easier. They give you access to things
from the URL, values submitted through forms, other information about the request and 
the server itself:

* `$_GET` - Values from the URL
* `$_POST` - Posted values (like from a form)
* `$_REQUEST` - A comination of the GET & POST
* `$_FILES` - Posted file information
* `$_SESSION` - Session information
* `$_SERVER` - Information about the server

This last one, `$_SERVER`, on the surface looks like something quite useful - it gives
us access to values like the server address (IP), the protocol the request was made with
and various other things. But it pulls it all from the server itself, right? 

**Wrong.**

See, there's a few values that PHP drops in based on the current request. If you assume
that these values are set in stone, you could be in for a world of hurt as would-be 
attackers walk all over your application and inject their content as they please.

Thankfully, not all of the values in `$_SERVER` can be exploited. Here's a list of 
the ones to watch out for and why:

* **PHP_SELF:** 
    Filename of the current script (relative to docroot). If you want to use this
    **always** filter!
* **REQUEST_URI:**
    URI that's used to access the page including everything from the URL requested
* **HTTP_HOST:**
    Has the value from the request's "Host:" header, client can set this
* **HTTP_USER_AGENT:**
    The client can set this to pretty much whaever they want (it could be "fuzzy bunny"
    if they wanted it to be).
* **PATH_INFO:** 
    Pathname as provided by the client (totally insecure)
* **PATH_TRANSLATED:** 
    Filesystem path to the script, but still could include XSS contentIncludes all values from the requested path
* **HTTP_REFERER:** 
    Set by the user agent, cannot be trusted
* **HTTP_ACCEPT:**
    If one is set, pulls from the "Accept:" header in the request
* **HTTP_ACCEPT_CHARSET:**
    Comes from the "Accept-Charset" header in the request
* **HTTP_ACCEPT_ENCODING:**
    Pulls from the "Accept-Encoding:" header in the request
* **HTTP_ACCEPT_LANGUAGE:**
    Value directly from the "Accept-Language:" header on the request
* **HTTP_CONNECTION:**
    Contains the value of the "Connection:" header, client can set to anything

As you can see, there's a good size list of things that cannot be trusted. Fortunately,
there's a few simple steps you can take to help prevent cross-site scripting attacks 
coming from these values. It really boils down to what should be a mantra for web 
developers by now (regardless of language): "Filter Input, Escape Output". Too bad 
"FIEO" doesn't exactly roll off the tongue...

One easy way to filter out a lot of the issues is to pass these values through a 
call to [strip_tags](http://php.net/strip_tags) or [htmlspecialchars](http://php.net/htmlspecialchars)
to remove malicious content. Obviously, this is pretty high-level filtering, so you
should still take some care when using the result. Nothing is infalable and users 
are tricky things - always filter!

By the way, one of the worst offenders I've seen is `PHP_SELF`. I've seen waaaaaay too 
many examples that use it in a `<form>` tag.

For more information on this, check out [the manual page for $_SERVER](http://www.php.net/manual/en/reserved.variables.server.php) in the PHP.net manual.


