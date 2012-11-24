---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: OWASP Top Ten: Cross-Site Scripting (XSS)
tags: xss,owasp
---

OWASP Top Ten: Cross-Site Scripting (XSS)
--------------

{{ byline }}

If you're a web application developer in this day and age and you haven't at least heard
about cross-site scripting ("XSS" for the lazy), you've probably been living under a rock.
Cross-site scripting vulnerabilities are one of the most wide spread web app attacks
that happen every day. They can also cause some of the worst troubles - tricking your
users with modified links, injected content or even pull information from the user's 
browser without them knowing.

This vulnerability is easy to overlook unless you're specifically looking to take care
of it, unfortunately, but it's prevention is key to keeping you and your app safe.

Lets look at the most basic of examples - this is one you'll find in 99.99% percent of 
the XSS tutorials out there (about PHP at least), but it's a good simple example:

`
<?php
$_GET['test'] = "<script>alert('injection, yo!')</script>";
echo $_GET['test'];
?>
`

Go ahead, try runing this one in a sample script - you'll see the problem immediately. 
In this example the value for `$_GET['test']` is hard-coded, but it could have just as
easily come in from the URL. This is where the problem *really is* - developers get lulled 
into a false sense of security and think "oh, the only things coming in are what I expect"
and they don't try to protect themselves.

So, how can you protect your site from this menace and rest a little easier at night? Well,
thankfuly PHP gives you a few different options to help get rid of the things that go
bump in the right like [strip_tags](http://php.net/strip_tags) and 
[htmlspecialchars](http://php.net/htmlspecialchars).

I'm going to focus on the second of these, [htmlspecialchars](http://php.net/htmlspecialchars)
because of a handy feature that will come in a bit later to help keep you even a bit safer.
This function takes the string you give it and converts all of the special characters that it finds
(the non-alphanumeric ones) into their corresponding HTML encoded versions. Since the key
to this kind of attack is in the unfiltered output of a user string that could contain markup,
it essentially removes most issues that could be XSS related. Things like `>`, `&` and quotes are
all sanitized.

The quick and dirty way to take care of lots of your issues is by escaping with `htmlspecialchars`:

`
<?php
echo htmlspecialchars($_GET['test']);
?>
`

This removes the HTML from the input and does a prety good job of sanitizing things. One thing
to be careful of, though - while it can prevent the inclusion of full tags, you still have to be 
careful if you're using the user inputted value in you tag attributes.

Remember the mantra: **Filter Input, Escape Output** (FIEO it!)

### Resources

* [OWASP XSS Cheat Sheet](https://www.owasp.org/index.php/XSS_(Cross_Site_Scripting)_Prevention_Cheat_Sheet)