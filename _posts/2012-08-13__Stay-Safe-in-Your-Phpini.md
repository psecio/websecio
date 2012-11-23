---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Stay Safe in Your Php.ini
---

Stay Safe in Your Php.ini
-----------------

PHP is one of the easiest web-related languages you can pick up these days - it's no 
wonder that so much of the web is powered by this language. Thanks to a lot of different
projects out there, even getting it insalled and cooperating is a few click process
(if it's not already installed by default!) Unfortunately, because the language is 
so easy to just drop into and get started with, it also leads to some bad security practices
by developers that "just want to get things done" and don't care too much about 
protecting the applications they write. Add to the fact that PHP doesn't have much
built-in security to help these developers out and you could be in for some nasty 
surprises.

There is one thing that PHP programmers have on their side, though - that handy 
settings file that's included functionality in every PHP instance on the planet, the
`php.ini`. By turning on/off some of the settings in this file, you can reap some
immediate benefits for the security of your application.

Below is a list of some of the more common settings you can use to provide a little
extra piece of mind for your apps:

- `display_errors`: By default, PHP will output it's error messaging to whatever's calling
the script (be it in a brrowser or a command line). Unfortuantely, this can provide attackers
with valuable things like file paths and even line numbers where errors hapened. Using this 
sort of information, they could "feel out" your app and possibly find vulnerable spots. 
Because of this, it's recommended that - in production - you set your INI to **turn off errors**
and prevent this information from showing. You can, however, leave them on if you choose to 
implement your own [error handling](http://us3.php.net/manual/en/function.set-error-handler.php)
that lets you control the output a bit more.

- `allow_url_include` & `allow_url_fopen`: These two are pretty similar, so I figured I'd 
just lump them together. When you think about your application and all of the files that it 
includes for each request, you think that's all pretty secure but what happens if somehow
an exploit is found and the attacker figures out how to get your code to include one of their
scripts from a remote server? Mass chaos, that's what. Since the inclusion of files doesn't
show up in any logging (unless you have something custom), it's very hard to diagnose something
like this. You can give yourself an extra layer of protection by disabling both of these features. 
They prevent the opening of resources from remote sourcesa and help to lock down your code 
to only local resources. This can be a bother if your app needs to pull in data from an external 
source, but you'd be better off in the long run having some other process do that pull rather than 
leave this possible door open.

- `max_execution_time` & `memory_limit`: This is another two-for-one that can not only help 
protect your application but can help to keep your sysadmins happy too. Their names are pretty
self-descriptive, but here's a brief summary for each. the `max_execution_time` is a per-request 
setting and can prevent runaway processes from taking over your system. If an attacker was trying 
to perform an exploit and was trying to overload the server, having something like this set could 
make their job a whole lot harder. The `memory_limit` setting could help with the same sort of
problem too. It keeps the pre-request memory limit of the script at or below the setting. It's
only allowed to allocate that much memory, so it can help keep a cap on your resources.

- `disable_functions`: You probably won't see this one used very often, but it can be very handy
if you know there's things you just don't want used in your application. You can provide
this configuration directive with a list of function names to restrict. One example of how this
could be useful is to disable any of the functions that let you execute anything on the filesystem
(like exec, passthru or system).

- `session.cookie_httponly`: By enabling this setting, your sessions (if you're using them) will be
forced to use a "HTTP Only" cookie to store the session ID on the client side. This type of cookie
has the added benefit of only being able to be read by the system that initiated it. This helps
to prevent any information disclosure via an XSS attack that could leak your session ID and 
make it super simple to perform a session fixation attack and masquerade as you.


#### Removed/Deprecated Settings

Fortunately, if you're using a recent release of PHP (5.3 or 5.4), you have two less things
to worry about. The `register_globals` and `magic_quotes_gpc` settings have been permenantly
removed from the language. These two settings alone have caused so many collective headaches to 
PHP developers thorough the years...it's good to see them go. 

Another setting that was deprecated
as of PHP 5.3 and *completely removed* in 5.4 was `safe_mode` which, contrary to its name, provided
a false sense of security for anyone that enabled it. It tried to lock down the install so
that the application couldn't "do things it shouldn't" but it had so many problems, it was finally 
removed. It's been replaced, though...thankfully by something a bit more robust - common sense! 
Since there's no silver bullet when it comes to protecting an application, you're just going to 
have to use some sense and put some thought into the architecture of your code to protect it!

#### Resources

[PhpSecInfo Tests for your php.ini](http://phpsec.org/projects/phpsecinfo/tests/)
[OWASP on HttpOnly](https://www.owasp.org/index.php/HttpOnly)
